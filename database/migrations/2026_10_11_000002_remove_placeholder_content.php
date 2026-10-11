<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * One-off clean-up of demo content that HomepageContentSeeder used to put on
 * the live homepage. Each change only touches rows that still hold the exact
 * original placeholder value, so anything an admin has since edited is left
 * alone.
 */
return new class extends Migration
{
    private const PLACEHOLDER_QUOTE = 'Grate template & grate code — I love the modern designs with many different styles to choose.';

    public function up(): void
    {
        // The theme's fake testimonials (Theresa Webb, "Dianne Russelln",
        // Wade Warren). The section hides itself when none are active.
        DB::table('testimonials')
            ->where('quote', self::PLACEHOLDER_QUOTE)
            ->whereIn('client_name', ['Theresa Webb', 'Dianne Russelln', 'Wade Warren'])
            ->delete();

        $fixes = [
            'contact_hours' => ['8/7', 'Mon–Sat, 10 AM – 7 PM IST'],
            'contact_phone' => ['9415729564', '+91 94157 29564'],
        ];

        foreach ($fixes as $key => [$old, $new]) {
            DB::table('settings')->where('key', $key)->where('value', $old)->update(['value' => $new]);
        }

        Cache::forget('settings.all');

        // The seeded privacy policy claimed "a single session cookie … not
        // used for tracking", which stops being true once analytics are on.
        // Swapped only if that exact seeded paragraph is still present.
        $oldCookies = '<p>The site uses a single session cookie required for form security (CSRF protection). It is not used for tracking or profiling.</p>';
        $newCookies = '<p>The site always sets two essential cookies: a session cookie and a security token that protects forms (CSRF protection). Neither is used for tracking or profiling.</p>
<p>If we enable analytics (such as Google Analytics or the Meta Pixel), those tools set their own cookies <strong>only after you click &ldquo;Accept&rdquo;</strong> on the cookie banner. If you decline, they are never loaded. You can reset your choice at any time by clearing this site&rsquo;s data in your browser.</p>';

        DB::table('pages')
            ->where('slug', 'privacy-policy')
            ->where('content', 'like', '%'.$oldCookies.'%')
            ->get(['id', 'content'])
            ->each(fn ($page) => DB::table('pages')->where('id', $page->id)->update([
                'content' => str_replace($oldCookies, $newCookies, $page->content),
                'updated_at' => now(),
            ]));

        Cache::forget('pages.footer_links');
    }

    public function down(): void
    {
        // Deliberately irreversible: restoring fake testimonials is never wanted.
    }
};
