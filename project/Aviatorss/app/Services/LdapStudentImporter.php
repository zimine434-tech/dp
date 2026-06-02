<?php

namespace App\Services;

use App\Concerns\InteractsWithLdapStudentDirectory;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class LdapStudentImporter
{
    use InteractsWithLdapStudentDirectory;

    /**
     * Импорт студентов из AD (те же фильтры OU, что при добавлении в команду).
     *
     * @param  callable(string): void|null  $onProgress
     */
    public function importAll(?callable $onProgress = null): int
    {
        $report = function (string $message) use ($onProgress): void {
            if ($onProgress) {
                $onProgress($message);
            }
        };

        $bases = array_values(array_unique(array_filter([
            $this->studentsBaseDn(),
            env('LDAP_BASE_DN'),
        ])));

        $imported = 0;
        $skipped = 0;
        $raw = 0;

        foreach ($bases as $baseDn) {
            $report("Поиск в AD: {$baseDn}");

            [$chunkImported, $chunkSkipped, $chunkRaw] = $this->importFromBase($baseDn);
            $imported += $chunkImported;
            $skipped += $chunkSkipped;
            $raw += $chunkRaw;

            if ($chunkRaw > 0) {
                break;
            }
        }

        $report("Записей из AD (до фильтра): {$raw}, импортировано: {$imported}, отфильтровано: {$skipped}");

        Log::info('LDAP import students finished', [
            'bases' => $bases,
            'raw' => $raw,
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        return $imported;
    }

    /**
     * @return array{0: int, 1: int, 2: int} imported, skipped, raw
     */
    private function importFromBase(string $baseDn): array
    {
        $imported = 0;
        $skipped = 0;
        $raw = 0;

        LdapUser::on()
            ->in($baseDn)
            ->chunk(500, function ($ldapUsers) use (&$imported, &$skipped, &$raw) {
                $raw += is_countable($ldapUsers) ? count($ldapUsers) : 0;

                foreach ($ldapUsers as $ldapUser) {
                    $row = $this->ldapUserToStudentCandidate($ldapUser);
                    if ($row === null) {
                        $skipped++;

                        continue;
                    }

                    $user = User::query()->firstOrNew(['login' => $row['login']]);
                    $user->fill([
                        'firstname' => $row['firstname'],
                        'lastname' => $row['lastname'],
                        'patronymic' => $row['patronymic'],
                        'role' => 'student',
                        'group_name' => $row['group_name'],
                        'active' => $this->isLdapAccountActive($ldapUser),
                    ]);

                    if (! $user->exists) {
                        $user->status_fizorg = false;
                    }

                    $user->save();
                    $imported++;
                }
            });

        return [$imported, $skipped, $raw];
    }
}
