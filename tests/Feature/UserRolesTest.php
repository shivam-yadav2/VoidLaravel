<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_reach_content_but_not_admin_only_sections(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->get('/admin/leads')->assertOk();
        $this->actingAs($editor)->get('/admin/posts')->assertOk();
        $this->actingAs($editor)->get('/admin/pages')->assertOk();
        $this->actingAs($editor)->get('/admin/testimonials')->assertOk();

        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
        $this->actingAs($editor)->get('/admin/seo')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_reach_everything(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/seo')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_editor_cannot_create_or_delete_users_even_by_direct_post(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $target = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->post('/admin/users', [
            'name' => 'Sneaky', 'email' => 'sneaky@x.io', 'password' => 'password123', 'role' => 'admin',
        ])->assertForbidden();

        $this->actingAs($editor)->delete("/admin/users/{$target->id}")->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@x.io']);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_the_only_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'editor',
        ]);

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_the_only_admin_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $other = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // A second admin CAN delete the first — only the *last* admin is protected.
        $this->actingAs($other)->delete("/admin/users/{$admin->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);

        // Now $other is the only admin left — deleting themselves must be blocked.
        $this->actingAs($other)->delete("/admin/users/{$other->id}");
        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }

    public function test_admin_can_promote_and_demote_other_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($admin)->put("/admin/users/{$editor->id}", [
            'name' => $editor->name, 'email' => $editor->email, 'role' => 'admin',
        ]);

        $this->assertSame('admin', $editor->fresh()->role);
    }

    public function test_updating_a_user_without_a_password_keeps_the_old_one(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $originalHash = $editor->password;

        $this->actingAs($admin)->put("/admin/users/{$editor->id}", [
            'name' => 'Renamed', 'email' => $editor->email, 'role' => 'editor', 'password' => '',
        ]);

        $this->assertSame($originalHash, $editor->fresh()->password);
        $this->assertSame('Renamed', $editor->fresh()->name);
    }

    public function test_sidebar_hides_admin_only_links_from_editors(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $html = $this->actingAs($editor)->get('/admin/leads')->getContent();

        $this->assertStringNotContainsString('href="'.route('admin.settings.edit').'"', $html);
        $this->assertStringNotContainsString('href="'.route('admin.users.index').'"', $html);
    }
}
