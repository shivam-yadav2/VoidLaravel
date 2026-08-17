@extends('admin.layout')

@section('title', 'Feature cards')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Feature cards</h1>
      <p class="adm-muted">Powers the "What We Build" cards on Services and the "How We Work" cards on About.</p>
    </div>
    <a href="{{ route('admin.feature-cards.create', ['group' => $group]) }}" class="adm-btn adm-btn--primary">Add card</a>
  </div>

  <div class="adm-tabs">
    <a href="{{ route('admin.feature-cards.index', ['group' => 'services']) }}"
       class="adm-tab {{ $group === 'services' ? 'is-active' : '' }}">Services page</a>
    <a href="{{ route('admin.feature-cards.index', ['group' => 'about_values']) }}"
       class="adm-tab {{ $group === 'about_values' ? 'is-active' : '' }}">About page</a>
  </div>

  @if ($cards->isEmpty())
    <div class="adm-empty"><p>No cards in this group yet.</p></div>
  @else
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead><tr><th></th><th>Title</th><th>Description</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach ($cards as $card)
            <tr>
              <td>
                @if ($card->iconUrl())
                  <img src="{{ $card->iconUrl() }}" alt="" class="adm-avatar" />
                @endif
              </td>
              <td><a href="{{ route('admin.feature-cards.edit', $card) }}" class="adm-link">{{ $card->title }}</a></td>
              <td class="adm-small">{{ \Illuminate\Support\Str::limit($card->description, 60) }}</td>
              <td>{{ $card->sort_order }}</td>
              <td><span class="adm-badge adm-badge--{{ $card->is_active ? 'contacted' : 'archived' }}">{{ $card->is_active ? 'active' : 'hidden' }}</span></td>
              <td class="adm-right"><a href="{{ route('admin.feature-cards.edit', $card) }}" class="adm-btn adm-btn--sm">Edit</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
