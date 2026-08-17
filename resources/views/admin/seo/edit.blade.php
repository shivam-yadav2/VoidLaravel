@extends('admin.layout')

@section('title', 'SEO · '.$page['label'])

@section('content')
  <a href="{{ route('admin.seo.index') }}" class="adm-back">&larr; Back to meta tags</a>

  <div class="adm-page-head">
    <div>
      <h1>{{ $page['label'] }}</h1>
      <p class="adm-muted">{{ route($page['route']) }}</p>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.seo.update', $key) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="adm-card">
      <div class="adm-field">
        <label for="meta_title">Meta title</label>
        <input id="meta_title" name="meta_title" type="text" maxlength="255"
               value="{{ old('meta_title', $record->meta_title) }}"
               placeholder="Leave blank to use the built-in title" />
        <span class="adm-help">Aim for 50–60 characters. “ | {{ $site->site_name }}” is appended automatically.</span>
      </div>

      <div class="adm-field">
        <label for="meta_description">Meta description</label>
        <textarea id="meta_description" name="meta_description" rows="3" maxlength="500"
                  placeholder="Leave blank to use the built-in description">{{ old('meta_description', $record->meta_description) }}</textarea>
        <span class="adm-help">Aim for 150–160 characters.</span>
      </div>

      <div class="adm-field">
        <label for="og_image">Social share image</label>
        @if ($record->og_image)
          <div class="adm-image-preview">
            <img src="{{ Storage::url($record->og_image) }}" alt="Current share image" />
            <label class="adm-check">
              <input type="checkbox" name="remove_og_image" value="1" />
              <span>Remove this image</span>
            </label>
          </div>
        @endif
        <input id="og_image" name="og_image" type="file" accept="image/*" />
        <span class="adm-help">Recommended 1200×630px. Max 2 MB. Falls back to the site logo.</span>
      </div>

      <label class="adm-check">
        <input type="checkbox" name="noindex" value="1" @checked(old('noindex', $record->noindex)) />
        <span>Hide from search engines (noindex)</span>
      </label>
    </div>

    <button type="submit" class="adm-btn adm-btn--primary">Save meta tags</button>
  </form>
@endsection
