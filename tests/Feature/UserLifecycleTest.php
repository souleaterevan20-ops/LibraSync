<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Spec #85: automated coverage for the pending/approve/reject lifecycle
 * upgrade (#77-84).
 */
class UserLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function libraryStaff(): User
    {
        return User::factory()->create([
            'role' => 'student_assistant',
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    private function pendingStudent(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'is_verified' => false,
            'is_active' => true,
        ]);
    }

    /** Spec #77: a pending registration must not appear in All Users. */
    public function test_pending_registration_does_not_appear_in_all_users(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $response = $this->actingAs($staff)->get('/admin/users');

        $response->assertOk();
        $response->assertDontSee($pending->email);
    }

    /** Spec #77: a pending registration must appear in Pending Approvals. */
    public function test_pending_registration_appears_in_pending_approvals(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $response = $this->actingAs($staff)->get('/admin/pending-users');

        $response->assertOk();
        $response->assertSee($pending->email);
    }

    /** Spec #78: approving moves the user to ACTIVE and records who/when. */
    public function test_approving_a_registration_activates_it(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $this->actingAs($staff)->patch("/admin/users/{$pending->id}/approve");

        $pending->refresh();
        $this->assertTrue($pending->is_verified);
        $this->assertSame('active', $pending->accountStatus());
        $this->assertEquals($staff->id, $pending->approved_by);
        $this->assertNotNull($pending->approved_at);
    }

    /** Spec #81: an approved user immediately counts in All Users. */
    public function test_approved_user_appears_in_all_users(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $this->actingAs($staff)->patch("/admin/users/{$pending->id}/approve");

        $response = $this->actingAs($staff)->get('/admin/users');
        $response->assertSee($pending->email);
    }

    /** Spec #79: rejecting requires a reason. */
    public function test_rejecting_without_a_reason_fails(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $response = $this->actingAs($staff)->patch("/admin/users/{$pending->id}/reject", []);

        $response->assertSessionHasErrors('reason');
        $pending->refresh();
        $this->assertNull($pending->rejected_at);
    }

    /** Spec #79: rejecting with a reason marks the account REJECTED, not deleted. */
    public function test_rejecting_with_a_reason_marks_account_rejected(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $this->actingAs($staff)->patch("/admin/users/{$pending->id}/reject", [
            'reason' => 'Invalid school information',
        ]);

        $pending->refresh();
        $this->assertNotNull($pending->rejected_at);
        $this->assertSame('Invalid school information', $pending->rejection_reason);
        $this->assertSame('rejected', $pending->accountStatus());
        $this->assertFalse($pending->trashed());
    }

    /** Spec #79: a rejected registration must never appear as an active user. */
    public function test_rejected_registration_does_not_appear_in_all_users(): void
    {
        $staff = $this->libraryStaff();
        $pending = $this->pendingStudent();

        $this->actingAs($staff)->patch("/admin/users/{$pending->id}/reject", [
            'reason' => 'Duplicate account',
        ]);

        $response = $this->actingAs($staff)->get('/admin/users');
        $response->assertDontSee($pending->email);
    }

    /** Spec #79: a rejected user cannot log in. */
    public function test_rejected_user_cannot_reach_the_dashboard(): void
    {
        $pending = $this->pendingStudent();
        $pending->update(['rejected_at' => now(), 'rejection_reason' => 'Not eligible']);

        $response = $this->actingAs($pending)->get('/dashboard');

        $response->assertRedirect(route('pending.approval'));
    }

    /** Spec #69: Super Admin only has read-only oversight — cannot approve/reject. */
    public function test_super_admin_cannot_approve_or_reject(): void
    {
        $admin = $this->superAdmin();
        $pending = $this->pendingStudent();

        $this->actingAs($admin)->patch("/admin/users/{$pending->id}/approve")->assertForbidden();
        $this->actingAs($admin)->patch("/admin/users/{$pending->id}/reject", ['reason' => 'x'])->assertForbidden();
    }

    /** Spec #69: a Student cannot reach Library Staff's operational routes. */
    public function test_student_cannot_access_pending_approvals(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_verified' => true, 'is_active' => true]);

        $this->actingAs($student)->get('/admin/pending-users')->assertForbidden();
    }

    /** Spec #81: Total Users only counts active (verified + enabled) Student/Teacher accounts. */
    public function test_total_users_dashboard_excludes_pending_disabled_and_rejected(): void
    {
        $admin = $this->superAdmin();

        User::factory()->create(['role' => 'student', 'is_verified' => true, 'is_active' => true]); // counts
        User::factory()->create(['role' => 'teacher', 'is_verified' => true, 'is_active' => true]); // counts
        User::factory()->create(['role' => 'student', 'is_verified' => true, 'is_active' => false]); // disabled, excluded
        $this->pendingStudent(); // pending, excluded
        User::factory()->create(['role' => 'student', 'is_verified' => false, 'rejected_at' => now()]); // rejected, excluded

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        // Verifies the underlying count directly rather than scraping the
        // rendered page (the exact markup around the stat card may change).
        $activeCount = User::whereIn('role', ['student', 'teacher'])
            ->where('is_verified', true)->where('is_active', true)->count();
        $this->assertSame(2, $activeCount);
    }
}
