@props([
    'title' => 'Utkirbek',
    'description' => 'Utkirbek — oʻrganish, ustozlik, til va hayot haqida fikrlar. Alohida boʻlimda dasturlash.',
    'route' => 'home',
])
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#09090b">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        (function () {
            var r = document.documentElement, m = 'dark';
            try { m = localStorage.getItem('ut4_theme') || 'dark'; } catch (e) {}
            var d = m === 'dark' || (m === 'auto' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
            r.dataset.theme = d ? 'dark' : 'light';
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-route="{{ $route }}" x-data="site(@js(collect(['grid', 'bookOpen', 'code', 'folder', 'branch', 'person'])->mapWithKeys(fn ($name) => [$name => view('components.icon', ['name' => $name, 'size' => 20])->render()])), @js(route('search')))">
    <div id="bgfx" aria-hidden="true"></div>
    <div id="site">
        <x-nav.tnav />
        <x-nav.tmini />
        <main id="view" class="view-in">{{ $slot }}</main>
        <footer class="foot">© {{ now()->year }} Utkirbek · Toshkent</footer>
    </div>
    <div class="toast glass" :class="{ show: toastText }" role="status" aria-live="polite"><span x-text="toastText"></span><template x-if="toastAction"><a :href="toastAction.href" x-text="toastAction.label"></a></template></div>
    <x-palette />
</body>
</html>
