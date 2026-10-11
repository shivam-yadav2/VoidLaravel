{{-- Heading and message are editable at Admin → Settings → “Page not found” page. --}}
@include('errors.public', [
  'code' => 404,
  'heading' => $site->not_found_title,
  'message' => $site->not_found_message,
])
