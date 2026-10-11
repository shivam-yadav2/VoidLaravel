{{-- Self-contained on purpose: no layout, no $site, no database. A 500 can
     mean the database (or the app itself) is down, and an error page that
     needs either would fail too. --}}
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex" />
    <title>Something went wrong | {{ config('app.name') }}</title>
    <style>
      body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #000; color: #fff; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; text-align: center; padding: 24px; box-sizing: border-box; }
      .code { color: #eaff48; font-size: 14px; letter-spacing: 3px; font-weight: 700; margin: 0 0 12px; }
      h1 { font-size: clamp(28px, 6vw, 48px); margin: 0 0 16px; }
      p { color: #dfe0e4; max-width: 520px; margin: 0 auto 28px; line-height: 1.6; }
      a { display: inline-block; background: #eaff48; color: #000; padding: 14px 28px; border-radius: 100px; font-weight: 600; text-decoration: none; }
    </style>
  </head>
  <body>
    <main>
      <p class="code">500</p>
      <h1>Something went wrong</h1>
      <p>An unexpected error happened on our side. It has been logged — please try again in a few minutes.</p>
      <a href="/">Back to home</a>
    </main>
  </body>
</html>
