<form class="contact-form-card" id="contact-form" method="POST" action="{{ route('contact.store') }}">
  @csrf

  @if (session('contact_success'))
    <div class="contact-alert contact-alert--success">
      {{ session('contact_success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="contact-alert contact-alert--error">
      Please fix the highlighted fields and try again.
    </div>
  @endif

  <div class="contact-form__grid">
    <div class="contact-field">
      <label for="cf-name">Your name</label>
      <input id="cf-name" name="name" type="text" placeholder="Ada Lovelace" value="{{ old('name') }}" required />
      @error('name')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>
    <div class="contact-field">
      <label for="cf-email">Email</label>
      <input id="cf-email" name="email" type="email" placeholder="ada@company.com" value="{{ old('email') }}" required />
      @error('email')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>

    <div class="contact-field contact-field--full">
      <label for="cf-company">Company</label>
      <input id="cf-company" name="company" type="text" placeholder="Void Industries" value="{{ old('company') }}" />
      @error('company')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>

    <div class="contact-field contact-field--full">
      <label>Budget</label>
      <div class="contact-budget" id="contact-budget">
        <input type="hidden" name="budget" id="cf-budget" value="{{ old('budget') }}" />
        @foreach (\App\Models\Lead::BUDGETS as $option)
          <button
            type="button"
            class="contact-pill{{ old('budget') === $option ? ' is-active' : '' }}"
            data-budget="{{ $option }}"
          >{{ $option }}</button>
        @endforeach
      </div>
    </div>

    <div class="contact-field contact-field--full">
      <label for="cf-brief">What are you building?</label>
      <textarea id="cf-brief" name="brief" placeholder="A bit about the product, the team and the deadline..." required>{{ old('brief') }}</textarea>
      @error('brief')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>
  </div>

  {{-- Honeypot: hidden from humans, irresistible to bots. --}}
  <div class="contact-hp" aria-hidden="true">
    <label for="cf-website">Website</label>
    <input id="cf-website" name="website" type="text" tabindex="-1" autocomplete="off" />
  </div>

  @if ($site->recaptcha_site_key)
    <div class="contact-recaptcha">
      <div class="g-recaptcha" data-sitekey="{{ $site->recaptcha_site_key }}"></div>
      @error('g-recaptcha-response')<span class="contact-field__error">{{ $message }}</span>@enderror
    </div>
  @endif

  <button type="submit" class="contact-submit">Send message</button>

  <p class="contact-form__note">
    We&rsquo;ll never share your details. By submitting you agree to our
    <a href="#privacy">privacy policy</a>.
  </p>
</form>

@if ($site->recaptcha_site_key)
  @push('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @endpush
@endif

@push('scripts')
  <script>
    (function () {
      var budgetGroup = document.getElementById("contact-budget");
      var budgetInput = document.getElementById("cf-budget");
      if (!budgetGroup) return;

      budgetGroup.addEventListener("click", function (e) {
        var btn = e.target.closest(".contact-pill");
        if (!btn) return;
        var wasActive = btn.classList.contains("is-active");
        budgetGroup.querySelectorAll(".contact-pill").forEach(function (b) {
          b.classList.remove("is-active");
        });
        if (wasActive) {
          budgetInput.value = "";
        } else {
          btn.classList.add("is-active");
          budgetInput.value = btn.getAttribute("data-budget");
        }
      });
    })();
  </script>
@endpush
