@extends('admin.layout')

@section('title', 'Categories')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Blog categories</h1>
      <p class="adm-muted">Deleting a category keeps its posts — they simply become uncategorised.</p>
    </div>
  </div>

  <div class="adm-detail">
    <div class="adm-detail__main">
      @if ($categories->isEmpty())
        <div class="adm-empty"><p>No categories yet.</p></div>
      @else
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th></th></tr></thead>
            <tbody>
              @foreach ($categories as $category)
                <tr>
                  <td>
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="adm-inline-form">
                      @csrf
                      @method('PUT')
                      <input type="text" name="name" value="{{ $category->name }}" required />
                      <input type="hidden" name="slug" value="{{ $category->slug }}" />
                      <button type="submit" class="adm-btn adm-btn--sm">Save</button>
                    </form>
                  </td>
                  <td class="adm-muted adm-small">{{ $category->slug }}</td>
                  <td>{{ $category->posts_count }}</td>
                  <td class="adm-right">
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                          onsubmit="return confirm('Delete this category?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="adm-btn adm-btn--sm adm-btn--danger">Delete</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <aside class="adm-detail__side">
      <div class="adm-card">
        <h2 class="adm-label">Add category</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}">
          @csrf
          <div class="adm-field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required />
          </div>
          <div class="adm-field">
            <label for="slug">Slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="auto-generated" />
          </div>
          <div class="adm-field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="2">{{ old('description') }}</textarea>
          </div>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">Add category</button>
        </form>
      </div>
    </aside>
  </div>
@endsection
