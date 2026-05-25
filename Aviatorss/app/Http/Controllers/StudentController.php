<?php

namespace App\Http\Controllers;

use App\Models\ScheduleGroup;
use App\Models\User;
use App\Services\IrkatScheduleDomParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of students grouped by groups.
     */
    public function index(Request $request)
    {
        if (! $request->filled('group')) {
            $request->merge(['group' => 'all']);
        }
        if (! $request->filled('fizorg')) {
            $request->merge(['fizorg' => 'all']);
        }

        $allGroups = User::query()
            ->where('role', 'student')
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name')
            ->values();

        $studentsWithoutGroupCount = User::query()
            ->where('role', 'student')
            ->whereNull('group_name')
            ->count();

        $groupChoices = array_merge(['all', 'no-group'], $allGroups->all());
        if ($studentsWithoutGroupCount === 0) {
            $groupChoices = array_values(array_filter($groupChoices, fn ($g) => $g !== 'no-group'));
        }

        $validated = $request->validate([
            'group' => ['nullable', Rule::in($groupChoices)],
            'fizorg' => ['nullable', Rule::in(['all', 'fizorg', 'not_fizorg'])],
            'lastname' => ['nullable', 'string', 'max:255'],
        ]);

        $groupFilter = $validated['group'] ?? 'all';
        $fizorgFilter = $validated['fizorg'] ?? 'all';
        $lastnameSearch = trim((string) ($validated['lastname'] ?? ''));

        $baseQuery = User::query()
            ->where('role', 'student')
            ->orderByRaw('CASE WHEN group_name IS NULL THEN 1 ELSE 0 END')
            ->orderBy('group_name')
            ->orderBy('lastname')
            ->orderBy('firstname');

        if ($groupFilter === 'no-group') {
            $baseQuery->whereNull('group_name');
        } elseif ($groupFilter !== 'all') {
            $baseQuery->where('group_name', $groupFilter);
        }

        if ($fizorgFilter === 'fizorg') {
            $baseQuery->where('status_fizorg', true);
        } elseif ($fizorgFilter === 'not_fizorg') {
            $baseQuery->where(function ($q) {
                $q->where('status_fizorg', false)
                    ->orWhereNull('status_fizorg');
            });
        }

        if ($lastnameSearch !== '') {
            $like = '%'.addcslashes($lastnameSearch, '%_\\').'%';
            $baseQuery->whereRaw('LOWER(lastname) LIKE LOWER(?)', [$like]);
        }

        $orderedMinimal = (clone $baseQuery)
            ->select(['id', 'group_name', 'lastname', 'firstname'])
            ->get();

        $totalFiltered = $orderedMinimal->count();
        $studentsTotalAll = User::query()->where('role', 'student')->count();

        if ($totalFiltered === 0) {
            $indexPayload = [
                'groupedOnPage' => collect(),
                'allGroups' => $allGroups,
                'studentsWithoutGroupCount' => $studentsWithoutGroupCount,
                'groupFilter' => $groupFilter,
                'fizorgFilter' => $fizorgFilter,
                'lastnameSearch' => $lastnameSearch,
                'studentsTotalAll' => $studentsTotalAll,
                'totalFiltered' => 0,
                'studentListCurrentPage' => 1,
                'studentListLastPage' => 1,
            ];

            if ($request->boolean('fragment')) {
                return view('students.partials.results', [
                    'studentsTotalAll' => $studentsTotalAll,
                    'totalFiltered' => 0,
                    'groupedOnPage' => collect(),
                    'studentListCurrentPage' => 1,
                    'studentListLastPage' => 1,
                ]);
            }

            return view('students.index', $indexPayload);
        }

        $pages = $this->packStudentGroupRunsIntoPages($orderedMinimal, 50);
        $studentListLastPage = count($pages);
        $studentListCurrentPage = max(1, min((int) $request->query('page', 1), $studentListLastPage));

        $pageRuns = $pages[$studentListCurrentPage - 1];
        $ids = collect($pageRuns)->flatMap(fn ($r) => $r['ids'])->all();

        $usersById = User::query()->whereIn('id', $ids)->get()->keyBy('id');
        $orderedUsers = collect($ids)->map(fn (int $id) => $usersById->get($id))->filter();

        $groupedOnPage = $this->groupStudentsOnPageForView($orderedUsers);

        $indexPayload = [
            'groupedOnPage' => $groupedOnPage,
            'allGroups' => $allGroups,
            'studentsWithoutGroupCount' => $studentsWithoutGroupCount,
            'groupFilter' => $groupFilter,
            'fizorgFilter' => $fizorgFilter,
            'lastnameSearch' => $lastnameSearch,
            'studentsTotalAll' => $studentsTotalAll,
            'totalFiltered' => $totalFiltered,
            'studentListCurrentPage' => $studentListCurrentPage,
            'studentListLastPage' => $studentListLastPage,
        ];

        if ($request->boolean('fragment')) {
            return view('students.partials.results', [
                'studentsTotalAll' => $studentsTotalAll,
                'totalFiltered' => $totalFiltered,
                'groupedOnPage' => $groupedOnPage,
                'studentListCurrentPage' => $studentListCurrentPage,
                'studentListLastPage' => $studentListLastPage,
            ]);
        }

        return view('students.index', $indexPayload);
    }

    /**
     * Display the specified student.
     */
    public function show(Request $request, int|string $student)
    {
        $user = User::query()
            ->whereKey($student)
            ->where('role', 'student')
            ->first();

        if (! $user) {
            return redirect()
                ->route('students.index', $request->query())
                ->with('error', 'Студент не найден. Возможно, запись удалена.');
        }

        return view('students.profile', ['user' => $user]);
    }

    /**
     * Toggle fizorg status for a student.
     */
    public function toggleFizorg(Request $request, int|string $student)
    {
        $user = User::query()
            ->whereKey($student)
            ->where('role', 'student')
            ->first();

        if (! $user) {
            return redirect()
                ->route('students.index', $request->query())
                ->with('error', 'Студент не найден. Возможно, запись удалена.');
        }

        if (! $user->status_fizorg) {
            if ($user->group_name) {
                $existingFizorg = User::where('role', 'student')
                    ->where('group_name', $user->group_name)
                    ->where('status_fizorg', true)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingFizorg) {
                    return redirect()
                        ->back(fallback: route('students.index', $request->query()))
                        ->with('error', 'В группе уже есть физорг. Сначала снимите статус физорга у другого студента.');
                }
            }

            $user->update(['status_fizorg' => true]);
            $message = 'Статус физорга успешно установлен!';
        } else {
            $user->update(['status_fizorg' => false]);
            $message = 'Статус физорга успешно снят!';
        }

        return redirect()
            ->back(fallback: route('students.index', $request->query()))
            ->with('success', $message);
    }

    public function groupSchedule(Request $request, IrkatScheduleDomParser $parser, string $groupName)
    {
        $groupName = trim($groupName);

        $lock = Cache::lock('irkat_schedule:sync_groups', 120);
        if ($lock->get()) {
            try {
                $syncedAt = Cache::get('irkat_schedule:groups_synced_at');
                $needsSync = ! $syncedAt || now()->diffInDays($syncedAt) >= 30 || ScheduleGroup::query()->count() === 0;

                if ($needsSync) {
                    $map = $parser->parseGroupMap();
                    foreach ($map as $name => $remoteId) {
                        ScheduleGroup::updateOrCreate(
                            ['name' => $name],
                            ['remote_id' => $remoteId, 'course' => null]
                        );
                    }

                    Cache::put('irkat_schedule:groups_synced_at', now(), now()->addDays(31));
                }
            } finally {
                optional($lock)->release();
            }
        }

        $groupId = config("schedule.groups.$groupName");
        if (! $groupId) {
            $groupId = ScheduleGroup::where('name', $groupName)->value('remote_id');
        }

        $date = (string) $request->query('date', now()->format('Y-m-d'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->format('Y-m-d');
        }

        $groups = ScheduleGroup::query()->orderBy('name')->get(['name', 'remote_id']);

        if (! $groupId) {
            return view('students.group-schedule', $this->groupScheduleViewPayload(
                $groupName,
                $date,
                $groups,
                scheduleNotFound: true,
            ));
        }

        $schedule = null;
        $scheduleNotFound = false;

        try {
            $cacheKey = "irkat_schedule:group={$groupId}:date={$date}";
            $schedule = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($parser, $groupId, $date) {
                return $parser->parseScheduleTable((int) $groupId, $date);
            });

            if ($this->scheduleDataIsEmpty($schedule)) {
                $scheduleNotFound = true;
                $schedule = null;
            }
        } catch (\Throwable) {
            $scheduleNotFound = true;
            $schedule = null;
        }

        $highlight = $scheduleNotFound
            ? null
            : $this->computeCurrentPairHighlight($schedule, $date);

        return view('students.group-schedule', $this->groupScheduleViewPayload(
            $groupName,
            $date,
            $groups,
            schedule: $schedule,
            highlight: $highlight,
            scheduleNotFound: $scheduleNotFound,
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ScheduleGroup>  $groups
     * @return array<string, mixed>
     */
    private function groupScheduleViewPayload(
        string $groupName,
        string $date,
        $groups,
        ?array $schedule = null,
        ?array $highlight = null,
        bool $scheduleNotFound = false,
    ): array {
        return [
            'groupName' => $groupName,
            'date' => $date,
            'schedule' => $schedule ?? ['headers' => [], 'times' => [], 'rows' => []],
            'highlight' => $highlight,
            'groups' => $groups,
            'scheduleNotFound' => $scheduleNotFound,
        ];
    }

    private function scheduleDataIsEmpty(?array $schedule): bool
    {
        if ($schedule === null) {
            return true;
        }

        return empty($schedule['headers']) || empty($schedule['times']);
    }

    /**
     * @param  Collection<int, User>  $orderedMinimal
     * @return list<list{array{key: string, ids: Collection<int, int>}>>
     */
    private function packStudentGroupRunsIntoPages(Collection $orderedMinimal, int $studentCountPageMax = 50): array
    {
        $groupKeyFn = static fn (User $row) => $row->group_name ?? 'no-group';

        $runs = [];
        $currentKey = null;
        $currentIds = collect();

        foreach ($orderedMinimal as $row) {
            $k = $groupKeyFn($row);
            if ($k !== $currentKey) {
                if ($currentIds->isNotEmpty()) {
                    $runs[] = ['key' => $currentKey, 'ids' => $currentIds->values()];
                }
                $currentKey = $k;
                $currentIds = collect([(int) $row->id]);
            } else {
                $currentIds->push((int) $row->id);
            }
        }
        if ($currentIds->isNotEmpty()) {
            $runs[] = ['key' => $currentKey, 'ids' => $currentIds->values()];
        }

        $pages = [];
        $pageRuns = [];
        $pageCount = 0;

        foreach ($runs as $run) {
            $ids = $run['ids'];
            $n = $ids->count();

            if ($n > $studentCountPageMax) {
                if ($pageRuns !== []) {
                    $pages[] = $pageRuns;
                    $pageRuns = [];
                    $pageCount = 0;
                }
                foreach ($ids->chunk($studentCountPageMax) as $chunk) {
                    $pages[] = [['key' => $run['key'], 'ids' => $chunk->values()]];
                }

                continue;
            }

            if ($pageCount + $n > $studentCountPageMax) {
                if ($pageRuns !== []) {
                    $pages[] = $pageRuns;
                    $pageRuns = [];
                    $pageCount = 0;
                }
            }

            $pageRuns[] = ['key' => $run['key'], 'ids' => $ids];
            $pageCount += $n;
        }

        if ($pageRuns !== []) {
            $pages[] = $pageRuns;
        }

        return $pages;
    }

    /**
     * @param  Collection<int, User>  $orderedUsers
     * @return Collection<string, Collection<int, User>>
     */
    private function groupStudentsOnPageForView(Collection $orderedUsers): Collection
    {
        return $orderedUsers
            ->groupBy(fn (User $s) => $s->group_name ?? 'no-group')
            ->sortKeysUsing(function (string $a, string $b) {
                if ($a === 'no-group') {
                    return $b === 'no-group' ? 0 : 1;
                }
                if ($b === 'no-group') {
                    return -1;
                }

                return strcmp($a, $b);
            });
    }

    /**
     * @param  array<string, mixed>  $schedule
     * @return array{row:int,col:int}|null
     */
    private function computeCurrentPairHighlight(array $schedule, string $baseDateYmd): ?array
    {
        try {
            $today = now()->startOfDay();
            $base = Carbon::createFromFormat('Y-m-d', $baseDateYmd)->startOfDay();

            $colIdx = null;
            foreach (($schedule['headers'] ?? []) as $i => $h) {
                $label = (string) ($h['label'] ?? '');
                if (! preg_match('/^(?<d>\d{2})\.(?<m>\d{2})/u', $label, $mm)) {
                    continue;
                }
                $d = (int) $mm['d'];
                $m = (int) $mm['m'];
                $y = (int) $base->year;
                $dt = Carbon::create($y, $m, $d)->startOfDay();

                if ($dt->diffInDays($base, false) > 180) {
                    $dt = $dt->subYear();
                } elseif ($base->diffInDays($dt, false) > 180) {
                    $dt = $dt->addYear();
                }

                if ($dt->equalTo($today)) {
                    $colIdx = (int) $i;
                    break;
                }
            }

            if ($colIdx === null) {
                return null;
            }

            $now = now();
            $rowIdxOngoing = null;
            $rowIdxNext = null;
            $nextStart = null;

            foreach (($schedule['times'] ?? []) as $i => $range) {
                $range = (string) $range;
                if (! preg_match('/^(?<h1>\d{2}):(?<m1>\d{2})\s*-\s*(?<h2>\d{2}):(?<m2>\d{2})/u', $range, $tm)) {
                    continue;
                }
                $start = $today->copy()->setTime((int) $tm['h1'], (int) $tm['m1'], 0);
                $end = $today->copy()->setTime((int) $tm['h2'], (int) $tm['m2'], 0);

                $cellCards = $schedule['rows'][$i][$colIdx] ?? [];
                $hasLesson = is_array($cellCards) && count($cellCards) > 0;
                if (! $hasLesson) {
                    continue;
                }

                if ($now->betweenIncluded($start, $end)) {
                    $rowIdxOngoing = (int) $i;
                    break;
                }

                if ($start->greaterThan($now)) {
                    if ($nextStart === null || $start->lessThan($nextStart)) {
                        $nextStart = $start;
                        $rowIdxNext = (int) $i;
                    }
                }
            }

            $rowIdx = $rowIdxOngoing ?? $rowIdxNext;
            if ($rowIdx === null) {
                return null;
            }

            return ['row' => $rowIdx, 'col' => $colIdx];
        } catch (\Throwable) {
            return null;
        }
    }
}
