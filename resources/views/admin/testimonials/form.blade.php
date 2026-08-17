@extends('admin.layout')

@section('title', $testimonial->exists ? 'Edit testimonial' : 'New testimonial')

@section('content')
  <a href="{{ route('admin.testimonials.index') }}" class="adm-back">&larr; Back to testimonials</a>

  <h1>{{ $testimonial->exists ? 'Edit testimonial' : 'New testimonial' }}</h1>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
    @csrf
    @if ($testimonial->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-grid-2">
            <div class="adm-field">
              <label>Client name</label>
              <input type="text" name="client_name" value="{{ old('client_name', $testimonial->client_name) }}" required />
            </div>
            <div class="adm-field">
              <label>Role</label>
              <input type="text" name="client_role" value="{{ old('client_role', $testimonial->client_role) }}" placeholder="e.g. Founder" />
            </div>
            <div class="adm-field adm-field--full">
              <label>Company</label>
              <input type="text" name="client_company" value="{{ old('client_company', $testimonial->client_company) }}" />
            </div>
            <div class="adm-field adm-field--full">
              <label>Quote</label>
              <textarea name="quote" rows="4" required>{{ old('quote', $testimonial->quote) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <div class="adm-field">
            <label>Rating</label>
            <select name="rating">
              @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((int) old('rating', $testimonial->rating) === $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
              @endfor
            </select>
          </div>
          <div class="adm-field">
            <label>Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial->sort_order) }}" />
          </div>
          <label class="adm-check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active)) />
            <span>Active</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $testimonial->exists ? 'Save' : 'Add testimonial' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Photo</h2>
          @if ($testimonial->photoUrl())
            <div class="adm-image-preview">
              <img src="{{ $testimonial->photoUrl() }}" alt="" />
              <label class="adm-check"><input type="checkbox" name="remove_photo" value="1" /><span>Remove photo</span></label>
            </div>
          @endif
          <input type="file" name="photo" accept="image/*" />
          <span class="adm-help">Optional. Square image works best.</span>
        </div>
      </aside>
    </div>
  </form>

  @if ($testimonial->exists)
    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this testimonial?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete testimonial</button>
    </form>
  @endif
@endsection
