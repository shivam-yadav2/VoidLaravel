@extends('admin.layout')

@section('title', 'Settings')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Site settings</h1>
      <p class="adm-muted">Contact details, social links and footer copy used across the public site.</p>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    @foreach ($groups as $groupKey => $group)
      <div class="adm-card">
        <h2 class="adm-label">{{ $group['label'] }}</h2>

        <div class="adm-grid-2">
          @foreach ($group['fields'] as $key => $field)
            <div class="adm-field {{ $field['type'] === 'textarea' ? 'adm-field--full' : '' }}">
              <label for="s-{{ $key }}">{{ $field['label'] }}</label>

              @if ($field['type'] === 'textarea')
                <textarea id="s-{{ $key }}" name="{{ $key }}" rows="3">{{ old($key, $site->get($key)) }}</textarea>
              @else
                <input
                  id="s-{{ $key }}"
                  name="{{ $key }}"
                  type="{{ $field['type'] === 'url' ? 'url' : ($field['type'] === 'email' ? 'email' : 'text') }}"
                  value="{{ old($key, $site->get($key)) }}"
                />
              @endif

              @isset($field['help'])
                <span class="adm-help">{{ $field['help'] }}</span>
              @endisset
              @error($key)<span class="adm-field-error">{{ $message }}</span>@enderror
            </div>
          @endforeach
        </div>
      </div>
    @endforeach

    <button type="submit" class="adm-btn adm-btn--primary">Save settings</button>
  </form>
@endsection
