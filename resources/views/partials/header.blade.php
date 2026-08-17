<header id="header-sticky" class="header-1">
  <div class="container">
    <div class="mega-menu-wrapper">
      <div class="header-main">
        <div class="header-left">
          <div class="logo">
            <a href="{{ url('/') }}" class="header-logo">
              <img
                src="{{ asset('assets/img/void_logo.png') }}"
                alt="logo-img"
                style="height: 70px; width: auto; object-fit: contain;"
              />
            </a>
          </div>
        </div>
        <div class="mean__menu-wrapper">
          <div class="main-menu">
            <nav id="mobile-menu" class="menu-box-list">
              <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('services') }}">Services</a></li>
                <li><a href="{{ route('about') }}">About</a></li>
                <li><a href="{{ route('blog.index') }}">Blog</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
              </ul>
            </nav>
          </div>
        </div>
        <div class="header-right d-xl-none">
          <button type="button" class="sidebar__toggle header__hamburger" aria-label="Open menu">
            <i class="fas fa-bars"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>
