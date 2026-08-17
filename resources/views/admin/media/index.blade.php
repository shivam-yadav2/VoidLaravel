@extends('admin.layout')

@section('title', 'Media library')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Media library</h1>
      <p class="adm-muted">Standalone image uploads — copy a URL to use anywhere, or manage alt text.</p>
    </div>
  </div>

  <div class="adm-card">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="adm-field">
        <label>Upload images</label>
        <input type="file" name="files[]" accept="image/*" multiple required />
        <span class="adm-help">You can select multiple files at once. Max 4 MB each.</span>
      </div>
      <button type="submit" class="adm-btn adm-btn--primary">Upload</button>
    </form>
  </div>

  @if ($media->isEmpty())
    <div class="adm-empty"><p>No media uploaded yet.</p></div>
  @else
    <div class="adm-media-grid">
      @foreach ($media as $item)
        <div class="adm-media-item">
          <img src="{{ $item->url() }}" alt="{{ $item->alt_text }}" />
          <div class="adm-media-item__body">
            <input type="text" readonly value="{{ $item->url() }}" onclick="this.select()" class="adm-media-item__url" />
            <form method="POST" action="{{ route('admin.media.update', $item) }}" class="adm-media-item__alt">
              @csrf
              @method('PUT')
              <input type="text" name="alt_text" value="{{ $item->alt_text }}" placeholder="Alt text" />
              <button type="submit" class="adm-btn adm-btn--sm">Save</button>
            </form>
            <div class="adm-media-item__meta">
              <span class="adm-muted adm-small">{{ $item->humanSize() }}</span>
              <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                    onsubmit="return confirm('Delete this file?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="adm-btn adm-btn--sm adm-btn--danger">Delete</button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="adm-pagination">{{ $media->links() }}</div>
  @endif
@endsection
