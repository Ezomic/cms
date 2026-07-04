<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — {{ __('site.not_found_headline') }}</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#F7F7F4; --ink:#17181A; --ink-soft:#63645F; --line:#DDDDD6;
    --accent:#E8590C; --accent-soft:#FCE6D8; --white:#FFFFFF;
    --display:'Space Grotesk',sans-serif; --body:'Inter',sans-serif; --mono:'IBM Plex Mono',monospace;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.5;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;align-items:center;justify-content:center;}
  ::selection{background:var(--accent);color:var(--white);}
  .wrap{max-width:560px;padding:32px;text-align:left;}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3rem);line-height:1.1;letter-spacing:-.02em;margin-bottom:16px;}
  p{color:var(--ink-soft);margin-bottom:32px;}
  .actions{display:flex;gap:16px;flex-wrap:wrap;align-items:center;}
  .btn{font-family:var(--mono);font-size:14px;background:var(--ink);color:var(--white);padding:14px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:background .15s;}
  .btn:hover{background:var(--accent);}
  .link{font-family:var(--mono);font-size:14px;color:var(--ink-soft);text-decoration:none;border-bottom:1px solid var(--line);}
  .link:hover{color:var(--ink);}
</style>
</head>
<body>
<div class="wrap">
  <div class="eyebrow">404</div>
  <h1>{{ __('site.not_found_headline') }}</h1>
  <p>{{ __('site.not_found_body') }}</p>
  <div class="actions">
    <a class="btn" href="{{ route('home') }}">{{ __('site.not_found_home') }}</a>
    <a class="link" href="{{ route('work.index') }}">{{ __('site.not_found_work') }}</a>
  </div>
</div>
</body>
</html>
