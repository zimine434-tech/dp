<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['present', 'string'],
        ], [
            'login.required' => 'Введите логин.',
            'password.present' => 'Введите пароль.',
        ]);

        // Сначала проверяем LDAP
        $login = trim($credentials['login']);
        $password = $credentials['password'];

        $ldapProfile = $this->attemptLdapAuth($login, $password, $errorMessage);
        
        if ($ldapProfile !== null) {
            if (! $this->isAllowedGroup($ldapProfile)) {
                Log::warning('Попытка входа из запрещённой группы безопасности', [
                    'login' => $login,
                    'role' => $ldapProfile['role'],
                    'group' => $ldapProfile['group_name'],
                    'member_of' => $ldapProfile['member_of'],
                    'allowed_groups' => $this->getAllowedGroups(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors(['login' => 'Вход запрещён.'])
                    ->withInput($request->only('login'));
            }

            if (! $ldapProfile['active']) {
                \App\Models\User::updateOrCreate(
                    ['login' => $login],
                    [
                        'firstname' => $ldapProfile['firstname'],
                        'lastname' => $ldapProfile['lastname'],
                        'patronymic' => $ldapProfile['patronymic'],
                        'role' => $ldapProfile['role'],
                        'group_name' => $ldapProfile['group_name'],
                        'active' => false,
                    ]
                );

                Log::warning('Попытка входа отключённой LDAP-учётки', [
                    'login' => $login,
                    'group' => $ldapProfile['group_name'],
                    'member_of' => $ldapProfile['member_of'],
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors(['login' => 'Ваша учётная запись заблокирована.'])
                    ->withInput($request->only('login'));
            }

            // Затем ищем/создаем пользователя в БД
            $user = \App\Models\User::updateOrCreate(
                ['login' => $login],
                [
                    'firstname' => $ldapProfile['firstname'],
                    'lastname' => $ldapProfile['lastname'],
                    'patronymic' => $ldapProfile['patronymic'],
                    'role' => $ldapProfile['role'],
                    'group_name' => $ldapProfile['group_name'],
                    'active' => $ldapProfile['active'],
                ]
            );
            
            // Логиним пользователя в Laravel
            Auth::login($user, $request->boolean('remember'));

            $request->session()->regenerate();

            $request->session()->put('ldap_profile', [
                'login' => $user->login,
                'role' => $user->role,
                'group' => $user->group_name,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'patronymic' => $user->patronymic,
                'member_of' => $ldapProfile['member_of'],
                'active' => $ldapProfile['active'],
            ]);

            Log::info('LDAP-вход выполнен', [
                'login' => $user->login,
                'role' => $user->role,
                'group' => $user->group_name,
            ]);

            return redirect()->route('dashboard');
        }

        return redirect()
            ->route('login')
            ->withErrors([
                'login' => $errorMessage ?? 'Неверный логин или пароль',
            ])
            ->withInput($request->only('login'));
    }

    private function attemptLdapAuth(string $username, string $password, ?string &$errorMessage = null): ?array
    {
        try {
            $connection = new Connection([
                'hosts' => [env('LDAP_HOST')],
                'base_dn' => env('LDAP_BASE_DN'),
                'username' => env('LDAP_USERNAME'),
                'password' => env('LDAP_PASSWORD'),
            ]);

            $connection->connect();

            // Пытаемся найти пользователя и проверить пароль
            $user = $this->findLdapUser($username);

            if (! $user) {
                $errorMessage = 'Неверный логин или пароль';
                return null;
            }

            if (! $this->isLdapAccountActive($user)) {
                $profile = $this->buildProfileFromLdap($user);

                \App\Models\User::updateOrCreate(
                    ['login' => $username],
                    [
                        'firstname' => $profile['firstname'],
                        'lastname' => $profile['lastname'],
                        'patronymic' => $profile['patronymic'],
                        'role' => $profile['role'],
                        'group_name' => $profile['group_name'],
                        'active' => false,
                    ]
                );

                Log::warning('Отклонён вход отключённой LDAP-учётки до проверки пароля', [
                    'login' => $username,
                    'group' => $profile['group_name'],
                    'member_of' => $profile['member_of'],
                ]);

                $errorMessage = 'Ваша учётная запись заблокирована.';

                return null;
            }

            if (! $connection->auth()->attempt($user->getDn(), $password)) {
                $errorMessage = 'Неверный логин или пароль';
                return null;
            }

            $profile = $this->buildProfileFromLdap($user);

            if ($profile['role'] !== 'teacher' && $profile['role'] !== 'student') {
                Log::warning('LDAP: отклонён вход — учётная запись не относится к студентам или преподавателям', [
                    'login' => $username,
                    'resolved_role' => $profile['role'],
                    'group' => $profile['group_name'],
                    'member_of' => $profile['member_of'],
                ]);
                $errorMessage = 'Вход разрешён только студентам и преподавателям.';

                return null;
            }

            return $profile;
        } catch (\Throwable $e) {
            Log::warning('Ошибка LDAP-авторизации', [
                'login' => $username,
                'message' => $e->getMessage(),
            ]);

            $errorMessage = 'Ошибка подключения к LDAP.';
            return null;
        }
    }

    private function findLdapUser(string $username): ?LdapUser
    {
        $sanitized = trim($username);
        $query = LdapUser::query();

        if (str_contains($sanitized, '@')) {
            $query->whereEquals('userprincipalname', $sanitized);
        } else {
            $query->whereEquals('samaccountname', $sanitized);
        }

        $user = $query->first();

        if ($user) {
            return $user;
        }

        // Дополнительные попытки поиска по альтернативным атрибутам
        $fallbackQuery = LdapUser::query()
            ->whereContains('userprincipalname', $sanitized)
            ->orWhereContains('samaccountname', $sanitized)
            ->orWhereEquals('mail', $sanitized);

        return $fallbackQuery->first();
    }

    private function buildProfileFromLdap(LdapUser $user): array
    {
        $firstname = $this->getFirstAttribute($user, ['givenname', 'givenName', 'gn']);
        $lastname = $this->getFirstAttribute($user, ['sn', 'surname']);
        $patronymic = $this->getFirstAttribute($user, ['middlename', 'initials', 'displaynamefirstlast']);
        $commonName = $this->getFirstAttribute($user, ['cn', 'name']);

        if ($firstname && str_contains($firstname, ' ')) {
            $parts = array_values(array_filter(preg_split('/\s+/u', trim($firstname)) ?: []));

            if (! empty($parts)) {
                $firstname = array_shift($parts);

                if (! $patronymic && ! empty($parts)) {
                    $patronymic = implode(' ', $parts);
                }
            }
        }

        if (! $firstname || ! $lastname) {
            $this->fillNamesFromCommonName($firstname, $lastname, $patronymic, $commonName);
        } elseif (! $patronymic) {
            $this->fillMiddleNameFromCommonName($patronymic, $commonName);
        }

        $role = $this->determineRole($user);
        $groupName = $this->resolveGroupName($user);

        // Для всех преподавателей устанавливаем группу "Преподаватели"
        // чтобы объединить основные, кандидатов и внешнее совместительство в одну группу
        if ($role === 'teacher') {
            $groupName = 'Преподаватели';
        }

        return [
            'firstname' => $firstname ?? 'Unknown',
            'lastname' => $lastname ?? 'User',
            'patronymic' => $patronymic,
            'role' => $role,
            'group_name' => $groupName,
            'member_of' => $this->extractGroupNames((array) $user->getAttribute('memberof', [])),
            'active' => $this->isLdapAccountActive($user),
        ];
    }

    private function getFirstAttribute(LdapUser $user, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $user->getFirstAttribute($key);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function fillNamesFromCommonName(?string &$first, ?string &$last, ?string &$middle, ?string $cn): void
    {
        if (! $cn) {
            return;
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', trim($cn)) ?: []));

        if (empty($parts)) {
            return;
        }

        if (! $last && count($parts) >= 1) {
            $last = $parts[0];
        }

        if (! $first && count($parts) >= 2) {
            $first = $parts[1];
        }

        if (! $middle && count($parts) >= 3) {
            $middle = $parts[2];
        }
    }

    private function fillMiddleNameFromCommonName(?string &$middle, ?string $cn): void
    {
        if ($middle || ! $cn) {
            return;
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', trim($cn)) ?: []));

        if (count($parts) >= 3) {
            $middle = $parts[2];
        }
    }

    /**
     * Явное определение роли по LDAP. Без совпадений — null (вход запрещён, см. attemptLdapAuth).
     */
    private function determineRole(LdapUser $user): ?string
    {
        $dn = (string) $user->getDn();
        $memberOf = (array) $user->getAttribute('memberof', []);

        // Ключевые слова для преподавателя (DN или группы LDAP). Можно переопределить в .env: LDAP_ROLE_KEYWORDS_TEACHER
        $teacherKeywords = $this->getRoleKeywords('LDAP_ROLE_KEYWORDS_TEACHER', [
            'teacher', 'teachers', 'преподаватель', 'преподаватели', 'педагог', 'педагогический', 'педагоги',
            'ппс', 'профессор', 'доцент', 'staff', 'faculty',
        ]);
        $isTeacher = $this->hasAnyKeyword([$dn], $teacherKeywords) || $this->hasAnyKeyword($memberOf, $teacherKeywords);

        // Ключевые слова для студента. Можно переопределить в .env: LDAP_ROLE_KEYWORDS_STUDENT
        $studentKeywords = $this->getRoleKeywords('LDAP_ROLE_KEYWORDS_STUDENT', ['student', 'students', 'студент', 'студенты']);
        $isStudent = $this->hasAnyKeyword([$dn], $studentKeywords) || $this->hasAnyKeyword($memberOf, $studentKeywords);

        // Если подходит и то и другое — приоритет у преподавателя
        if ($isTeacher) {
            return 'teacher';
        }
        if ($isStudent) {
            return 'student';
        }

        return null;
    }

    private function resolveGroupName(LdapUser $user): ?string
    {
        $groupAttribute = env('LDAP_GROUP_NAME_ATTRIBUTE', '');

        if ($groupAttribute) {
            $value = $user->getFirstAttribute($groupAttribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        $dn = (string) $user->getDn();
        preg_match('/OU=([^,]+)/u', $dn, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        $memberOf = (array) $user->getAttribute('memberof', []);

        foreach ($memberOf as $dnString) {
            if (preg_match('/CN=([^,]+)/u', $dnString, $groupMatch)) {
                return $groupMatch[1];
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $haystacks
     * @param  array<int, string>  $keywords
     */
    private function hasAnyKeyword(array $haystacks, array $keywords): bool
    {
        foreach ($haystacks as $item) {
            if (! is_string($item) || $item === '') {
                continue;
            }

            foreach ($keywords as $keyword) {
                if ($keyword === '') {
                    continue;
                }

                if ($this->stringContainsInsensitive($item, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function getRoleKeywords(string $envKey, array $defaults = []): array
    {
        $value = env($envKey);

        if ($value === null || $value === '') {
            return $defaults;
        }

        $parts = array_map(static function ($part) {
            return trim($part);
        }, explode(',', $value));

        return array_values(array_filter($parts, static function ($part) {
            return $part !== '';
        }));
    }

    private function stringContainsInsensitive(string $haystack, string $needle): bool
    {
        $haystackNormalized = $this->normalizeString($haystack);
        $needleNormalized = $this->normalizeString($needle);

        if ($needleNormalized === '') {
            return false;
        }

        return str_contains($haystackNormalized, $needleNormalized);
    }

    private function normalizeString(string $value): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }

    /**
     * @param  array<int, string>  $dns
     * @return array<int, string>
     */
    private function extractGroupNames(array $dns): array
    {
        $names = [];

        foreach ($dns as $dn) {
            if (! is_string($dn) || $dn === '') {
                continue;
            }

            if (preg_match('/CN=([^,]+)/u', $dn, $match)) {
                $names[] = $match[1];
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Determine if the LDAP account is active.
     */
    private function isLdapAccountActive(LdapUser $user): bool
    {
        if ($this->isAccountDisabled($user)) {
            return false;
        }

        if ($this->isAccountExpired($user)) {
            return false;
        }

        return true;
    }

    private function isAccountDisabled(LdapUser $user): bool
    {
        $uac = $user->getFirstAttribute('useraccountcontrol');

        if (! is_numeric($uac)) {
            return false;
        }

        $value = (int) $uac;

        // ACCOUNTDISABLE flag.
        return ($value & 0x0002) !== 0;
    }

    private function isAccountExpired(LdapUser $user): bool
    {
        $accountExpires = $user->getFirstAttribute('accountexpires');

        if (! is_numeric($accountExpires)) {
            return false;
        }

        $expires = (int) $accountExpires;

        // 0 or 9223372036854775807 mean "never expires".
        if ($expires === 0 || $expires === 0x7FFFFFFFFFFFFFFF) {
            return false;
        }

        $expiryMoment = $this->fileTimeToCarbon($expires);

        if (! $expiryMoment) {
            return false;
        }

        return $expiryMoment->isPast();
    }

    private function fileTimeToCarbon(int $filetime): ?Carbon
    {
        if ($filetime <= 0) {
            return null;
        }

        // Convert Windows FILETIME (100-nanosecond intervals since 1601-01-01)
        // to Unix timestamp (seconds since 1970-01-01).
        $unixTimestamp = intdiv($filetime, 10000000) - 11644473600;

        if ($unixTimestamp <= 0) {
            return null;
        }

        return Carbon::createFromTimestampUTC($unixTimestamp);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function isAllowedGroup(array $profile): bool
    {
        $role = $profile['role'] ?? null;
        if ($role !== 'teacher' && $role !== 'student') {
            return false;
        }

        $allowed = $this->getAllowedGroups();

        if ($allowed === []) {
            return true;
        }

        $normalizedAllowed = array_map([$this, 'normalizeString'], $allowed);

        // Допуск по роли: если в списке есть "teacher"/"teachers" или "student"/"students", разрешаем по роли из LDAP
        if ($role === 'teacher' && (in_array('teacher', $normalizedAllowed, true) || in_array('teachers', $normalizedAllowed, true))) {
            return true;
        }
        if ($role === 'student' && (in_array('student', $normalizedAllowed, true) || in_array('students', $normalizedAllowed, true))) {
            return true;
        }

        $groupName = $profile['group_name'] ?? null;

        if (is_string($groupName) && $groupName !== '') {
            if (in_array($this->normalizeString($groupName), $normalizedAllowed, true)) {
                return true;
            }
        }

        if (! empty($profile['member_of']) && is_array($profile['member_of'])) {
            foreach ($profile['member_of'] as $name) {
                if (! is_string($name) || $name === '') {
                    continue;
                }

                if (in_array($this->normalizeString($name), $normalizedAllowed, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedGroups(): array
    {
        // По умолчанию разрешены преподаватели (Преподаватели/teachers) и студенты (Студенты/students)
        $value = env('LDAP_ALLOWED_GROUPS', 'Преподаватели,teachers,Студенты,students');

        if ($value === null || $value === '') {
            return [];
        }

        $parts = array_map(static function ($part) {
            return trim($part);
        }, explode(',', $value));

        return array_values(array_filter($parts, static function ($part) {
            return $part !== '';
        }));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'avatar' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'avatar_cropped' => ['nullable', 'string'],
        ]);

        // На случай, если миграция ещё не была применена в текущей БД.
        $this->ensureAvatarColumnExists();

        if (filled($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = null;
        $croppedRaw = $validated['avatar_cropped'] ?? null;
        if (is_string($croppedRaw) && str_starts_with($croppedRaw, 'data:image/')) {
            $parts = explode(',', $croppedRaw, 2);
            $binary = isset($parts[1]) ? base64_decode($parts[1], true) : false;
            if ($binary === false) {
                return redirect()->back()->withErrors(['avatar' => 'Не удалось обработать кадрирование.'])->withInput();
            }
            $path = 'avatars/'.Str::uuid().'.png';
            Storage::disk('public')->put($path, $binary);
        } elseif (! empty($validated['avatar'])) {
            $path = $validated['avatar']->store('avatars', 'public');
        }

        if (! $path) {
            return redirect()->back()->withErrors(['avatar' => 'Выберите изображение для загрузки.'])->withInput();
        }
        $user->update(['avatar_path' => $path]);

        return redirect()->route('profile')->with('success', 'Аватар обновлён.');
    }

    public function editAvatar(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        return view('profile-avatar-edit', ['user' => $user]);
    }

    private function ensureAvatarColumnExists(): void
    {
        if (Schema::hasColumn('users', 'avatar_path')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable();
        });
    }
}
