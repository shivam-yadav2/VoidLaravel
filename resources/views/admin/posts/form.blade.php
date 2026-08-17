@extends('admin.layout')

@section('title', $post->exists ? 'Edit post' : 'New post')

@section('content')
  <a href="{{ route('admin.posts.index') }}" class="adm-back">&larr; Back to posts</a>

  <div class="adm-page-head">
    <div>
      <h1>{{ $post->exists ? 'Edit post' : 'New post' }}</h1>
      @if ($post->exists)
        <p class="adm-muted">/blog/{{ $post->slug }} · {{ $post->views }} views</p>
      @endif
    </div>
    @if ($post->exists && $post->isLive())
      <a href="{{ route('blog.show', $post) }}" target="_blank" rel="noopener" class="adm-btn">View post &nearr;</a>
    @endif
  </div>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
    @csrf
    @if ($post->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-field">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title', $post->title) }}" required />
          </div>

          <div class="adm-field">
            <label for="slug">URL slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $post->slug) }}"
                   placeholder="auto-generated from the title" />
            <span class="adm-help">Lowercase letters, numbers and hyphens only.</span>
          </div>

          <div class="adm-field">
            <label for="excerpt">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                      placeholder="Short summary shown on the blog listing and in search results">{{ old('excerpt', $post->excerpt) }}</textarea>
          </div>

          <div class="adm-field">
            <label for="content">Content</label>
            @include('admin.partials.editor', ['name' => 'content', 'value' => old('content', $post->content)])
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <h2 class="adm-label">Publish</h2>

          <div class="adm-field">
            <label for="status">Status</label>
            <select id="status" name="status">
              @foreach (\App\Models\Post::STATUSES as $s)
                <option value="{{ $s }}" @selected(old('status', $post->status) === $s)>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>

          <div class="adm-field">
            <label for="published_at">Publish date</label>
            <input id="published_at" name="published_at" type="datetime-local"
                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" />
            <span class="adm-help">Leave blank to publish immediately. A future date schedules the post.</span>
          </div>

          <div class="adm-field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
              <option value="">— Uncategorised —</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $post->category_id) === $category->id)>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
            @if ($categories->isEmpty())
              <span class="adm-help"><a href="{{ route('admin.categories.index') }}" class="adm-link">Add a category</a> first.</span>
            @endif
          </div>

          <div class="adm-field">
            <label for="read_minutes">Reading time (minutes)</label>
            <input id="read_minutes" name="read_minutes" type="number" min="1" max="120"
                   value="{{ old('read_minutes', $post->read_minutes) }}" placeholder="auto" />
          </div>

          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $post->exists ? 'Save post' : 'Create post' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Featured image</h2>
          @if ($post->imageUrl())
            <div class="adm-image-preview">
              <img src="{{ $post->imageUrl() }}" alt="Current featured image" />
              <label class="adm-check">
                <input type="checkbox" name="remove_image" value="1" />
                <span>Remove this image</span>
              </label>
            </div>
          @endif
          <div class="adm-field">
            <input id="featured_image" name="featured_image" type="file" accept="image/*" />
            <span class="adm-help">Recommended 1200×630px. Max 4 MB.</span>
          </div>
          <div class="adm-field">
            <label for="featured_image_alt">Image alt text</label>
            <input id="featured_image_alt" name="featured_image_alt" type="text"
                   value="{{ old('featured_image_alt', $post->featured_image_alt) }}"
                   placeholder="Describes the image for screen readers" />
          </div>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">SEO</h2>
          <div class="adm-field">
            <label for="meta_title">Meta title</label>
            <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $post->meta_title) }}"
                   placeholder="Defaults to the post title" />
          </div>
          <div class="adm-field">
            <label for="meta_description">Meta description</label>
            <textarea id="meta_description" name="meta_description" rows="3"
                      placeholder="Defaults to the excerpt">{{ old('meta_description', $post->meta_description) }}</textarea>
          </div>
        </div>
      </aside>
    </div>
  </form>

  @if ($post->exists)
    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this post permanently?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete post</button>
    </form>
  @endif
@endsection
