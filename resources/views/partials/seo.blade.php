@php
  /**
   * Central SEO block. Pages pass a `$seo` array; anything omitted falls back
   * to the admin-managed site settings so every URL still ships a sane title,
   * description, canonical and social card.
   *
   * Supported keys: key, title, description, image, canonical, type, noindex,
   * published_time, modified_time, author, jsonld (one schema.org object or a
   * list of them), breadcrumbs (list of [name, url] pairs)
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
      'breadcrumbs' => [],
  ], $seo ?? []);

  // Anything an admin filled in at /admin/seo outranks the controller default.
  $seo = array_merge($seo, \App\Models\SeoMeta::overridesFor($seo['key']));

  $siteName = $site->site_name;
  $metaTitle = $seo['title'] ? $seo['title'].' | '.$siteName : $siteName.' — '.$site->site_tagline;
  $metaDescription = str(strip_tags($seo['description'] ?: $site->site_description))->squish()->limit(160)->toString();
  $metaImage = $seo['image'] ?: asset($site->site_og_image ?: 'assets/img/void_logo.png');

  // "Lucknow, Uttar Pradesh" -> locality + region, so the address is
  // machine-readable rather than one free-text blob.
  $addressParts = array_map('trim', explode(',', (string) $site->contact_address));

  $organization = array_filter([
      '@type' => 'Organization',
      '@id' => url('/').'#organization',
      'name' => $siteName,
      'url' => url('/'),
      'logo' => asset('assets/img/void_logo.png'),
      'description' => $site->site_description,
      'email' => $site->contact_email,
      'telephone' => $site->contact_phone_link,
      'address' => array_filter([
          '@type' => 'PostalAddress',
          'addressLocality' => $addressParts[0] ?? null,
          'addressRegion' => $addressParts[1] ?? null,
          'addressCountry' => 'IN',
      ]),
      'contactPoint' => array_filter([
          '@type' => 'ContactPoint',
          'contactType' => 'sales',
          'email' => $site->contact_email,
          'telephone' => $site->contact_phone_link,
          'areaServed' => 'Worldwide',
          'availableLanguage' => ['English', 'Hindi'],
      ]),
      'sameAs' => array_values($site->socialLinks()) ?: null,
  ]);

  $graph = [$organization];

  if (request()->routeIs('home')) {
      $graph[] = [
          '@type' => 'WebSite',
          '@id' => url('/').'#website',
          'name' => $siteName,
          'url' => url('/'),
          'publisher' => ['@id' => url('/').'#organization'],
      ];
  }

  if ($seo['breadcrumbs']) {
      $graph[] = [
          '@type' => 'BreadcrumbList',
          'itemListElement' => collect($seo['breadcrumbs'])->values()->map(fn ($crumb, $i) => [
              '@type' => 'ListItem',
              'position' => $i + 1,
              'name' => $crumb[0],
              'item' => $crumb[1],
          ])->all(),
      ];
  }

  if ($seo['jsonld']) {
      $pageSchemas = array_is_list($seo['jsonld']) ? $seo['jsonld'] : [$seo['jsonld']];
      foreach ($pageSchemas as $schema) {
          unset($schema['@context']);
          $graph[] = $schema;
      }
  }
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}" />
<link rel="canonical" href="{{ $seo['canonical'] }}" />

@if ($seo['noindex'])
  <meta name="robots" content="noindex, follow" />
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
<meta property="og:locale" content="en_IN" />
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
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
