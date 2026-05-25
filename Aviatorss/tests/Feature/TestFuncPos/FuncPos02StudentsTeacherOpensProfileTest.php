<?php

namespace Tests\Feature\TestFuncPos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesStudentsTestUsers;
use Tests\TestCase;

class FuncPos02StudentsTeacherOpensProfileTest extends TestCase
{
    use CreatesStudentsTestUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStudentsTestUsers();
    }

    public function test_func_pos_02_students_teacher_opens_profile(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('students.show', $this->student));

        $response->assertOk();
        $response->assertSee($this->student->lastname, false);
        $response->assertSee($this->student->firstname, false);
    }
}
