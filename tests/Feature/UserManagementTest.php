<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_manager_can_see_the_team_page(): void
    {
        $this->actingAs($this->userWithRole('manager'))
            ->get('/users')
            ->assertOk();
    }

    public function test_manager_can_add_a_member(): void
    {
        $manager = $this->userWithRole('manager');

        $this->actingAs($manager)
            ->post('/users', [
                'name' => 'Selam Tesfaye',
                'email' => 'selam@example.com',
                'password' => 'Secret-pass-123',
                'role' => 'member',
            ])
            ->assertRedirect('/users');

        $created = User::where('email', 'selam@example.com')->firstOrFail();

        $this->assertSame('Selam Tesfaye', $created->name);
        $this->assertTrue($created->hasRole('member'));
        $this->assertFalse($created->hasRole('manager'));
        $this->assertTrue(Hash::check('Secret-pass-123', $created->password));
        $this->assertNotNull($created->email_verified_at);
    }

    public function test_new_user_can_log_in_with_the_password_the_manager_set(): void
    {
        $this->actingAs($this->userWithRole('manager'))->post('/users', [
            'name' => 'Selam Tesfaye',
            'email' => 'selam@example.com',
            'password' => 'Secret-pass-123',
            'role' => 'member',
        ]);

        auth()->logout();

        $this->post('/login', [
            'email' => 'selam@example.com',
            'password' => 'Secret-pass-123',
        ])->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->userWithRole('manager'))
            ->post('/users', [
                'name' => 'Someone',
                'email' => 'taken@example.com',
                'password' => 'Secret-pass-123',
                'role' => 'member',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_role_must_be_member_or_manager(): void
    {
        $this->actingAs($this->userWithRole('manager'))
            ->post('/users', [
                'name' => 'Someone',
                'email' => 'someone@example.com',
                'password' => 'Secret-pass-123',
                'role' => 'admin',
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_member_cannot_see_or_use_the_team_page(): void
    {
        $member = $this->userWithRole('member');

        $this->actingAs($member)->get('/users')->assertForbidden();

        $this->actingAs($member)
            ->post('/users', [
                'name' => 'Sneaky',
                'email' => 'sneaky@example.com',
                'password' => 'Secret-pass-123',
                'role' => 'manager',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }
}
