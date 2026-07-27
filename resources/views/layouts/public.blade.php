<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@yield('head')
@include('partials.fonts')
<style>
  :root{
    --bg:#fbf7ef; --surface:#ffffff; --raise:#f4eee2;
    --ink:#221d17; --muted:#6b6258; --faint:#9a9188; --line:#e7ddcd; --line-strong:#d8ccb8;
    --primary:#4b3fd0; --soft:#e7e4fb; --deep:#3a2fb0;
    --ok:#1f6b4a; --ok-soft:#dcefe4; --danger:#b3382f; --danger-soft:#f6e0dd;
    --geo:'Jost','Futura','Avenir Next',system-ui,sans-serif;
    --body:'Inter',system-ui,-apple-system,sans-serif;
    --mono:'IBM Plex Mono',ui-monospace,Menlo,monospace;
    --r:10px; --r-lg:16px; --wrap:1120px;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  @media (prefers-reduced-motion:reduce){html{scroll-behavior:auto;}*{transition-duration:.01ms !important;animation-duration:.01ms !important;}}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.55;-webkit-font-smoothing:antialiased;}
  a{color:inherit;text-decoration:none;}
  ::selection{background:var(--primary);color:#fff;}
  a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,select:focus-visible{outline:2px solid var(--primary);outline-offset:2px;border-radius:3px;}
  h1,h2,h3,h4{font-family:var(--geo);font-weight:700;letter-spacing:-.02em;color:var(--ink);}
  section[id]{scroll-margin-top:78px;}
  .wrap{max-width:var(--wrap);margin:0 auto;padding:0 28px;}
  .eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:var(--primary);}
  .skip-link{position:absolute;left:-9999px;top:0;z-index:100;background:var(--ink);color:#fff;font-family:var(--mono);font-size:13px;padding:10px 16px;}
  .skip-link:focus{left:0;}
  .tag{font-family:var(--mono);font-size:11px;color:var(--muted);border:1px solid var(--line-strong);border-radius:20px;padding:3px 10px;white-space:nowrap;}

  /* buttons */
  .btn{font-family:var(--geo);font-weight:600;font-size:14px;border-radius:var(--r);padding:12px 20px;display:inline-flex;align-items:center;gap:8px;border:1.5px solid transparent;cursor:pointer;transition:background .15s,border-color .15s,color .15s;}
  .btn.pri{background:var(--primary);color:#fff;}
  .btn.pri:hover{background:var(--deep);}
  .btn.sec{background:var(--surface);color:var(--ink);border-color:var(--line-strong);}
  .btn.sec:hover{border-color:var(--ink);}
  .btn.sm{padding:9px 15px;font-size:13px;}

  /* nav */
  nav.site{position:sticky;top:0;z-index:50;background:rgba(251,247,239,.85);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);}
  nav.site .wrap{display:flex;align-items:center;justify-content:space-between;height:66px;}
  .logo{font-family:var(--geo);font-weight:700;font-size:17px;display:flex;align-items:center;gap:9px;}
  .logo .b{width:16px;height:16px;border-radius:5px;background:var(--primary);}
  .nav-links{display:flex;align-items:center;gap:26px;}
  .nav-links a.link{font-family:var(--geo);font-weight:500;font-size:14.5px;color:var(--muted);transition:color .15s;}
  .nav-links a.link:hover,.nav-links a.link.on{color:var(--ink);}
  .lang-toggle{font-family:var(--mono);font-size:12px;color:var(--muted);border:1px solid var(--line-strong);border-radius:7px;padding:4px 9px;transition:color .15s,border-color .15s;}
  .lang-toggle:hover{color:var(--ink);border-color:var(--ink);}
  .nav-right{display:flex;align-items:center;gap:12px;}
  .nav-burger{display:none;background:none;border:1px solid var(--line-strong);border-radius:8px;width:40px;height:40px;cursor:pointer;padding:0;flex-direction:column;align-items:center;justify-content:center;gap:4px;}
  .nav-burger span{display:block;width:16px;height:2px;background:var(--ink);transition:transform .2s,opacity .2s;}
  .nav-burger[aria-expanded="true"] span:nth-child(1){transform:translateY(6px) rotate(45deg);}
  .nav-burger[aria-expanded="true"] span:nth-child(2){opacity:0;}
  .nav-burger[aria-expanded="true"] span:nth-child(3){transform:translateY(-6px) rotate(-45deg);}
  .mobile-menu{display:none;border-top:1px solid var(--line);}
  .mobile-menu.open{display:block;}
  .mobile-menu .wrap{display:block;height:auto;padding-top:6px;padding-bottom:10px;}
  .mobile-menu a{display:block;font-family:var(--geo);font-weight:500;font-size:15px;color:var(--ink);padding:13px 0;border-bottom:1px solid var(--line);}
  .mobile-menu a:last-child{border-bottom:none;color:var(--muted);}
  @media (max-width:900px){.nav-desktop{display:none;}.nav-burger{display:flex;}}

  /* generic section */
  section.block{padding:66px 0;border-bottom:1px solid var(--line);}
  .section-head{margin-bottom:38px;}
  .section-head .eyebrow{display:block;margin-bottom:12px;}
  .section-head h2{font-size:clamp(1.5rem,3vw,2.1rem);}

  /* work / project cards */
  .card-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
  .pcard{display:flex;flex-direction:column;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);overflow:hidden;transition:border-color .15s;}
  .pcard:hover{border-color:var(--primary);}
  .pcard .thumb{aspect-ratio:16/10;background:var(--soft);object-fit:cover;width:100%;display:block;}
  .pcard .thumb.ph{display:flex;align-items:flex-end;padding:12px;}
  .pcard .thumb.ph .k{font-family:var(--mono);font-size:10.5px;color:var(--deep);background:var(--surface);border-radius:20px;padding:3px 9px;}
  .pcard .in{padding:16px 17px 18px;flex:1;display:flex;flex-direction:column;}
  .pcard h3{font-size:17px;}
  .pcard .meta{font-family:var(--mono);font-size:11px;color:var(--faint);margin-top:4px;}
  .pcard p{font-size:13.5px;color:var(--muted);margin-top:8px;}
  .pcard .outcome{font-family:var(--mono);font-size:12px;color:var(--primary);margin-top:10px;}
  .pcard .tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:auto;padding-top:14px;}
  @media (max-width:820px){.card-grid{grid-template-columns:1fr 1fr;}}
  @media (max-width:560px){.card-grid{grid-template-columns:1fr;}}

  /* footer */
  footer.site{padding:34px 0;}
  footer.site .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;font-family:var(--mono);font-size:12px;color:var(--muted);}
  footer.site a:hover{color:var(--ink);}
</style>
@stack('styles')
</head>
<body>

<a class="skip-link" href="#main">{{ __('site.skip_to_content') }}</a>

@include('partials.public-nav')

<main id="main" tabindex="-1">
@yield('content')
</main>

@include('partials.public-footer')

<script>
  (function(){
    var burger = document.querySelector('.nav-burger');
    var menu = document.getElementById('mobile-menu');
    if (!burger || !menu) return;
    burger.addEventListener('click', function(){
      var open = menu.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    menu.querySelectorAll('a').forEach(function(link){
      link.addEventListener('click', function(){
        menu.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
      });
    });
  })();
</script>
@stack('scripts')

</body>
</html>
