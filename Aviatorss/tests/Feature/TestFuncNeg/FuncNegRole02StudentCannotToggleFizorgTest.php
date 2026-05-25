<?php

namespace Tests\Feature\TestFuncNeg;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesStudentsTestUsers;
use Tests\TestCase;

class FuncNegRole02StudentCannotToggleFizorgTest extends TestCase
{
    use CreatesStudentsTestUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStudentsTestUsers();
    }

    public function test_func_neg_role_02_student_cannot_toggle_fizorg(): void
    {
        $response = $this->actingAs($this->student)->post(
            route('students.toggle-fizorg', $this->student)
        );

        $response->assertStatus(403);

        $this->assertFalse($this->student->fresh()->status_fizorg);
    }
}
