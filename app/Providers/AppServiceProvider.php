<?php

namespace App\Providers;

use App\Models\Faq;
use App\Models\FeatureCard;
use App\Models\Page;
use App\Models\Product;
use App\Models\Stat;
use App\Models\TechBadge;
use App\Models\Testimonial;
use App\Support\SiteConfig;
use Carbon\Carbon;
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

        // `$date->displayTz()` — a copy converted from the UTC storage
        // timezone into the business's local one (config app.display_timezone).
        Carbon::macro('displayTz', function () {
            return $this->copy()->setTimezone(config('app.display_timezone'));
        });

        // Admin-managed contact details, socials and copy, available to every
        // view as `$site` so partials never have to be passed them explicitly.
        View::share('site', $this->app->make(SiteConfig::class));

        // Footer policy links. Cached and busted by the Page model on write, so
        // rendering the footer costs no query.
        View::composer('partials.support-cta', function ($view) {
            $view->with('footerPages', Page::footerLinks());
        });

        // CMS-driven homepage/about/services sections get their data here
        // rather than querying inside the Blade partial.
        View::composer('sections.landing.products', function ($view) {
            $view->with('products', Product::published()->with('images')->ordered()->take(6)->get());
            $view->with('productTotal', Product::published()->count());
        });
        View::composer('sections.landing.faq', fn ($view) => $view->with('faqs', Faq::active()->get()));
        View::composer('sections.landing.testimonial', fn ($view) => $view->with('testimonials', Testimonial::active()->get()));
        View::composer('sections.landing.feature-highlights', fn ($view) => $view->with('badges', TechBadge::active()->get()));
        View::composer('sections.about.about-stats', fn ($view) => $view->with('stats', Stat::active()->get()));
        View::composer('sections.services.services-list', fn ($view) => $view->with('cards', FeatureCard::group(FeatureCard::GROUP_SERVICES)->active()->get()));
        View::composer('sections.about.about-values', fn ($view) => $view->with('cards', FeatureCard::group(FeatureCard::GROUP_ABOUT_VALUES)->active()->get()));
    }
}
