<?php

namespace Tests\Feature\TestFuncPos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesStudentsTestUsers;
use Tests\TestCase;

class FuncPos01StudentsTeacherOpensListTest extends TestCase
{
    use CreatesStudentsTestUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStudentsTestUsers();
    }

    public function test_func_pos_01_students_teacher_opens_list(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('students.index'));

        $response->assertOk();
        $response->assertSee('Студенты', false);
        $response->assertSee($this->student->lastname, false);
    }
}
