<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;

class IrkatScheduleDomParser
{
    /**
     * Parse group id mapping from schedule.irkat.ru rendered DOM.
     *
     * @return array<string,int> [groupName => remoteId]
     */
    public function parseGroupMap(): array
    {
        $html = Browsershot::url('https://schedule.irkat.ru/')
            ->setNodeBinary('node')
            ->setNpmBinary('npm')
            ->waitUntilNetworkIdle()
            ->timeout(60)
            ->bodyHtml();

        // Extract <option value="298">ИС-25-1</option>
        preg_match_all('/<option[^>]*value="(?<id>\d+)"[^>]*>(?<name>[^<]+)<\/option>/u', $html, $m, PREG_SET_ORDER);

        $map = [];
        foreach ($m as $row) {
            $id = (int) ($row['id'] ?? 0);
            $name = trim(html_entity_decode((string) ($row['name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($id <= 0 || $name === '' || $name === 'Все группы' || mb_strtolower($name) === mb_strtolower('Все группы')) {
                continue;
            }
            // Skip course separator options like "1 курс"
            if (preg_match('/^\d+\s+курс$/u', $name)) {
                continue;
            }
            $map[$name] = $id;
        }

        if (count($map) === 0) {
            throw new \RuntimeException('Failed to parse group list from schedule.irkat.ru (no options found).');
        }

        return $map;
    }

    /**
     * Parse rendered schedule table for a group and a chosen date (week view).
     *
     * @return array{
     *   groupName:string|null,
     *   headers: array<int, array{date:string,label:string}>,
     *   times: array<int, string>,
     *   rows: array<int, array<int, array<int, array{
     *     type?:string|null,
     *     title?:string|null,
     *     teacher?:string|null,
     *     place?:string|null
     *   }>>>
     * }
     */
    public function parseScheduleTable(int $groupId, string $dateYmd): array
    {
        $url = 'https://schedule.irkat.ru/?group=' . urlencode((string) $groupId) . '&date=' . urlencode($dateYmd);

        // We must render the SPA, then read the DOM (table headers + cells).
        $json = Browsershot::url($url)
            ->setNodeBinary('node')
            ->setNpmBinary('npm')
            ->waitUntilNetworkIdle()
            ->timeout(90)
            ->evaluate(<<<'JS'
                JSON.stringify((() => {
                  const groupTitle = document.querySelector('h2.text-sky-900')?.textContent?.trim() || null;
                  const headerThs = Array.from(document.querySelectorAll('table thead th')).slice(1);
                  const headers = headerThs.map(th => {
                    const label = (th.textContent || '').trim().replace(/\s+/g, ' ');
                    const m = label.match(/^(\d{2}\.\d{2})/);
                    return { date: m ? m[1] : '', label };
                  });

                  const tbodyRows = Array.from(document.querySelectorAll('tbody.tbody_items > tr'));
                  const times = tbodyRows.map(tr => (tr.querySelector('td')?.textContent || '').trim());

                  const rows = tbodyRows.map(tr => {
                    const tds = Array.from(tr.querySelectorAll('td')).slice(1);
                    return tds.map(td => {
                      const cards = Array.from(td.querySelectorAll('div.text-center.relative'));
                      return cards.map(card => {
                        const type = card.querySelector('div')?.textContent?.trim() || null;
                        const title = card.querySelector('b')?.textContent?.trim() || null;
                        const teacherLine = Array.from(card.querySelectorAll('div')).map(x => x.textContent?.trim()).filter(Boolean).slice(-1)[0] || null;
                        // Remaining text nodes after teacher line often contains place (e.g. "Дистанционно" or "каб. 101")
                        const text = (card.textContent || '').replace(/\s+/g,' ').trim();
                        let place = null;
                        if (teacherLine) {
                          const idx = text.indexOf(teacherLine);
                          if (idx >= 0) {
                            place = text.substring(idx + teacherLine.length).trim() || null;
                          }
                        }
                        return { type, title, teacher: teacherLine, place };
                      });
                    });
                  });

                  return { groupName: groupTitle, headers, times, rows };
                })());
                JS);

        $data = json_decode((string) $json, true);

        if (!is_array($data) || empty($data['headers']) || empty($data['times'])) {
            throw new \RuntimeException('Failed to parse schedule table from schedule.irkat.ru.');
        }

        return $data;
    }
}

