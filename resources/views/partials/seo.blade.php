@php
  /**
   * Central SEO block. Pages pass a `$seo` array; anything omitted falls back
   * to the admin-managed site settings so every URL still ships a sane title,
   * description, canonical and social card.
   *
   * Supported keys: title, description, image, canonical, type, noindex,
   * published_time, modified_time, author, jsonld (array|null)
   */
  $seo = array_merge([
      'key' => null,
      'title' => null,
      'description' => null,
      'image' => null,
      'canonical' => url()->current(),
      'type' => 'website',
      'noindex' => false,
      'published_time' => null,
      'modified_time' => null,
      'author' => null,
      'jsonld' => null,
  ], $seo ?? []);

  // Anything an admin filled in at /admin/seo outranks the controller default.
  $seo = array_merge($seo, \App\Models\SeoMeta::overridesFor($seo['key']));

  $siteName = $site->site_name;
  $metaTitle = $seo['title'] ? $seo['title'].' | '.$siteName : $siteName.' — '.$site->site_tagline;
  $metaDescription = str(strip_tags($seo['description'] ?: $site->site_description))->squish()->limit(160)->toString();
  $metaImage = $seo['image'] ?: asset($site->site_og_image ?: 'assets/img/void_logo.png');
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}" />
<link rel="canonical" href="{{ $seo['canonical'] }}" />

@if ($seo['noindex'])
  <meta name="robots" content="noindex, nofollow" />
@else
  <meta name="robots" content="index, follow, max-image-preview:large" />
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}" />
<meta property="og:type" content="{{ $seo['type'] }}" />
<meta property="og:title" content="{{ $metaTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:url" content="{{ $seo['canonical'] }}" />
<meta property="og:image" content="{{ $metaImage }}" />
<meta property="og:locale" content="en_US" />
@if ($seo['published_time'])
  <meta property="article:published_time" content="{{ $seo['published_time'] }}" />
@endif
@if ($seo['modified_time'])
  <meta property="article:modified_time" content="{{ $seo['modified_time'] }}" />
@endif
@if ($seo['author'])
  <meta property="article:author" content="{{ $seo['author'] }}" />
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $metaTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $metaImage }}" />

{{-- Structured data --}}
@if ($seo['jsonld'])
  <script type="application/ld+json">{!! json_encode($seo['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@else
  <script type="application/ld+json">{!! json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      'name' => $siteName,
      'url' => url('/'),
      'logo' => asset('assets/img/void_logo.png'),
      'description' => $site->site_description,
      'email' => $site->contact_email,
      'telephone' => $site->contact_phone_link,
      'address' => [
          '@type' => 'PostalAddress',
          'addressLocality' => $site->contact_address,
      ],
      'sameAs' => array_values($site->socialLinks()),
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
