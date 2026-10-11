@php
  // Only render the widget when the server will actually verify it — both
  // keys set. A site key alone used to show a checkbox nobody checked.
  $recaptchaOn = $site->recaptcha_site_key && $site->recaptcha_secret_key;
@endphp
<form class="contact-form-card" id="contact-form" method="POST" action="{{ route('contact.store') }}">
  @csrf

  @if (session('contact_success'))
    <div class="contact-alert contact-alert--success" role="status">
      {{ session('contact_success') }}
    </div>
  @endif

  @if (session('contact_error'))
    <div class="contact-alert contact-alert--error" role="alert">
      {{ session('contact_error') }}
    </div>
  @elseif ($errors->any())
    <div class="contact-alert contact-alert--error" role="alert">
      Please fix the highlighted fields and try again.
    </div>
  @endif

  <div class="contact-form__grid">
    <div class="contact-field">
      <label for="cf-name">Your name</label>
      <input id="cf-name" name="name" type="text" autocomplete="name" maxlength="120" placeholder="Your full name" value="{{ old('name') }}" required @error('name') aria-invalid="true" aria-describedby="cf-name-error" @enderror />
      @error('name')<span class="contact-field__error" id="cf-name-error">{{ $message }}</span>@enderror
    </div>
    <div class="contact-field">
      <label for="cf-email">Email</label>
      <input id="cf-email" name="email" type="email" autocomplete="email" maxlength="190" placeholder="you@company.com" value="{{ old('email') }}" required @error('email') aria-invalid="true" aria-describedby="cf-email-error" @enderror />
      @error('email')<span class="contact-field__error" id="cf-email-error">{{ $message }}</span>@enderror
    </div>

    <div class="contact-field contact-field--full">
      <label for="cf-company">Company <span class="contact-field__optional">(optional)</span></label>
      <input id="cf-company" name="company" type="text" autocomplete="organization" maxlength="150" placeholder="Company name" value="{{ old('company') }}" />
      @error('company')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>

    <div class="contact-field contact-field--full">
      <span class="contact-field__label" id="cf-budget-label">Budget <span class="contact-field__optional">(optional)</span></span>
      <div class="contact-budget" id="contact-budget" role="group" aria-labelledby="cf-budget-label">
        <input type="hidden" name="budget" id="cf-budget" value="{{ old('budget') }}" />
        @foreach (\App\Models\Lead::BUDGETS as $option)
          <button
            type="button"
            class="contact-pill{{ old('budget') === $option ? ' is-active' : '' }}"
            data-budget="{{ $option }}"
            aria-pressed="{{ old('budget') === $option ? 'true' : 'false' }}"
          >{{ $option }}</button>
        @endforeach
      </div>
      @error('budget')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>

    <div class="contact-field contact-field--full">
      <label for="cf-brief">What are you building?</label>
      <textarea id="cf-brief" name="brief" maxlength="5000" placeholder="A bit about the product, the team and the deadline..." required @error('brief') aria-invalid="true" aria-describedby="cf-brief-error" @enderror>{{ old('brief') }}</textarea>
      @error('brief')<span class="contact-field__error" id="cf-brief-error">{{ $message }}</span>@enderror
    </div>
  </div>

  {{-- Honeypot: hidden from humans, irresistible to bots. --}}
  <div class="contact-hp" aria-hidden="true">
    <label for="cf-website">Website</label>
    <input id="cf-website" name="website" type="text" tabindex="-1" autocomplete="off" />
  </div>

  @if ($recaptchaOn)
    <div class="contact-recaptcha">
      <div class="g-recaptcha" data-sitekey="{{ $site->recaptcha_site_key }}" data-theme="dark"></div>
      @error('g-recaptcha-response')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>
  @endif

  <button type="submit" class="contact-submit">Send message</button>

  <p class="contact-form__note">
    We&rsquo;ll never share your details. By submitting you agree to our
    <a href="{{ route('page.show', 'privacy-policy') }}">privacy policy</a>.
  </p>
</form>

@if ($recaptchaOn)
  @push('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @endpush
@endif

@push('scripts')
  <script>
    (function () {
      var form = document.getElementById("contact-form");
      var budgetGroup = document.getElementById("contact-budget");
      var budgetInput = document.getElementById("cf-budget");
      if (!form || !budgetGroup) return;

      budgetGroup.addEventListener("click", function (e) {
        var btn = e.target.closest(".contact-pill");
        if (!btn) return;
        var wasActive = btn.classList.contains("is-active");
        budgetGroup.querySelectorAll(".contact-pill").forEach(function (b) {
          b.classList.remove("is-active");
          b.setAttribute("aria-pressed", "false");
        });
        if (wasActive) {
          budgetInput.value = "";
        } else {
          btn.classList.add("is-active");
          btn.setAttribute("aria-pressed", "true");
          budgetInput.value = btn.getAttribute("data-budget");
        }
      });

      // Stop double submissions (and the duplicate leads they create).
      form.addEventListener("submit", function () {
        var submit = form.querySelector(".contact-submit");
        if (!submit) return;
        submit.disabled = true;
        submit.textContent = "Sending…";
      });

      // Coming back via the browser's Back button restores the page from
      // cache with the button still disabled — re-enable it.
      window.addEventListener("pageshow", function (e) {
        if (!e.persisted) return;
        var submit = form.querySelector(".contact-submit");
        if (submit) { submit.disabled = false; submit.textContent = "Send message"; }
      });
    })();
  </script>
@endpush
