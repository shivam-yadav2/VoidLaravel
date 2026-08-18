<section class="support-cta-section section-padding fix" id="Support">
  <div class="container">
    <div class="support-cta-wrapper">
      <div class="support-content">
        <div class="support-info">
          <div class="line-shape">
            <img src="{{ asset('assets/img/feature/line-shape.png') }}" alt="img" />
          </div>

          <div class="support-text">
            <p class="text">{{ $site->footer_support_text }}</p>
            <div class="support-social">
              <span>Connect Us</span>
              <div class="social-line">
                <img src="{{ asset('assets/img/feature/small-line.png') }}" alt="img" />
              </div>
              @php
                // Fixed set from SiteConfig::socialLinks() — kept local rather
                // than changing that method's return shape, since partials/seo.blade.php
                // reads it as icon=>url directly for the JSON-LD `sameAs` field.
                $socialLabels = [
                  'fa-brands fa-instagram' => 'Instagram',
                  'fa-brands fa-linkedin-in' => 'LinkedIn',
                  'fa-brands fa-whatsapp' => 'WhatsApp',
                  'fa-brands fa-facebook-f' => 'Facebook',
                  'fa-brands fa-x-twitter' => 'X (Twitter)',
                ];
              @endphp
              <div class="social-icon">
                @foreach ($site->socialLinks() as $icon => $url)
                  <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLabels[$icon] ?? 'Social link' }}">
                    <i class="{{ $icon }}"></i>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="support-cta-copyright">
      <p class="text">&copy; {{ date('Y') }} {{ $site->site_name }}. All rights reserved.</p>

      <nav class="footer-links">
        <a href="{{ route('blog.index') }}">Blog</a>
        @foreach ($footerPages as $footerPage)
          <a href="{{ route('page.show', $footerPage) }}">{{ $footerPage->title }}</a>
        @endforeach
        <a href="mailto:{{ $site->contact_email }}">{{ $site->contact_email }}</a>
      </nav>

      <p class="text">{{ $site->footer_tagline }}</p>
    </div>
  </div>
</section>
