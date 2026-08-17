@extends('admin.layout')

@section('title', $page->exists ? 'Edit page' : 'New page')

@section('content')
  <a href="{{ route('admin.pages.index') }}" class="adm-back">&larr; Back to pages</a>

  <div class="adm-page-head">
    <div>
      <h1>{{ $page->exists ? 'Edit page' : 'New page' }}</h1>
      @if ($page->exists)
        <p class="adm-muted">{{ route('page.show', $page) }}</p>
      @endif
    </div>
    @if ($page->exists)
      <a href="{{ route('page.show', $page) }}" target="_blank" rel="noopener" class="adm-btn">View page &nearr;</a>
    @endif
  </div>

  <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
    @csrf
    @if ($page->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-field">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title', $page->title) }}" required />
          </div>

          <div class="adm-field">
            <label for="slug">URL slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $page->slug) }}"
                   placeholder="auto-generated from the title" @disabled($page->is_locked) />
            <span class="adm-help">
              @if ($page->is_locked)
                This page is linked from the site footer, so its URL is fixed.
              @else
                Lowercase letters, numbers and hyphens only.
              @endif
            </span>
          </div>

          <div class="adm-field">
            <label for="content">Content</label>
            @include('admin.partials.editor', ['name' => 'content', 'value' => old('content', $page->content)])
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <h2 class="adm-label">Publish</h2>
          <label class="adm-check">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published)) />
            <span>Published</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $page->exists ? 'Save page' : 'Create page' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">SEO</h2>
          <div class="adm-field">
            <label for="meta_title">Meta title</label>
            <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $page->meta_title) }}"
                   placeholder="Defaults to the title" />
          </div>
          <div class="adm-field">
            <label for="meta_description">Meta description</label>
            <textarea id="meta_description" name="meta_description" rows="3"
                      placeholder="Defaults to the first 155 characters">{{ old('meta_description', $page->meta_description) }}</textarea>
          </div>
        </div>
      </aside>
    </div>
  </form>

  @if ($page->exists && ! $page->is_locked)
    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this page permanently?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete page</button>
    </form>
  @endif
@endsection
