<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $project->name }} — {{ $profile->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#F7F7F4; --ink:#17181A; --ink-soft:#63645F; --line:#DDDDD6;
    --accent:#E8590C; --accent-soft:#FCE6D8; --white:#FFFFFF;
    --display:'Space Grotesk',sans-serif; --body:'Inter',sans-serif; --mono:'IBM Plex Mono',monospace;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.6;-webkit-font-smoothing:antialiased;}
  a{color:inherit;}
  .wrap{max-width:820px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .wrap{max-width:1120px;display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .back-link{font-family:var(--mono);font-size:13px;text-decoration:none;color:var(--ink-soft);}
  .back-link:hover{color:var(--ink);}
  header.hero{padding:64px 0 40px;border-bottom:1px solid var(--line);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3rem);line-height:1.1;letter-spacing:-.02em;margin-bottom:20px;}
  .meta-row{display:flex;gap:24px;flex-wrap:wrap;font-family:var(--mono);font-size:13px;color:var(--ink-soft);margin-bottom:24px;}
  .work-tags{display:flex;flex-wrap:wrap;gap:8px;}
  .tag{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;border-radius:20px;white-space:nowrap;}
  .body-content{padding:24px 0 96px;font-size:17px;color:var(--ink);}
  .body-content p{margin-bottom:1.2em;white-space:pre-line;}
  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);border-top:1px solid var(--line);}
  footer .wrap{max-width:1120px;}
</style>
</head>
<body>

<nav>
  <div class="wrap">
    <div class="logo"><span class="dot"></span>{{ strtoupper($profile->name) }} / NL</div>
    <a class="back-link" href="{{ route('home') }}">← Back to site</a>
  </div>
</nav>

<header class="hero">
  <div class="wrap">
    <div class="eyebrow">Case study</div>
    <h1>{{ $project->name }}</h1>
    <div class="meta-row">
      @if ($project->client_name)<span>{{ $project->client_name }}</span>@endif
      @if ($project->year)<span>{{ $project->year }}</span>@endif
    </div>
    <div class="work-tags">
      @foreach ($project->tagList() as $tag)
        <span class="tag">{{ $tag }}</span>
      @endforeach
    </div>
  </div>
</header>

<div class="wrap">
  <div class="body-content">
    <p>{{ $project->body }}</p>
  </div>
</div>

<footer>
  <div class="wrap">© {{ date('Y') }} {{ $profile->name }}.</div>
</footer>

</body>
</html>
