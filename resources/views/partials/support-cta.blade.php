<footer class="support-cta-section section-padding fix" id="Support">
  <div class="container">
    <div class="support-cta-wrapper">
      <div class="support-content">
        <div class="support-info">
          <div class="line-shape">
            <img src="{{ asset('assets/img/feature/line-shape.png') }}" alt="" loading="lazy" decoding="async" />
          </div>

          <div class="support-text">
            <p class="text">{{ $site->footer_support_text }}</p>
            <div class="support-social">
              <span>Connect with us</span>
              <div class="social-line">
                <img src="{{ asset('assets/img/feature/small-line.png') }}" alt="" loading="lazy" decoding="async" />
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
                    <i class="{{ $icon }}" aria-hidden="true"></i>
                  </a>
                @endforeach
              </div>
            </div>

            {{-- Name / address / phone in the footer of every page: what local
                 search (and visitors) look for first. --}}
            <address class="footer-nap">
              <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $site->contact_address }}</span>
              @if ($site->has('contact_phone'))
                <a href="tel:{{ $site->contact_phone_link }}"><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $site->contact_phone }}</a>
              @endif
              <a href="mailto:{{ $site->contact_email }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>{{ $site->contact_email }}</a>
            </address>
          </div>
        </div>
      </div>
    </div>

    <div class="support-cta-copyright">
      <p class="text">&copy; {{ date('Y') }} {{ $site->site_name }}. All rights reserved.</p>

      <nav class="footer-links" aria-label="Footer">
        <a href="{{ route('services') }}">Services</a>
        <a href="{{ route('products.index') }}">Products</a>
        <a href="{{ route('about') }}">About</a>
        <a href="{{ route('blog.index') }}">Blog</a>
        <a href="{{ route('contact') }}">Contact</a>
        @foreach ($footerPages as $footerPage)
          <a href="{{ route('page.show', $footerPage) }}">{{ $footerPage->title }}</a>
        @endforeach
      </nav>

      <p class="text">{{ $site->footer_tagline }}</p>
    </div>
  </div>
</footer>
