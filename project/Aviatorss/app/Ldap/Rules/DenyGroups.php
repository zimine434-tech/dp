<?php

namespace App\Ldap\Rules;

use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Validation\Rule;
use LdapRecord\Models\Model;

class DenyGroups extends Rule
{
    public function passes(Model $user = null): bool
    {
        if (! $user) {
            return false;
        }

        $allowed = $this->getAllowedGroups();

        if ($allowed === []) {
            return true;
        }

        $normalizedAllowed = array_map([$this, 'normalize'], $allowed);

        $groupName = $this->resolveGroupName($user);

        if ($groupName && in_array($this->normalize($groupName), $normalizedAllowed, true)) {
            Log::info('LDAP allow by group name', [
                'dn' => (string) $user->getDn(),
                'group_name' => $groupName,
                'allowed' => $allowed,
            ]);

            return true;
        }

        $memberOf = (array) $user->getAttribute('memberof', []);

        foreach ($memberOf as $dn) {
            if (! is_string($dn) || $dn === '') {
                continue;
            }

            if (preg_match('/CN=([^,]+)/u', $dn, $match)) {
                $normalized = $this->normalize($match[1]);

                if (in_array($normalized, $normalizedAllowed, true)) {
                    Log::info('LDAP allow by memberOf', [
                        'dn' => (string) $user->getDn(),
                        'member' => $match[1],
                        'allowed' => $allowed,
                    ]);

                    return true;
                }
            }
        }

        Log::warning('LDAP denied by group rule', [
            'dn' => (string) $user->getDn(),
            'group_name' => $groupName,
            'allowed' => $allowed,
            'member_of' => $memberOf,
        ]);

        return false;
    }

    public function message(): string
    {
        return 'Вход запрещён.';
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedGroups(): array
    {
        $value = env('LDAP_ALLOWED_GROUPS', 'teachers,students');

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

    private function resolveGroupName(Model $user): ?string
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

        return null;
    }

    private function normalize(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }
}

