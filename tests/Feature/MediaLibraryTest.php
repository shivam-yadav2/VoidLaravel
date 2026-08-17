<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_multiple_images_at_once(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/media', [
            'files' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
            ],
        ])->assertRedirect();

        $this->assertDatabaseCount('media', 2);
        Media::each(fn ($m) => Storage::disk('public')->assertExists($m->path));
    }

    public function test_deleting_media_removes_the_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $path = UploadedFile::fake()->image('x.jpg')->store('media', 'public');
        $media = Media::create(['path' => $path, 'original_name' => 'x.jpg', 'mime_type' => 'image/jpeg', 'size' => 100]);

        $this->actingAs($admin)->delete("/admin/media/{$media->id}")->assertRedirect();

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_editor_can_access_media_library(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->get('/admin/media')->assertOk();
    }

    public function test_guests_cannot_upload(): void
    {
        $this->post('/admin/media', [])->assertRedirect('/admin/login');
    }
}
