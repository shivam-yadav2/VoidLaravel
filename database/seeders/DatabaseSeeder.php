<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the site's default content. Safe to re-run: every seeder uses
     * firstOrCreate and never overwrites admin edits.
     *
     * The admin login is NOT created here — run AdminUserSeeder explicitly
     * once ADMIN_EMAIL / ADMIN_PASSWORD are set in .env. (This used to create
     * a test@example.com / "password" account, which with the old 'admin'
     * role default was a ready-made backdoor.)
     */
    public function run(): void
    {
        $this->call([
            ContentSeeder::class,
            HomepageContentSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
