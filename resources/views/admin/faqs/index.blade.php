@extends('admin.layout')

@section('title', 'FAQs')

@section('content')
  <div class="adm-page-head">
    <div>
      <h1>Homepage FAQs</h1>
      <p class="adm-muted">Shown in the FAQ accordion on the homepage, in this order.</p>
    </div>
  </div>

  <div class="adm-detail">
    <div class="adm-detail__main">
      @if ($faqs->isEmpty())
        <div class="adm-empty"><p>No FAQs yet.</p></div>
      @else
        @foreach ($faqs as $faq)
          <div class="adm-card">
            <form method="POST" action="{{ route('admin.faqs.update', $faq) }}">
              @csrf
              @method('PUT')
              <div class="adm-field">
                <label>Question</label>
                <input type="text" name="question" value="{{ $faq->question }}" required />
              </div>
              <div class="adm-field">
                <label>Answer</label>
                <textarea name="answer" rows="2" required>{{ $faq->answer }}</textarea>
              </div>
              <div class="adm-field-row">
                <div class="adm-field adm-field--inline">
                  <label>Order</label>
                  <input type="number" name="sort_order" value="{{ $faq->sort_order }}" min="0" style="width:80px" />
                </div>
                <label class="adm-check adm-check--inline">
                  <input type="checkbox" name="is_active" value="1" @checked($faq->is_active) />
                  <span>Active</span>
                </label>
                <button type="submit" class="adm-btn adm-btn--sm">Save</button>
              </div>
            </form>
            <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}"
                  onsubmit="return confirm('Delete this FAQ?');" class="adm-inline-delete">
              @csrf
              @method('DELETE')
              <button type="submit" class="adm-btn adm-btn--sm adm-btn--danger">Delete</button>
            </form>
          </div>
        @endforeach
      @endif
    </div>

    <aside class="adm-detail__side">
      <div class="adm-card">
        <h2 class="adm-label">Add FAQ</h2>
        <form method="POST" action="{{ route('admin.faqs.store') }}">
          @csrf
          <div class="adm-field">
            <label>Question</label>
            <input type="text" name="question" value="{{ old('question') }}" required />
          </div>
          <div class="adm-field">
            <label>Answer</label>
            <textarea name="answer" rows="3" required>{{ old('answer') }}</textarea>
          </div>
          <div class="adm-field">
            <label>Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" />
          </div>
          <label class="adm-check">
            <input type="checkbox" name="is_active" value="1" checked />
            <span>Active</span>
          </label>
          <button type="submit" class="adm-btn adm-btn--primary adm-btn--block">Add FAQ</button>
        </form>
      </div>
    </aside>
  </div>
@endsection
