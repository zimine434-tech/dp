<?php

namespace Database\Seeders\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;

trait UsesSeedUsers
{
    protected function seedTeacher(): ?User
    {
        return User::query()->where('role', 'teacher')->first();
    }

    protected function seedStudents(): Collection
    {
        return User::query()
            ->where('role', 'student')
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();
    }
}
