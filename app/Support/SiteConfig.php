<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Read-side facade over the `settings` table, shared with every view as `$site`.
 *
 * Resolution order: database value -> explicit default -> config/site.php.
 * Reads hit a forever-cache that the Setting model busts on write, so putting
 * `$site->contact_email` in a Blade partial costs nothing per render.
 */
class SiteConfig
{
    public function get(string $key, ?string $default = null): ?string
    {
        return Setting::get($key) ?? $default ?? config("site.defaults.{$key}");
    }

    public function __get(string $key): ?string
    {
        return $this->get($key);
    }

    public function has(string $key): bool
    {
        return filled($this->get($key));
    }

    /**
     * Social links that are actually configured, ready to loop in the footer.
     *
     * @return array<string, string>  fontawesome icon class => url
     */
    public function socialLinks(): array
    {
        $map = [
            'social_instagram' => 'fa-brands fa-instagram',
            'social_linkedin' => 'fa-brands fa-linkedin-in',
            'social_whatsapp' => 'fa-brands fa-whatsapp',
            'social_facebook' => 'fa-brands fa-facebook-f',
            'social_twitter' => 'fa-brands fa-x-twitter',
        ];

        $links = [];

        foreach ($map as $key => $icon) {
            if ($url = $this->get($key)) {
                $links[$icon] = $url;
            }
        }

        return $links;
    }
}
