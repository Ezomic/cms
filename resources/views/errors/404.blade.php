<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 · {{ __('site.not_found_headline') }}</title>
<meta name="robots" content="noindex">
@include('partials.fonts')
<style>
  :root{
    --bg:#fbf7ef; --ink:#221d17; --muted:#6b6258; --line:#e7ddcd; --line-strong:#d8ccb8;
    --primary:#4b3fd0; --deep:#3a2fb0;
    --geo:'Jost','Futura','Avenir Next',system-ui,sans-serif;
    --body:'Inter',system-ui,-apple-system,sans-serif;
    --mono:'IBM Plex Mono',ui-monospace,monospace;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.55;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;align-items:center;justify-content:center;}
  ::selection{background:var(--primary);color:#fff;}
  a{color:inherit;text-decoration:none;}
  a:focus-visible{outline:2px solid var(--primary);outline-offset:2px;border-radius:3px;}
  .wrap{max-width:560px;padding:32px;}
  .big{font-family:var(--geo);font-weight:700;font-size:clamp(4rem,14vw,7rem);line-height:1;letter-spacing:-.03em;color:var(--primary);}
  h1{font-family:var(--geo);font-weight:700;font-size:clamp(1.6rem,4vw,2.2rem);line-height:1.1;letter-spacing:-.02em;margin:14px 0 12px;}
  p{color:var(--muted);margin-bottom:28px;max-width:42ch;}
  .actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center;}
  .btn{font-family:var(--geo);font-weight:600;font-size:14px;border-radius:10px;padding:12px 20px;display:inline-flex;align-items:center;gap:8px;border:1.5px solid transparent;}
  .btn.pri{background:var(--primary);color:#fff;}
  .btn.pri:hover{background:var(--deep);}
  .btn.sec{background:#fff;color:var(--ink);border-color:var(--line-strong);}
  .btn.sec:hover{border-color:var(--ink);}
</style>
</head>
<body>
<main class="wrap">
  <div class="big">404</div>
  <h1>{{ __('site.not_found_headline') }}</h1>
  <p>{{ __('site.not_found_body') }}</p>
  <div class="actions">
    <a class="btn pri" href="{{ route('home') }}">{{ __('site.not_found_home') }}</a>
    <a class="btn sec" href="{{ route('work.index') }}">{{ __('site.not_found_work') }}</a>
  </div>
</main>
</body>
</html>
