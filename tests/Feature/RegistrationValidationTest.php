<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Spec #85: automated coverage for the registration validation upgrade
 * (#73-76). Mirrors the exact test cases listed in the spec so a failing
 * assertion here maps directly back to a numbered requirement.
 */
class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    private function baseStudentPayload(array $overrides = []): array
    {
        return array_merge([
            'role' => 'student',
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'contact_number' => '1234567890',
            'department' => 'College of Computer Studies',
            'course_program' => 'BSIT',
            'school_or_employee_id' => '202600123',
            'year_level_position' => '1st Year',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ], $overrides);
    }

    /** Spec #85: Contact Number "1234567890" -> VALID */
    public function test_valid_contact_number_is_accepted(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload());

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
    }

    /** Spec #85: Contact Number "ABC123" -> INVALID */
    public function test_alphabetic_contact_number_is_rejected(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload(['contact_number' => 'ABC123']));

        $response->assertSessionHasErrors('contact_number');
        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    /** Spec #85: Contact Number "0917ABC1234" -> INVALID */
    public function test_mixed_alphanumeric_contact_number_is_rejected(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload(['contact_number' => '0917ABC1234']));

        $response->assertSessionHasErrors('contact_number');
    }

    /** Spec #85: School ID "202600123" -> VALID */
    public function test_valid_school_id_is_accepted(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload());

        $response->assertRedirect(route('login'));
    }

    /** Spec #85: School ID "ABC2026" -> INVALID */
    public function test_alphabetic_school_id_is_rejected(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload(['school_or_employee_id' => 'ABC2026']));

        $response->assertSessionHasErrors('school_or_employee_id');
    }

    /** Spec #74: leading zeroes in a School ID must survive exactly as entered. */
    public function test_school_id_leading_zeroes_are_preserved(): void
    {
        $this->post('/register', $this->baseStudentPayload(['school_or_employee_id' => '00123456']));

        $this->assertDatabaseHas('users', ['school_or_employee_id' => '00123456']);
    }

    /** Spec #85: Year Level "7" / "11" -> VALID (i.e. any option from the real list) */
    public function test_valid_year_level_is_accepted(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload(['year_level_position' => '2nd Year']));

        $response->assertRedirect(route('login'));
    }

    /** Spec #85: Year Level "ABC" -> INVALID */
    public function test_non_numeric_year_level_is_rejected(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload(['year_level_position' => 'ABC']));

        $response->assertSessionHasErrors('year_level_position');
    }

    /** Spec #85: Year Level "99" -> INVALID (not one of the school's real levels) */
    public function test_out_of_range_year_level_is_rejected(): void
    {
        $response = $this->post('/register', $this->baseStudentPayload(['year_level_position' => '99']));

        $response->assertSessionHasErrors('year_level_position');
    }

    /** Spec #77: a brand-new registration must be PENDING, not immediately active. */
    public function test_new_registration_is_pending_not_verified(): void
    {
        $this->post('/register', $this->baseStudentPayload());

        $user = User::where('email', 'juan@example.com')->first();

        $this->assertNotNull($user);
        $this->assertFalse($user->is_verified);
        $this->assertSame('pending', $user->accountStatus());
    }
}
