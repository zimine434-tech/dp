<?php

namespace App\Concerns;

use Carbon\Carbon;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * Общая логика отбора учётных записей студентов в AD (как при поиске для команды).
 */
trait InteractsWithLdapStudentDirectory
{
    /**
     * Проверяет OU/атрибуты и возвращает данные для локальной записи студента или null, если запись не подходит.
     *
     * @return array{firstname: string, lastname: string, patronymic: ?string, login: string, group_name: ?string, dn: string}|null
     */
    protected function ldapUserToStudentCandidate(LdapUser $ldapUser): ?array
    {
        $dnOriginal = $ldapUser->getDn() ?? '';
        $dnLower = mb_strtolower($dnOriginal, 'UTF-8');

        $cn = $this->getFirstAttribute($ldapUser, ['cn', 'name', 'displayname']) ?? '';
        $cnLower = mb_strtolower($cn, 'UTF-8');

        $isExcluded = $this->isInExcludedOu($dnOriginal)
            || $this->isInExcludedOu($dnLower)
            || $this->isInExcludedOuSimple($dnOriginal)
            || $this->isInExcludedOuSimple($dnLower)
            || $this->hasExcludedMarkerInCn($cn)
            || $this->hasExcludedMarkerInCn($cnLower);

        if ($isExcluded) {
            return null;
        }

        $isInStudentsOu = false;

        if (preg_match_all('/ou\s*=\s*([^,]+)/i', $dnOriginal, $allMatches)) {
            foreach ($allMatches[1] as $ouName) {
                $ouNameLower = mb_strtolower(trim($ouName), 'UTF-8');

                if (
                    (stripos($ouNameLower, 'студенты') !== false || stripos($ouNameLower, 'students') !== false)
                    && stripos($ouNameLower, 'до') === false
                    && stripos($ouNameLower, 'дпо') === false
                    && stripos($ouNameLower, 'по') === false
                    && stripos($ouNameLower, 'отм') === false
                ) {
                    $isInStudentsOu = true;
                    break;
                }
            }
        }

        if (! $isInStudentsOu) {
            return null;
        }

        if (! $this->isUserInAllowedOu($ldapUser)) {
            return null;
        }

        $firstname = $this->getFirstAttribute($ldapUser, ['givenname', 'givenName', 'gn']);
        $lastname = $this->getFirstAttribute($ldapUser, ['sn', 'surname']);
        $patronymic = $this->getFirstAttribute($ldapUser, ['middlename', 'initials']);
        $commonName = $this->getFirstAttribute($ldapUser, ['cn', 'name', 'displayname']);
        [$firstname, $lastname, $patronymic] = $this->normalizeLdapNames($firstname, $lastname, $patronymic, $commonName);
        $login = $this->getFirstAttribute($ldapUser, ['samaccountname']);

        if (! $firstname || ! $lastname || ! $login) {
            return null;
        }

        $dnFull = $ldapUser->getDn() ?? '';
        $groupName = $this->resolveGroupName($ldapUser) ?: $this->extractGroupFromDn($dnFull);

        return [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'patronymic' => $patronymic,
            'login' => $login,
            'group_name' => $groupName,
            'dn' => $dnFull,
        ];
    }

    protected function getFirstAttribute(LdapUser $user, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $user->getFirstAttribute($key);
            if ($value) {
                return $this->sanitizeLdapString($value);
            }
        }

