@extends('admin.layout')

@section('title', 'Products')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Products</h1>
      <p class="adm-muted">{{ $counts['all'] }} total · {{ $counts['published'] }} published — shown on the homepage and at <a href="{{ route('products.index') }}" target="_blank" rel="noopener" class="adm-link">/products</a></p>
    </div>
    <div class="adm-head-actions">
      <form method="GET" action="{{ route('admin.products.index') }}" class="adm-search">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search products…" />
        <button type="submit" class="adm-btn">Search</button>
      </form>
      <a href="{{ route('admin.products.create') }}" class="adm-btn adm-btn--primary">New product</a>
    </div>
  </div>

  @if ($products->isEmpty())
    <div class="adm-empty">
      <p>No products {{ $q ? 'match that search' : 'yet' }}.</p>
      <p class="adm-muted">Add a SaaS product, app or tool you've built. The homepage section stays hidden until one is published.</p>
    </div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr><th></th><th>Product</th><th>Category</th><th>Status</th><th>Media</th><th>Order</th><th></th></tr>
        </thead>
        <tbody>
          @foreach ($products as $product)
            <tr>
              <td style="width: 56px">
                @if ($product->logoUrl() ?: $product->coverUrl())
                  <img src="{{ $product->logoUrl() ?: $product->coverUrl() }}" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 10px;" />
                @endif
              </td>
              <td>
                <a href="{{ route('admin.products.edit', $product) }}" class="adm-link">{{ $product->name }}</a>
                <div class="adm-muted adm-small">{{ $product->tagline ?: '/products/'.$product->slug }}</div>
              </td>
              <td>{{ $product->category ?: '—' }}</td>
              <td>
                <span class="adm-badge adm-badge--{{ $product->is_published ? 'contacted' : 'archived' }}">{{ $product->is_published ? 'published' : 'draft' }}</span>
                <span class="adm-badge adm-badge--new">{{ strtolower($product->statusLabel()) }}</span>
                @if ($product->is_featured)
                  <span class="adm-badge adm-badge--new">featured</span>
                @endif
              </td>
              <td class="adm-muted adm-small">
                {{ $product->images_count }} image{{ $product->images_count === 1 ? '' : 's' }}{{ $product->video() ? ' · video' : '' }}
              </td>
              <td class="adm-muted adm-small">{{ $product->sort_order }}</td>
              <td class="adm-right">
                @if ($product->is_published)
                  <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener" class="adm-btn adm-btn--sm">View</a>
                @endif
                <a href="{{ route('admin.products.edit', $product) }}" class="adm-btn adm-btn--sm">Edit</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
