@extends('admin.layout')

@section('title', $badge->exists ? 'Edit badge' : 'New badge')

@section('content')
  <a href="{{ route('admin.tech-badges.index') }}" class="adm-back">&larr; Back to tech badges</a>

  <h1>{{ $badge->exists ? 'Edit badge' : 'New badge' }}</h1>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $badge->exists ? route('admin.tech-badges.update', $badge) : route('admin.tech-badges.store') }}">
    @csrf
    @if ($badge->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-field">
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $badge->title) }}" placeholder="e.g. React & TypeScript" required />
          </div>
          <div class="adm-field">
            <label>Subtitle</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $badge->subtitle) }}" placeholder="e.g. Modern Frontend" />
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <div class="adm-field">
            <label>Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $badge->sort_order) }}" />
          </div>
          <label class="adm-check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $badge->is_active)) />
            <span>Active</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $badge->exists ? 'Save' : 'Add badge' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Icon</h2>
          @if ($badge->iconUrl())
            <div class="adm-image-preview">
              <img src="{{ $badge->iconUrl() }}" alt="" />
              <label class="adm-check"><input type="checkbox" name="remove_icon" value="1" /><span>Remove icon</span></label>
            </div>
          @endif
          <input type="file" name="icon" accept="image/*" />
        </div>
      </aside>
    </div>
  </form>

  @if ($badge->exists)
    <form method="POST" action="{{ route('admin.tech-badges.destroy', $badge) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this badge?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete badge</button>
    </form>
  @endif
@endsection