        return null;
    }

    protected function normalizeLdapNames(?string $firstname, ?string $lastname, ?string $patronymic, ?string $commonName): array
    {
        $first = $firstname ? trim($firstname) : null;
        $last = $lastname ? trim($lastname) : null;
        $middle = $patronymic ? trim($patronymic) : null;
        $cn = $commonName ? trim($commonName) : null;

        if ($first && str_contains($first, ' ')) {
            $parts = array_values(array_filter(preg_split('/\s+/u', $first) ?: []));
            if (! empty($parts)) {
                $first = array_shift($parts);
                if (! $middle && ! empty($parts)) {
                    $middle = implode(' ', $parts);
                }
            }
        }

        if ((! $first || ! $last) && $cn) {
            $this->fillNamesFromCommonName($first, $last, $middle, $cn);
        } elseif (! $middle && $cn) {
            $this->fillMiddleNameFromCommonName($middle, $cn);
        }

        return [$first, $last, $middle];
    }

    protected function fillNamesFromCommonName(?string &$firstname, ?string &$lastname, ?string &$patronymic, ?string $commonName): void
    {
        if (! $commonName) {
            return;
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', trim($commonName)) ?: []));
        if (empty($parts)) {
            return;
        }

        if (! $lastname) {
            $lastname = array_shift($parts);
        }

        if (! $firstname && ! empty($parts)) {
            $firstname = array_shift($parts);
        }

        if (! $patronymic && ! empty($parts)) {
            $patronymic = implode(' ', $parts);
        }
    }

    protected function fillMiddleNameFromCommonName(?string &$patronymic, ?string $commonName): void
    {
        if (! $commonName || $patronymic) {
            return;
        }

        $parts = array_values(array_filter(preg_split('/\s+/u', trim($commonName)) ?: []));
        if (count($parts) >= 3) {
            $patronymic = implode(' ', array_slice($parts, 2));
        }
    }

    protected function sanitizeLdapString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace(["\xC2\xA0", "\xE2\x80\xAF"], ' ', $value);
        $normalized = preg_replace('/\s+/u', ' ', $normalized ?? '') ?? '';
        $normalized = trim($normalized);

        return $normalized === '' ? null : $normalized;
    }

    protected function extractGroupFromDn(?string $dn): ?string
    {
        if (! $dn) {
            return null;
        }

        if (preg_match('/OU=([^,]+)/i', $dn, $matches)) {
            $value = trim($matches[1]);

            return $value !== '' ? $value : null;
        }

        return null;
    }

    protected function getAllowedOus(): array
    {
        $value = env('LDAP_ALLOWED_OUS', 'Студенты|ДО|ДПО|ОТМ|ПО');
        if (! $value) {
            return [];
        }

        $normalized = str_replace(';', ',', $value);
        $parts = array_filter(array_map('trim', explode(',', $normalized)));

        return array_map(function ($part) {
            $segments = array_map('trim', explode('|', $part));
            $segments = array_map(function ($segment) {
                if ($segment === '') {
                    return '';
                }
                if (stripos($segment, 'ou=') === 0 || stripos($segment, 'dc=') === 0) {
                    return $segment;
                }

                return 'OU='.$segment;
            }, $segments);

            return implode('|', $segments);
        }, $parts);
    }

    protected function isUserInAllowedOu(LdapUser $user): bool
    {
        $dn = strtolower($user->getDn() ?? '');
        $allowed = $this->getAllowedOus();

        if (empty($allowed) || ! $dn) {
            return true;
        }

        foreach ($allowed as $ou) {
            $ouLower = strtolower($ou);

            $parts = explode('|', $ouLower);
            $ouName = trim($parts[0]);
            $excludes = array_filter(array_map('trim', array_slice($parts, 1)));

            if ($ouName === '' || ! str_contains($dn, $ouName)) {
                continue;
            }

            $excludeMatch = false;
            foreach ($excludes as $exclude) {
                if ($exclude !== '' && str_contains($dn, $exclude)) {
                    $excludeMatch = true;
                    break;
                }
            }

            if (! $excludeMatch) {
                return true;
            }
        }

        return false;
    }

    protected function isInExcludedOu(string $dn): bool
    {
        if (empty($dn)) {
            return false;
        }

        $excludedOus = ['до', 'ДО', 'дпо', 'ДПО', 'отм', 'ОТМ', 'по', 'ПО'];
        $dnLower = mb_strtolower($dn, 'UTF-8');

        foreach ($excludedOus as $excludedOu) {
            $excludedOuLower = mb_strtolower($excludedOu, 'UTF-8');

            if (
                preg_match('/ou\s*=\s*'.preg_quote($excludedOuLower, '/').'(?=[,\s]|$)/i', $dn)
                || preg_match('/ou\s*=\s*'.preg_quote($excludedOu, '/').'(?=[,\s]|$)/i', $dn)
                || stripos($dn, 'ou='.$excludedOuLower) !== false
                || stripos($dn, 'ou='.$excludedOu) !== false
            ) {
                return true;
            }
        }

        return false;
    }

    protected function isInExcludedOuSimple(string $dn): bool
    {
        if (empty($dn)) {
            return false;
        }

        $excludedOus = ['до', 'ДО', 'дпо', 'ДПО', 'отм', 'ОТМ', 'по', 'ПО'];
        $dnLower = mb_strtolower($dn, 'UTF-8');

        foreach ($excludedOus as $excludedOu) {
            $excludedOuLower = mb_strtolower($excludedOu, 'UTF-8');

            if (
                stripos($dnLower, 'ou='.$excludedOuLower) !== false
                || stripos($dnLower, 'ou ='.$excludedOuLower) !== false
                || stripos($dnLower, 'ou= '.$excludedOuLower) !== false
                || stripos($dnLower, 'ou = '.$excludedOuLower) !== false
            ) {
                return true;
            }

            if (preg_match('/ou\s*=\s*'.preg_quote($excludedOuLower, '/').'(?=[,\s]|$)/i', $dn)) {
                return true;
            }
        }

        return false;
    }

    protected function hasExcludedMarkerInCn(string $cn): bool
    {
        if (empty($cn)) {
            return false;
        }

        $excludedMarkers = ['(до)', '(дпо)', '(отм)', '(по)', '(ДО)', '(ДПО)', '(ОТМ)', '(ПО)'];

        foreach ($excludedMarkers as $marker) {
            if (stripos($cn, $marker) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function resolveGroupName(LdapUser $user): ?string
    {
        $groupAttribute = env('LDAP_GROUP_NAME_ATTRIBUTE', '');
        if (empty($groupAttribute)) {
            return null;
        }

        $value = $user->getFirstAttribute($groupAttribute);

        return $value ? trim($value) : null;
    }

    protected function studentsBaseDn(): string
    {
        $configured = env('LDAP_STUDENTS_BASE_DN');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $base = env('LDAP_BASE_DN', 'DC=iat,DC=iat');

        return 'OU=Студенты,'.$base;
    }

    protected function isLdapAccountActive(LdapUser $user): bool
    {
        if ($this->isLdapAccountDisabled($user)) {
            return false;
        }

        if ($this->isLdapAccountExpired($user)) {
            return false;
        }

        return true;
    }

    protected function isLdapAccountDisabled(LdapUser $user): bool
    {
        $uac = $user->getFirstAttribute('useraccountcontrol');

        if (! is_numeric($uac)) {
            return false;
        }

        return ((int) $uac & 0x0002) !== 0;
    }

    protected function isLdapAccountExpired(LdapUser $user): bool
    {
        $accountExpires = $user->getFirstAttribute('accountexpires');

        if (! is_numeric($accountExpires)) {
            return false;
        }

        $expires = (int) $accountExpires;

        if ($expires === 0 || $expires === 0x7FFFFFFFFFFFFFFF) {
            return false;
        }

        $expiryMoment = $this->ldapFileTimeToCarbon($expires);

        return $expiryMoment?->isPast() ?? false;
    }

    protected function ldapFileTimeToCarbon(int $filetime): ?Carbon
    {
        if ($filetime <= 0) {
            return null;
        }

        $unixTimestamp = intdiv($filetime, 10000000) - 11644473600;

        if ($unixTimestamp <= 0) {
            return null;
        }

        return Carbon::createFromTimestampUTC($unixTimestamp);
    }
}
