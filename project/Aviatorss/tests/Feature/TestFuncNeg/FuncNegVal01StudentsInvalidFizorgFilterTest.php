<?php

namespace Tests\Feature\TestFuncNeg;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesStudentsTestUsers;
use Tests\TestCase;

class FuncNegVal01StudentsInvalidFizorgFilterTest extends TestCase
{
    use CreatesStudentsTestUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStudentsTestUsers();
    }

    public function test_func_neg_val_01_students_rejects_invalid_fizorg_filter(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('students.index', [
            'fizorg' => 'not_a_valid_filter',
        ]));

        $response->assertSessionHasErrors(['fizorg']);
    }
}
