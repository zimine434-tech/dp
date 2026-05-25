<?php

namespace Tests\Feature\TestFuncPos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesStudentsTestUsers;
use Tests\TestCase;

class FuncPos03StudentsTeacherAssignsFizorgTest extends TestCase
{
    use CreatesStudentsTestUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStudentsTestUsers();
    }

    public function test_func_pos_03_students_teacher_assigns_fizorg(): void
    {
        $this->assertFalse($this->student->fresh()->status_fizorg);

        $response = $this->actingAs($this->teacher)->post(
            route('students.toggle-fizorg', $this->student)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Статус физорга успешно установлен!');

        $this->assertTrue($this->student->fresh()->status_fizorg);
    }
}
