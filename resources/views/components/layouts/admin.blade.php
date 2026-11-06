@props(['title' => 'Admin', 'section' => null, 'pane' => 'detail', 'shell' => true])
@php
    $nav = [
        ['dashboard', 'grid', 'Bosh sahifa', null, 'Bosh', route('admin.dashboard')],
        ['posts', 'doc', 'Yozuvlar', 'posts', 'Yozuvlar', route('admin.posts.index')],
        ['projects', 'folder', 'Loyihalar', 'projects', 'Loyihalar', route('admin.projects.index')],
        ['settings', 'gear', 'Sozlamalar', null, 'Sozlamalar', route('admin.settings.edit')],
    ];
    $counts = ['posts' => $postTotal ?? 0, 'projects' => $projectTotal ?? 0];
@endphp
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#09090b">
    <title>{{ $title }} — Admin</title>
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
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body data-route="admin" x-data="admin(@js(session('toast')), @js(route('admin.posts.create')))">
    <div id="admin">
        @if ($shell)
            <div class="adm" data-section="{{ $section }}" data-pane="{{ $pane }}">
                <aside class="a-side" aria-label="Admin menyu">
                    <a class="c as-me" href="{{ route('admin.dashboard') }}"><span class="av">U</span><span><b>Utkirbek</b><small>Admin panel</small></span></a>
                    <a class="as-new" href="{{ route('admin.posts.create') }}"><x-icon name="plus" /><span>Yangi yozuv</span><kbd>N</kbd></a>
                    <nav class="as-nav" aria-label="Boʻlimlar">
                        @foreach ($nav as [$key, $icon, $label, $count, , $href])
                            <a class="c as-t{{ $section === $key ? ' on' : '' }}" href="{{ $href }}" @if ($section === $key) aria-current="page" @endif>
                                <x-icon :name="$icon" /><span>{{ $label }}</span>@if ($count)<span class="as-c" data-count="{{ $count }}">{{ $counts[$count] }}</span>@endif
                            </a>
                        @endforeach
                    </nav>
                    <span class="as-sp"></span>
                    <div class="as-x">
                        <a class="c as-b" href="{{ route('home') }}"><x-icon name="eye" /><span>Sayt</span></a>
                        <button class="c as-b" type="button" x-on:click="$store.theme.toggle()"><x-icon name="moon" class="t-moon" /><x-icon name="sun" class="t-sun" /><span>Mavzu</span></button>
                        <form method="POST" action="{{ route('admin.logout') }}" style="display:contents">
                            @csrf
                            <button class="c as-b" type="submit"><x-icon name="logout" /><span>Chiqish</span></button>
                        </form>
                    </div>
                </aside>
                @isset($list){{ $list }}@endisset
                <section class="a-detail" id="adetail">{{ $slot }}</section>
                <nav class="abar" aria-label="Admin boʻlimlari">
                    @foreach ($nav as [$key, $icon, , , $short, $href])
                        <a class="ab{{ $section === $key ? ' on' : '' }}" href="{{ $href }}"><x-icon :name="$icon" /><span>{{ $short }}</span></a>
                    @endforeach
                    <a class="ab" href="{{ route('home') }}"><x-icon name="eye" /><span>Sayt</span></a>
                </nav>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
    <div class="toast glass" :class="{ show: toastText }" role="status" aria-live="polite"><span x-text="toastText"></span><template x-if="toastAction"><a :href="toastAction.href" x-text="toastAction.label"></a></template></div>
    <x-alert />
</body>
</html>
