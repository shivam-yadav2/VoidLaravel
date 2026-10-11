@extends('admin.layout')

@php
  use App\Http\Controllers\Admin\ProductController as PC;
  use App\Support\UploadLimit;
  $imageLimit = UploadLimit::human(UploadLimit::fileKb(PC::IMAGE_KB));
  $videoLimit = UploadLimit::human(UploadLimit::fileKb(PC::VIDEO_KB));
@endphp

@section('title', $product->exists ? 'Edit product' : 'New product')

@section('content')
  <a href="{{ route('admin.products.index') }}" class="adm-back">&larr; Back to products</a>

  <div class="adm-page-head">
    <div>
      <h1>{{ $product->exists ? 'Edit product' : 'New product' }}</h1>
      @if ($product->exists)
        <p class="adm-muted">/products/{{ $product->slug }}</p>
      @endif
    </div>
    @if ($product->exists && $product->is_published)
      <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener" class="adm-btn">View product &nearr;</a>
    @endif
  </div>

  <form method="POST" enctype="multipart/form-data"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="adm-detail">
      <div class="adm-detail__main">
        <div class="adm-card">
          <div class="adm-field">
            <label for="name">Product name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required />
          </div>
          <div class="adm-field">
            <label for="tagline">Tagline</label>
            <input id="tagline" name="tagline" type="text" maxlength="200" value="{{ old('tagline', $product->tagline) }}"
                   placeholder="One line — what it is, e.g. “Attendance & payroll for small factories”" />
          </div>
          <div class="adm-field">
            <label for="slug">URL slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $product->slug) }}" placeholder="auto-generated from the name" />
          </div>
          <div class="adm-field">
            <label for="summary">Card summary</label>
            <textarea id="summary" name="summary" rows="3" maxlength="500" required
                      placeholder="Two sentences shown on the product card.">{{ old('summary', $product->summary) }}</textarea>
          </div>
          <div class="adm-field">
            <label for="description">Full description</label>
            @include('admin.partials.editor', ['name' => 'description', 'value' => old('description', $product->description)])
            <span class="adm-help">Shown in the product's detail popup and page.</span>
          </div>
          <div class="adm-field">
            <label for="features">Key features</label>
            <textarea id="features" name="features" rows="6" placeholder="One feature per line">{{ old('features', $product->features) }}</textarea>
            <span class="adm-help">One per line — shown as a checklist.</span>
          </div>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Gallery</h2>
          <p class="adm-help" style="margin-top:0">Screenshots shown in the detail popup (up to {{ PC::MAX_GALLERY }}). Max {{ $imageLimit }} each.</p>

          @if ($product->exists && $product->images->isNotEmpty())
            <div class="adm-gallery">
              @foreach ($product->images as $image)
                <div class="adm-gallery__item">
                  <img src="{{ $image->url() }}" alt="" />
                  <input type="text" name="gallery_alt[{{ $image->id }}]" value="{{ old("gallery_alt.{$image->id}", $image->alt) }}" placeholder="Alt text" aria-label="Alt text for image {{ $loop->iteration }}" />
                  <div class="adm-gallery__row">
                    <label>Order <input type="number" min="0" name="gallery_order[{{ $image->id }}]" value="{{ old("gallery_order.{$image->id}", $image->sort_order) }}" /></label>
                    <label class="adm-check"><input type="checkbox" name="remove_images[]" value="{{ $image->id }}" /> <span>Remove</span></label>
                  </div>
                </div>
              @endforeach
            </div>
          @endif

          <div class="adm-field">
            <label for="gallery">Add images</label>
            <input id="gallery" name="gallery[]" type="file" accept="image/*" multiple />
          </div>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Video</h2>
          <div class="adm-field">
            <label for="video_url">YouTube or Vimeo link <span class="adm-muted">(recommended)</span></label>
            <input id="video_url" name="video_url" type="url" value="{{ old('video_url', $product->video_url) }}" placeholder="https://youtu.be/… or https://vimeo.com/…" />
          </div>
          @if ($product->video_path)
            <div class="adm-image-preview">
              <video src="{{ Storage::url($product->video_path) }}" controls preload="metadata" style="width:100%; border-radius:10px"></video>
              <label class="adm-check"><input type="checkbox" name="remove_video" value="1" /> <span>Remove uploaded video</span></label>
            </div>
          @endif
          <div class="adm-field">
            <label for="video_file">…or upload a video file</label>
            <input id="video_file" name="video_file" type="file" accept="video/mp4,video/webm" />
            <span class="adm-help">MP4 or WebM, max {{ $videoLimit }} on this server. An uploaded file is used instead of the link.</span>
          </div>
        </div>
      </div>

      <aside class="adm-detail__side">
        <div class="adm-card">
          <h2 class="adm-label">Publish</h2>
          <label class="adm-check">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $product->is_published)) />
            <span>Published</span>
          </label>
          <label class="adm-check">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) />
            <span>Featured (shown first)</span>
          </label>
          <div class="adm-field">
            <label for="status">Product status</label>
            <select id="status" name="status">
              @foreach (\App\Models\Product::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $product->status) === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="adm-field">
            <label for="sort_order">Order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $product->sort_order ?? 0) }}" />
            <span class="adm-help">Lower numbers appear first.</span>
          </div>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">
            {{ $product->exists ? 'Save product' : 'Create product' }}
          </button>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Details</h2>
          <div class="adm-field">
            <label for="category">Category</label>
            <input id="category" name="category" type="text" value="{{ old('category', $product->category) }}" placeholder="e.g. SaaS, Mobile app, Chrome extension" />
          </div>
          <div class="adm-field">
            <label for="tech_stack">Tech stack</label>
            <input id="tech_stack" name="tech_stack" type="text" value="{{ old('tech_stack', $product->tech_stack) }}" placeholder="Laravel, React, AWS" />
            <span class="adm-help">Comma separated.</span>
          </div>
          <div class="adm-field">
            <label for="product_url">Product link</label>
            <input id="product_url" name="product_url" type="url" value="{{ old('product_url', $product->product_url) }}" placeholder="https://…" />
          </div>
          <div class="adm-field">
            <label for="cta_label">Button text</label>
            <input id="cta_label" name="cta_label" type="text" maxlength="60" value="{{ old('cta_label', $product->cta_label) }}" placeholder="Visit product" />
          </div>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Logo</h2>
          @if ($product->logoUrl())
            <div class="adm-image-preview">
              <img src="{{ $product->logoUrl() }}" alt="Current logo" style="max-width:120px" />
              <label class="adm-check"><input type="checkbox" name="remove_logo" value="1" /> <span>Remove logo</span></label>
            </div>
          @endif
          <div class="adm-field">
            <input id="logo" name="logo" type="file" accept="image/*" />
            <span class="adm-help">Square, at least 256×256px. Max {{ $imageLimit }}.</span>
          </div>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">Cover image</h2>
          @if ($product->coverUrl())
            <div class="adm-image-preview">
              <img src="{{ $product->coverUrl() }}" alt="Current cover" />
              <label class="adm-check"><input type="checkbox" name="remove_cover" value="1" /> <span>Remove cover</span></label>
            </div>
          @endif
          <div class="adm-field">
            <input id="cover_image" name="cover_image" type="file" accept="image/*" />
            <span class="adm-help">Shown on the card. 1600×1000px (16:10). Max {{ $imageLimit }}.</span>
          </div>
          <div class="adm-field">
            <label for="cover_image_alt">Cover alt text</label>
            <input id="cover_image_alt" name="cover_image_alt" type="text" value="{{ old('cover_image_alt', $product->cover_image_alt) }}" />
          </div>
        </div>

        <div class="adm-card">
          <h2 class="adm-label">SEO</h2>
          <div class="adm-field">
            <label for="meta_title">Meta title</label>
            <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $product->meta_title) }}" placeholder="Defaults to name — tagline" />
          </div>
          <div class="adm-field">
            <label for="meta_description">Meta description</label>
            <textarea id="meta_description" name="meta_description" rows="3" placeholder="Defaults to the card summary">{{ old('meta_description', $product->meta_description) }}</textarea>
          </div>
        </div>
      </aside>
    </div>
  </form>

  @if ($product->exists)
    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="adm-danger-form"
          onsubmit="return confirm('Delete this product and all its images and video permanently?');">
      @csrf
      @method('DELETE')
      <button type="submit" class="adm-btn adm-btn--danger">Delete product</button>
    </form>
  @endif
@endsection
