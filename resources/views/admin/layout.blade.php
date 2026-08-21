<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="{{ asset('assets/img/void_logo.png') }}" />
    <title>@yield('title', 'Admin') · {{ $site->site_name }}</title>
    {{-- Cache-busted with the file's own mtime — see the same comment in
         layouts/app.blade.php for why. --}}
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ @filemtime(public_path('assets/css/admin.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-forms.css') }}?v={{ @filemtime(public_path('assets/css/admin-forms.css')) }}" />
  </head>
  <body>
    @auth
      @php
        $navGroups = [
          'Overview' => [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard',
              'icon' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>'],
          ],
          'Content' => [
            ['route' => 'admin.leads.index', 'match' => 'admin.leads.*', 'label' => 'Leads',
              'icon' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/>'],
            ['route' => 'admin.posts.index', 'match' => 'admin.posts.*', 'label' => 'Blog',
              'icon' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M9 13h6"/><path d="M9 17h4"/>'],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Categories',
              'icon' => '<path d="M20.59 13.41 12 22l-9-9 8.59-8.59A2 2 0 0 1 13 4h6a1 1 0 0 1 1 1v6a2 2 0 0 1-.41 1.41Z"/><circle cx="16.5" cy="7.5" r="1.5"/>'],
            ['route' => 'admin.pages.index', 'match' => 'admin.pages.*', 'label' => 'Pages',
              'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/>'],
          ],
          'Homepage content' => [
            ['route' => 'admin.testimonials.index', 'match' => 'admin.testimonials.*', 'label' => 'Testimonials',
              'icon' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>'],
            ['route' => 'admin.faqs.index', 'match' => 'admin.faqs.*', 'label' => 'FAQs',
              'icon' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 3.5"/><path d="M12 17h.01"/>'],
            ['route' => 'admin.feature-cards.index', 'match' => 'admin.feature-cards.*', 'label' => 'Feature cards',
              'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
            ['route' => 'admin.tech-badges.index', 'match' => 'admin.tech-badges.*', 'label' => 'Tech badges',
              'icon' => '<path d="m9 18-6-6 6-6"/><path d="m15 6 6 6-6 6"/>'],
            ['route' => 'admin.stats.index', 'match' => 'admin.stats.*', 'label' => 'Stats',
              'icon' => '<path d="M3 3v18h18"/><path d="M7 15v3"/><path d="M12 10v8"/><path d="M17 6v12"/>'],
          ],
          'Tools' => [
            ['route' => 'admin.media.index', 'match' => 'admin.media.*', 'label' => 'Media library',
              'icon' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>'],
          ],
          'Account' => [
            ['route' => 'admin.security', 'match' => 'admin.security*', 'label' => 'Security',
              'icon' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
          ],
        ];

        // Admin-only sections — hidden from editors entirely rather than
        // shown-then-403'd, since a visible link to a page you can't open
        // just reads as a bug.
        if (auth()->user()?->isAdmin()) {
          $navGroups['Site'] = [
            ['route' => 'admin.seo.index', 'match' => 'admin.seo.*', 'label' => 'SEO',
              'icon' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>'],
            ['route' => 'admin.settings.edit', 'match' => 'admin.settings.*', 'label' => 'Settings',
              'icon' => '<path d="M4 6h10"/><circle cx="17" cy="6" r="2"/><path d="M4 12h4"/><circle cx="11" cy="12" r="2"/><path d="M15 12h5"/><path d="M4 18h10"/><circle cx="17" cy="18" r="2"/>'],
            ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Team',
              'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['route' => 'admin.redirects.index', 'match' => 'admin.redirects.*', 'label' => 'Redirects',
              'icon' => '<path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>'],
          ];
        }
      @endphp

      <div class="adm-mobile-bar">
        <button type="button" class="adm-mobile-toggle" data-sidebar-toggle aria-label="Toggle menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="adm-brand">
          <img src="{{ asset('assets/img/void_logo.png') }}" alt="{{ $site->site_name }}" />
          <span>Admin</span>
        </a>
      </div>

      <div class="adm-sidebar-overlay" data-sidebar-overlay data-sidebar-toggle></div>

      <aside class="adm-sidebar" data-sidebar>
        <a href="{{ route('admin.dashboard') }}" class="adm-brand">
          <img src="{{ asset('assets/img/void_logo.png') }}" alt="{{ $site->site_name }}" />
          <span>Admin</span>
        </a>

        <nav class="adm-nav">
          @foreach ($navGroups as $groupLabel => $items)
            <span class="adm-nav__heading">{{ $groupLabel }}</span>
            @foreach ($items as $item)
              <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['match']) ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                {{ $item['label'] }}
              </a>
            @endforeach
          @endforeach
        </nav>

        <div class="adm-sidebar__footer">
          <a href="{{ url('/') }}" target="_blank" rel="noopener" class="adm-sidebar__link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
            View site
          </a>

          <div class="adm-sidebar__user">
            <span class="adm-user">{{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
              @csrf
              <button type="submit" class="adm-sidebar__link adm-sidebar__link--btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Log out
              </button>
            </form>
          </div>
        </div>
      </aside>
    @endauth

    <main class="adm-main {{ auth()->check() ? '' : 'adm-main--bare' }}">
      @if (session('admin_success'))
        <div class="adm-alert adm-alert--success">{{ session('admin_success') }}</div>
      @endif
      @if (session('admin_error'))
        <div class="adm-alert adm-alert--error">{{ session('admin_error') }}</div>
      @endif
      @if ($errors->any() && ! request()->routeIs('login'))
        <div class="adm-alert adm-alert--error">
          <strong>Please fix the following:</strong>
          <ul class="adm-error-list">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('content')
    </main>

    @auth
      <script>
        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (el) {
          el.addEventListener('click', function () {
            document.querySelector('[data-sidebar]').classList.toggle('is-open');
            document.querySelector('[data-sidebar-overlay]').classList.toggle('is-open');
            document.body.classList.toggle('adm-no-scroll');
          });
        });
      </script>
    @endauth

    @stack('scripts')
  </body>
</html>
