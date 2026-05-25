<?php

namespace App\Ldap\Rules;

use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Validation\Rule;
use LdapRecord\Models\Model;

class AllowOnlyTeachersAndStudents extends Rule
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

        $memberOf = (array) $user->getAttribute('memberof', []);

        foreach ($memberOf as $dn) {
            if (! is_string($dn) || $dn === '') {
                continue;
            }

            if (preg_match('/CN=([^,]+)/u', $dn, $match)) {
                if (in_array($this->normalize($match[1]), $normalizedAllowed, true)) {
                    return true;
                }
            }
        }

        $dn = (string) $user->getDn();

        Log::warning('LDAP denied by role rule', [
            'dn' => $dn,
            'member_of' => $memberOf,
            'allowed' => $allowed,
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

    private function normalize(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }
}

