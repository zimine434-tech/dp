<?php

namespace App\Http\Controllers;

use App\Concerns\InteractsWithLdapStudentDirectory;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class TeamController extends Controller
{
    use InteractsWithLdapStudentDirectory;

    /**
     * Display a listing of the teams.
     */
    public function index()
    {
        $teams = Team::with(['members.user', 'sport'])->latest()->paginate(10);

        return view('teams.teacher.index', compact('teams'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        $sports = Sport::query()->orderBy('name')->orderBy('id')->get();

        return view('teams.teacher.create', compact('sports'));
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:teams,name',
            'description' => 'nullable|string',
            'sport_id' => 'nullable|exists:sports,id',
        ], [
            'name.unique' => 'Команда с таким названием уже существует.',
        ]);

        $team = Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Команда успешно создана!');
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $team->load([
            'sport',
            'members' => fn ($q) => $q->with(['user', 'addedBy', 'removedBy'])->orderBy('joined_at', 'desc'),
        ]);

        return view('teams.teacher.show', compact('team'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $sports = Sport::query()->orderBy('name')->orderBy('id')->get();

        return view('teams.teacher.edit', compact('team', 'sports'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:teams,name,' . $team->id,
            'description' => 'nullable|string',
            'sport_id' => 'nullable|exists:sports,id',
        ], [
            'name.unique' => 'Команда с таким названием уже существует.',
        ]);

        $team->update($validated);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Команда успешно обновлена!');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        // Удаляем только участников команды
        $team->members()->delete();
        
        // Удаляем саму команду
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Команда удалена.');
    }

    /**
     * AJAX поиск студентов в LDAP для добавления в команду.
     */
    public function searchStudents(Request $request, Team $team)
    {
        $search = trim($request->input('search', ''));
        
        if (mb_strlen($search, 'UTF-8') < 2) {
            return response()->json(['students' => []]);
        }

        try {
            if (!$team->relationLoaded('members')) {
                $team->load('members');
            }

            // Только текущие участники (не удалённые)
            $memberIds = $team->members->whereNull('out')->pluck('user_id')->unique()->values()->toArray();
            $searchWords = array_filter(preg_split('/\s+/u', mb_strtolower($search, 'UTF-8')), function ($word) {
                return $word !== '';
            });

            if (empty($searchWords)) {
                return response()->json(['students' => []]);
            }

            $ldapUsers = collect();

            try {
                $firstWord = $searchWords[0];
                $ldapUsers = LdapUser::query()
                    ->whereContains('cn', $firstWord)
                    ->orWhereContains('name', $firstWord)
                    ->orWhereContains('displayname', $firstWord)
                    ->limit(200)
                    ->get();
            } catch (\Throwable $e) {
                \Log::error('LDAP search error in team', [
                    'search' => $search,
                    'error' => $e->getMessage(),
                ]);
            }

            $students = [];

            foreach ($ldapUsers as $ldapUser) {
                $row = $this->ldapUserToStudentCandidate($ldapUser);
                if ($row === null) {
                    continue;
                }

                $firstnameLower = mb_strtolower($row['firstname'], 'UTF-8');
                $lastnameLower = mb_strtolower($row['lastname'], 'UTF-8');
                $patronymicLower = $row['patronymic'] ? mb_strtolower($row['patronymic'], 'UTF-8') : '';
                $commonName = trim(implode(' ', array_filter([$row['lastname'], $row['firstname'], $row['patronymic']])));
                $cnLower = mb_strtolower($commonName, 'UTF-8');
                $composedCnLower = mb_strtolower(trim(implode(' ', array_filter([$row['lastname'], $row['firstname'], $row['patronymic']]))), 'UTF-8');

                $matches = true;
                foreach ($searchWords as $word) {
                    if (
                        ! str_contains($firstnameLower, $word)
                        && ! str_contains($lastnameLower, $word)
                        && ! str_contains($patronymicLower, $word)
                        && ! str_contains($cnLower, $word)
                        && ! str_contains($composedCnLower, $word)
                    ) {
                        $matches = false;
                        break;
                    }
                }

                if (! $matches) {
                    continue;
                }

                $login = $row['login'];
                $user = User::where('login', $login)->first();
                if ($user && in_array($user->id, $memberIds)) {
                    continue;
                }

                $students[] = [
                    'id' => $user ? $user->id : null,
                    'lastname' => $row['lastname'],
                    'firstname' => $row['firstname'],
                    'patronymic' => $row['patronymic'],
                    'login' => $login,
                    'group_name' => $row['group_name'],
                    'status_fizorg' => $user ? (bool) $user->status_fizorg : false,
                    'dn' => $row['dn'],
                ];
            }

            // Сортируем по фамилии и имени
            usort($students, function($a, $b) {
                $cmp = strcmp($a['lastname'], $b['lastname']);
                if ($cmp === 0) {
                    $cmp = strcmp($a['firstname'], $b['firstname']);
                }
                return $cmp;
            });

            return response()->json(['students' => $students]);

        } catch (\Throwable $e) {
            \Log::error('Ошибка поиска студентов в AD для команды', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'search' => $search,
                'team_id' => $team->id,
            ]);

            return response()->json([
                'students' => [], 
                'error' => 'Ошибка поиска в Active Directory'
            ], 500);
        }
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $validated = $request->validate([
            'student_data' => 'required|string',
            'type_user' => 'nullable|in:coach,capitan,member',
        ]);

        $studentData = json_decode($validated['student_data'], true);
        if (!is_array($studentData) || empty($studentData['login'])) {
            return redirect()->route('teams.show', $team)
                ->withErrors(['student_data' => 'Не удалось определить выбранного студента. Попробуйте выбрать снова.'])
                ->withInput();
        }

        $firstname = trim($studentData['firstname'] ?? '');
        $lastname = trim($studentData['lastname'] ?? '');
        $patronymic = trim($studentData['patronymic'] ?? '');
        $login = trim($studentData['login']);
        $groupName = $studentData['group_name'] ?? null;
        if (!$groupName && !empty($studentData['dn'])) {
            $groupName = $this->extractGroupFromDn($studentData['dn']);
        }

        if ($firstname === '' || $lastname === '' || $login === '') {
            return redirect()->route('teams.show', $team)
                ->withErrors(['student_data' => 'У выбранного студента отсутствуют необходимые данные.'])
                ->withInput();
        }

        $user = User::where('login', $login)->first();

        if (!$user) {
            $user = User::create([
                'login' => $login,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'patronymic' => $patronymic !== '' ? $patronymic : null,
                'role' => 'student',
                'group_name' => $groupName,
                'active' => true,
            ]);
        } else {
            $user->update([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'patronymic' => $patronymic !== '' ? $patronymic : null,
                'group_name' => $groupName ?? $user->group_name,
            ]);
        }

        // Проверяем, не является ли пользователь уже текущим участником команды
        $existingMember = TeamMember::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->whereNull('out')
            ->first();

        if ($existingMember) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Этот пользователь уже является участником команды.');
        }

        // Если устанавливается роль капитана, снимаем роль капитана с других текущих участников
        if ($validated['type_user'] === 'capitan') {
            TeamMember::where('team_id', $team->id)
                ->whereNull('out')
                ->where('type_user', 'capitan')
                ->update(['type_user' => 'member']);
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'id_adding' => auth()->id(),
            'type_user' => $validated['type_user'] ?? 'member',
            'joined_at' => now(),
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Участник успешно добавлен в команду!');
    }

    /**
     * Update member role in the team.
     */
    public function updateMemberRole(Request $request, Team $team, TeamMember $member)
    {
        if ($member->team_id !== $team->id) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Участник не принадлежит этой команде.');
        }
        if ($member->out !== null) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Нельзя изменить роль выбывшего участника.');
        }

        $validated = $request->validate([
            'type_user' => 'nullable|in:capitan,member',
        ]);

        if ($validated['type_user'] === 'capitan') {
            TeamMember::where('team_id', $team->id)
                ->whereNull('out')
                ->where('type_user', 'capitan')
                ->where('id', '!=', $member->id)
                ->update(['type_user' => 'member']);
        }

        $member->update([
            'type_user' => $validated['type_user'] ?? 'member',
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Роль участника успешно обновлена!');
    }

    /**
     * Accept/activate a team member.
     */
    public function acceptMember(Request $request, Team $team, TeamMember $member)
    {
        if ($member->team_id !== $team->id) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Участник не принадлежит этой команде.');
        }
        if ($member->out !== null) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Участник уже выбыл из команды.');
        }

        $validated = $request->validate([
            'type_user' => 'nullable|in:capitan,member',
        ]);

        if ($validated['type_user'] === 'capitan') {
            TeamMember::where('team_id', $team->id)
                ->whereNull('out')
                ->where('type_user', 'capitan')
                ->where('id', '!=', $member->id)
                ->update(['type_user' => 'member']);
        }

        $member->update([
            'type_user' => $validated['type_user'] ?? 'member',
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Участник успешно принят в команду!');
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, TeamMember $member)
    {
        // Проверяем, что участник принадлежит этой команде
        if ($member->team_id !== $team->id) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Участник не принадлежит этой команде.');
        }

        // Уже удалён (история)
        if ($member->out !== null) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Этот участник уже был удалён из команды.');
        }

        // Не удаляем запись — помечаем выход, чтобы сохранить лог: кто и когда удалил
        $member->update([
            'out' => now(),
            'id_out' => auth()->id(),
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Участник успешно удален из команды!');
    }

    /**
     * Display a listing of the teams for students (read-only).
     */
    public function indexStudent()
    {
        $teams = Team::with(['sport', 'members.user'])->latest()->paginate(12);

        return view('teams.student.index', compact('teams'));
    }

    /**
     * Display the specified team for students (read-only).
     */
    public function showStudent(Team $team)
    {
        $team->load([
            'sport',
            'members' => fn ($q) => $q->with('user')->orderBy('joined_at', 'desc'),
        ]);
        $joinRequest = \App\Models\TeamJoinRequest::query()
            ->where('team_id', $team->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();

        return view('teams.student.show', compact('team', 'joinRequest'));
    }
}

