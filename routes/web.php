<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\FeatureCardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\TechBadgeController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/services', [SiteController::class, 'services'])->name('services');
Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('contact.store');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        // Named `login` so Laravel's default unauthenticated redirect resolves.
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:6,1')
            ->name('login.attempt');

        // Second step of login when the account has 2FA enabled. Still
        // 'guest' middleware — Auth::login() hasn't run yet at this point,
        // see AuthController::login().
        Route::get('/2fa', [AuthController::class, 'showChallenge'])->name('admin.2fa.challenge');
        Route::post('/2fa', [AuthController::class, 'verifyChallenge'])
            ->middleware('throttle:6,1')
            ->name('admin.2fa.verify');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Leads
        Route::get('/leads', [LeadController::class, 'index'])->name('admin.leads.index');
        Route::get('/leads/export', [LeadController::class, 'export'])->name('admin.leads.export');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('admin.leads.show');
        Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('admin.leads.update');
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('admin.leads.destroy');

        // Blog
        Route::get('/posts', [AdminPostController::class, 'index'])->name('admin.posts.index');
        Route::get('/posts/create', [AdminPostController::class, 'create'])->name('admin.posts.create');
        Route::post('/posts', [AdminPostController::class, 'store'])->name('admin.posts.store');
        Route::get('/posts/{post:id}/edit', [AdminPostController::class, 'edit'])->name('admin.posts.edit');
        Route::put('/posts/{post:id}', [AdminPostController::class, 'update'])->name('admin.posts.update');
        Route::delete('/posts/{post:id}', [AdminPostController::class, 'destroy'])->name('admin.posts.destroy');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('/categories/{category:id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category:id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

        // Own products (SaaS, apps) — card grid + detail modal on the site
        Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('admin.products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
        Route::get('/products/{product:id}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/products/{product:id}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{product:id}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

        // Content pages (privacy, terms, …)
        Route::get('/pages', [AdminPageController::class, 'index'])->name('admin.pages.index');
        Route::get('/pages/create', [AdminPageController::class, 'create'])->name('admin.pages.create');
        Route::post('/pages', [AdminPageController::class, 'store'])->name('admin.pages.store');
        Route::get('/pages/{page:id}/edit', [AdminPageController::class, 'edit'])->name('admin.pages.edit');
        Route::put('/pages/{page:id}', [AdminPageController::class, 'update'])->name('admin.pages.update');
        Route::delete('/pages/{page:id}', [AdminPageController::class, 'destroy'])->name('admin.pages.destroy');

        // Homepage / marketing content blocks
        Route::get('/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonials.index');
        Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('admin.testimonials.create');
        Route::post('/testimonials', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
        Route::get('/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
        Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
        Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

        Route::get('/faqs', [AdminFaqController::class, 'index'])->name('admin.faqs.index');
        Route::post('/faqs', [AdminFaqController::class, 'store'])->name('admin.faqs.store');
        Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('admin.faqs.update');
        Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('admin.faqs.destroy');

        Route::get('/feature-cards', [FeatureCardController::class, 'index'])->name('admin.feature-cards.index');
        Route::get('/feature-cards/create', [FeatureCardController::class, 'create'])->name('admin.feature-cards.create');
        Route::post('/feature-cards', [FeatureCardController::class, 'store'])->name('admin.feature-cards.store');
        Route::get('/feature-cards/{featureCard}/edit', [FeatureCardController::class, 'edit'])->name('admin.feature-cards.edit');
        Route::put('/feature-cards/{featureCard}', [FeatureCardController::class, 'update'])->name('admin.feature-cards.update');
        Route::delete('/feature-cards/{featureCard}', [FeatureCardController::class, 'destroy'])->name('admin.feature-cards.destroy');

        Route::get('/tech-badges', [TechBadgeController::class, 'index'])->name('admin.tech-badges.index');
        Route::get('/tech-badges/create', [TechBadgeController::class, 'create'])->name('admin.tech-badges.create');
        Route::post('/tech-badges', [TechBadgeController::class, 'store'])->name('admin.tech-badges.store');
        Route::get('/tech-badges/{techBadge}/edit', [TechBadgeController::class, 'edit'])->name('admin.tech-badges.edit');
        Route::put('/tech-badges/{techBadge}', [TechBadgeController::class, 'update'])->name('admin.tech-badges.update');
        Route::delete('/tech-badges/{techBadge}', [TechBadgeController::class, 'destroy'])->name('admin.tech-badges.destroy');

        Route::get('/stats', [StatController::class, 'index'])->name('admin.stats.index');
        Route::get('/stats/create', [StatController::class, 'create'])->name('admin.stats.create');
        Route::post('/stats', [StatController::class, 'store'])->name('admin.stats.store');
        Route::get('/stats/{stat}/edit', [StatController::class, 'edit'])->name('admin.stats.edit');
        Route::put('/stats/{stat}', [StatController::class, 'update'])->name('admin.stats.update');
        Route::delete('/stats/{stat}', [StatController::class, 'destroy'])->name('admin.stats.destroy');

        // Two-factor auth: every account (admin or editor) manages its own.
        Route::get('/security', [TwoFactorController::class, 'show'])->name('admin.security');
        Route::post('/security/2fa/enable', [TwoFactorController::class, 'enable'])->name('admin.security.2fa.enable');
        Route::post('/security/2fa/confirm', [TwoFactorController::class, 'confirm'])->name('admin.security.2fa.confirm');
        Route::post('/security/2fa/disable', [TwoFactorController::class, 'disable'])->name('admin.security.2fa.disable');
        Route::post('/security/2fa/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('admin.security.2fa.recovery-codes');

        Route::get('/media', [MediaController::class, 'index'])->name('admin.media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('admin.media.store');
        Route::put('/media/{medium}', [MediaController::class, 'update'])->name('admin.media.update');
        Route::delete('/media/{medium}', [MediaController::class, 'destroy'])->name('admin.media.destroy');

        // Admin-only: site-wide settings, SEO, and team accounts can break the
        // whole site or grant further access, so editors don't get in here.
        Route::middleware('admin.only')->group(function () {
            Route::get('/settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
            Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');

            Route::get('/seo', [SeoController::class, 'index'])->name('admin.seo.index');
            Route::get('/seo/{key}', [SeoController::class, 'edit'])->name('admin.seo.edit');
            Route::put('/seo/{key}', [SeoController::class, 'update'])->name('admin.seo.update');

            Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

            Route::get('/redirects', [RedirectController::class, 'index'])->name('admin.redirects.index');
            Route::post('/redirects', [RedirectController::class, 'store'])->name('admin.redirects.store');
            Route::put('/redirects/{redirect}', [RedirectController::class, 'update'])->name('admin.redirects.update');
            Route::delete('/redirects/{redirect}', [RedirectController::class, 'destroy'])->name('admin.redirects.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin-managed content pages — registered last so a page slug can never
| shadow a real route.
|--------------------------------------------------------------------------
*/

Route::get('/{page}', [PageController::class, 'show'])
    ->where('page', '[a-zA-Z0-9-]+')
    ->name('page.show');
