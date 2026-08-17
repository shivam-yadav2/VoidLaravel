<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Enables 2FA for the user, then logs the test client back out —
     * actingAs() leaves the guard authenticated for the rest of the test,
     * which would make the 'guest' middleware on /admin/login and /admin/2fa
     * silently redirect away instead of exercising the real login flow.
     */
    private function enableTwoFactorFor(User $user): string
    {
        $this->actingAs($user)->post('/admin/security/2fa/enable');

        $secret = session('2fa-setup-secret');

        $this->actingAs($user)
            ->withSession(['2fa-setup-secret' => $secret])
            ->post('/admin/security/2fa/confirm', ['code' => Totp::code($secret)]);

        $this->post('/admin/logout');

        return $secret;
    }

    public function test_full_setup_flow_enables_two_factor_and_issues_recovery_codes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/security/2fa/enable')->assertRedirect();
        $secret = session('2fa-setup-secret');
        $this->assertNotEmpty($secret);
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());

        $response = $this->actingAs($user)
            ->withSession(['2fa-setup-secret' => $secret])
            ->post('/admin/security/2fa/confirm', ['code' => Totp::code($secret)]);

        $response->assertRedirect(route('admin.security'));
        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_confirming_with_the_wrong_code_does_not_enable_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/admin/security/2fa/enable');
        $secret = session('2fa-setup-secret');

        $this->actingAs($user)
            ->withSession(['2fa-setup-secret' => $secret])
            ->post('/admin/security/2fa/confirm', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_login_requires_a_second_step_once_two_factor_is_enabled(): void
    {
        $user = User::factory()->create(['email' => 'two-factor@example.com', 'password' => bcrypt('password123')]);
        $secret = $this->enableTwoFactorFor($user);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        // Fresh, unauthenticated request: password alone must NOT log them in.
        $response = $this->post('/admin/login', [
            'email' => 'two-factor@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.2fa.challenge'));
        $this->assertGuest();

        // The challenge page itself must not leak into protected admin routes.
        $this->get('/admin/leads')->assertRedirect('/admin/login');
    }

    public function test_correct_totp_code_completes_login(): void
    {
        $user = User::factory()->create(['email' => 'two-factor@example.com', 'password' => bcrypt('password123')]);
        $secret = $this->enableTwoFactorFor($user);

        $this->post('/admin/login', ['email' => 'two-factor@example.com', 'password' => 'password123']);

        $response = $this->post('/admin/2fa', ['code' => Totp::code($secret)]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_totp_code_does_not_complete_login(): void
    {
        $user = User::factory()->create(['email' => 'two-factor@example.com', 'password' => bcrypt('password123')]);
        $this->enableTwoFactorFor($user);

        $this->post('/admin/login', ['email' => 'two-factor@example.com', 'password' => 'password123']);

        $this->post('/admin/2fa', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_a_recovery_code_completes_login_and_is_then_single_use(): void
    {
        $user = User::factory()->create(['email' => 'two-factor@example.com', 'password' => bcrypt('password123')]);
        $this->enableTwoFactorFor($user);
        $recoveryCode = $user->fresh()->two_factor_recovery_codes[0];

        $this->post('/admin/login', ['email' => 'two-factor@example.com', 'password' => 'password123']);
        $this->post('/admin/2fa', ['code' => $recoveryCode])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Used code no longer works for a second login.
        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => 'two-factor@example.com', 'password' => 'password123']);
        $this->post('/admin/2fa', ['code' => $recoveryCode])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_disabling_requires_the_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $this->enableTwoFactorFor($user);

        $this->actingAs($user)
            ->post('/admin/security/2fa/disable', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->actingAs($user)
            ->post('/admin/security/2fa/disable', ['password' => 'password123'])
            ->assertRedirect();
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_users_without_two_factor_log_in_normally_in_one_step(): void
    {
        $user = User::factory()->create(['email' => 'plain@example.com', 'password' => bcrypt('password123')]);

        $this->post('/admin/login', ['email' => 'plain@example.com', 'password' => 'password123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_editor_can_manage_their_own_two_factor_without_admin_role(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->get('/admin/security')->assertOk();
        $this->actingAs($editor)->post('/admin/security/2fa/enable')->assertRedirect();
    }

    public function test_two_factor_secret_is_never_exposed_in_json_serialization(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactorFor($user);

        $array = $user->fresh()->toArray();

        $this->assertArrayNotHasKey('two_factor_secret', $array);
        $this->assertArrayNotHasKey('password', $array);
    }
}
