@extends('admin.layout')

@section('title', $card->exists ? 'Edit card' : 'New card')

@section('content')
  <a href="{{ route('admin.feature-cards.index', ['group' => $card->group]) }}" class="adm-back">&larr; Back to feature cards</a>

  <h1>{{ $card->exists ? 'Edit card' : 'New card' }}</h1>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $card->exists ? route('admin.feature-cards.update', $card) : route('admin.feature-cards.store') }}">
    @csrf
    @if ($card->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-field">
            <label>Shows on</label>
            <select name="group">
              <option value="services" @selected(old('group', $card->group) === 'services')>Services page ("What We Build")</option>
              <option value="about_values" @selected(old('group', $card->group) === 'about_values')>About page ("How We Work")</option>
            </select>
          </div>
          <div class="adm-field">
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $card->title) }}" required />
          </div>
          <div class="adm-field">
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $card->description) }}</textarea>
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <div class="adm-field">
            <label>Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $card->sort_order) }}" />
          </div>
          <label class="adm-check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $card->is_active)) />
            <span>Active</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $card->exists ? 'Save' : 'Add card' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Icon</h2>
          @if ($card->iconUrl())
            <div class="adm-image-preview">
              <img src="{{ $card->iconUrl() }}" alt="" />
              <label class="adm-check"><input type="checkbox" name="remove_icon" value="1" /><span>Remove icon</span></label>
            </div>
          @endif
          <input type="file" name="icon" accept="image/*" />
        </div>
      </aside>
    </div>
  </form>

  @if ($card->exists)
    <form method="POST" action="{{ route('admin.feature-cards.destroy', $card) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this card?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete card</button>
    </form>
  @endif
@endsection
