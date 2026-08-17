<div class="contact-info">
  <p class="contact-info__label">Let&rsquo;s talk.</p>
  <a href="mailto:{{ $site->contact_email }}" class="contact-info__email">{{ $site->contact_email }}</a>

  <div class="contact-card">
    <div class="contact-card__row">
      <div class="contact-card__icon">
        <i class="fa-solid fa-location-dot"></i>
      </div>
      <div>
        <p class="contact-card__kicker">Studio</p>
        <p class="contact-card__title">{{ $site->contact_address }}</p>
        <p class="contact-card__text">{{ $site->contact_address_note }}</p>
      </div>
    </div>
  </div>

  @if ($site->has('contact_phone'))
    <div class="contact-card">
      <div class="contact-card__row">
        <div class="contact-card__icon">
          <i class="fa-solid fa-phone"></i>
        </div>
        <div>
          <p class="contact-card__kicker">Phone</p>
          <p class="contact-card__title">
            <a href="tel:{{ $site->contact_phone_link }}" class="contact-info__phone">{{ $site->contact_phone }}</a>
          </p>
          <p class="contact-card__text">Support {{ $site->contact_hours }}</p>
        </div>
      </div>
    </div>
  @endif

  <div class="contact-card">
    <p class="contact-card__kicker">What happens next</p>
    <ul class="contact-steps">
      <li>
        <span class="contact-steps__num">1</span>
        A founding partner reads your message — not a bot.
      </li>
      <li>
        <span class="contact-steps__num">2</span>
        We come back within 24h with three concrete approaches.
      </li>
      <li>
        <span class="contact-steps__num">3</span>
        If there&rsquo;s a fit, we scope a 2-week discovery.
      </li>
    </ul>
  </div>
</div>
