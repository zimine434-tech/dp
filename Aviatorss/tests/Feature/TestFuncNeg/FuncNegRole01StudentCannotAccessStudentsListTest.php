<?php

namespace Tests\Feature\TestFuncNeg;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesStudentsTestUsers;
use Tests\TestCase;

class FuncNegRole01StudentCannotAccessStudentsListTest extends TestCase
{
    use CreatesStudentsTestUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStudentsTestUsers();
    }

    public function test_func_neg_role_01_student_cannot_open_students_list(): void
    {
        $response = $this->actingAs($this->student)->get(route('students.index'));

        $response->assertStatus(403);
    }
}
