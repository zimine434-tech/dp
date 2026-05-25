<?php

namespace Tests\Feature\Concerns;

use App\Models\User;

trait CreatesStudentsTestUsers
{
    protected User $teacher;

    protected User $student;

    protected function setUpStudentsTestUsers(): void
    {
        $this->teacher = User::factory()->create([
            'role' => 'teacher',
            'active' => true,
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'active' => true,
            'group_name' => 'ИС-221',
            'status_fizorg' => false,
        ]);
    }
}
