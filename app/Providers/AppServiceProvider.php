<?php

namespace App\Providers;

use App\Models\Page;
use App\Support\SiteConfig;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteConfig::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Only in production, so local http:// development is unaffected —
        // forcing https:// there would break every generated link.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Admin-managed contact details, socials and copy, available to every
        // view as `$site` so partials never have to be passed them explicitly.
        View::share('site', $this->app->make(SiteConfig::class));

        // Footer policy links. Cached and busted by the Page model on write, so
        // rendering the footer costs no query.
        View::composer('partials.support-cta', function ($view) {
            $view->with('footerPages', Page::footerLinks());
        });
    }
}
