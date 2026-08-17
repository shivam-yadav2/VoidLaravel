@extends('admin.layout')

@section('title', $stat->exists ? 'Edit stat' : 'New stat')

@section('content')
  <a href="{{ route('admin.stats.index') }}" class="adm-back">&larr; Back to stats</a>

  <h1>{{ $stat->exists ? 'Edit stat' : 'New stat' }}</h1>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $stat->exists ? route('admin.stats.update', $stat) : route('admin.stats.store') }}">
    @csrf
    @if ($stat->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-field">
            <label>Value</label>
            <input type="text" name="value" value="{{ old('value', $stat->value) }}" placeholder="e.g. 50+ or 1 day" required />
          </div>
          <div class="adm-field">
            <label>Label</label>
            <input type="text" name="label" value="{{ old('label', $stat->label) }}" placeholder="e.g. Products shipped" required />
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <div class="adm-field">
            <label>Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $stat->sort_order) }}" />
          </div>
          <label class="adm-check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $stat->is_active)) />
            <span>Active</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $stat->exists ? 'Save' : 'Add stat' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Icon</h2>
          @if ($stat->iconUrl())
            <div class="adm-image-preview">
              <img src="{{ $stat->iconUrl() }}" alt="" />
              <label class="adm-check"><input type="checkbox" name="remove_icon" value="1" /><span>Remove icon</span></label>
            </div>
          @endif
          <input type="file" name="icon" accept="image/*" />
        </div>
      </aside>
    </div>
  </form>

  @if ($stat->exists)
    <form method="POST" action="{{ route('admin.stats.destroy', $stat) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this stat?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete stat</button>
    </form>
  @endif
@endsection
