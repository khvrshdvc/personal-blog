<x-layouts.admin title="Bosh sahifa" section="dashboard" pane="detail">
    <div class="d-scroll">
        <div class="adash">
            <header class="ad-h">
                <div>
                    <p class="eyebrow">{{ $eyebrow }}</p>
                    <h1>{{ $greeting }}, Utkirbek. <span>Bugun nima haqida yozasiz?</span></h1>
                </div>
                <a class="dbtn" href="{{ route('home') }}"><x-icon name="eye" />Saytni koʻrish</a>
            </header>
            <div class="dgrid">
                @if ($hero)
                    <x-card tag="section" class="d-hero">
                        <x-card-title icon="compose">Oxirgi tahrir<span class="r"><span class="pill {{ $hero['status'] }}">{{ $hero['statusLabel'] }}</span></span></x-card-title>
                        <h2>{{ $hero['title'] }}</h2>
                        <p>{{ $hero['excerpt'] }}</p>
                        <span class="d-m">{{ $hero['category']->one() }} · {{ $hero['tag'] }} · {{ $hero['words'] }} soʻz · {{ $hero['ago'] }}</span>
                        <div class="d-acts">
                            <a class="dbtn pri" href="{{ route('admin.posts.edit', $hero['id']) }}"><x-icon name="pen" />Davom ettirish</a>
                            <a class="dbtn" href="{{ route('admin.posts.create', 'fikr') }}"><x-icon name="plus" />Yangi fikr</a>
                            <a class="dbtn" href="{{ route('admin.posts.create', 'dev') }}"><x-icon name="code" />Dasturlash posti</a>
                        </div>
                    </x-card>
                @else
                    <x-card tag="section" class="d-hero">
                        <x-card-title icon="compose">Boshlash</x-card-title>
                        <h2>Birinchi yozuvingizni yozing</h2>
                        <div class="d-acts">
                            <a class="dbtn pri" href="{{ route('admin.posts.create', 'fikr') }}"><x-icon name="plus" />Yangi fikr</a>
                            <a class="dbtn" href="{{ route('admin.posts.create', 'dev') }}"><x-icon name="code" />Dasturlash posti</a>
                        </div>
                    </x-card>
                @endif
                <div class="d-stats">
                    <x-card tag="a" class="d-st" :href="route('admin.posts.index')"><x-card-title icon="check">Chop etilgan</x-card-title><b>{{ $publishedCount }}</b><small>{{ $thoughtCount }} fikr · {{ $devCount }} dasturlash</small></x-card>
                    <x-card tag="a" class="d-st" :href="route('admin.posts.index')"><x-card-title icon="doc">Qoralama</x-card-title><b>{{ $draftCount }}</b><small>{{ $draftCount ? 'yakunlanmagan' : 'hammasi tayyor' }}</small></x-card>
                    <x-card tag="a" class="d-st" :href="route('admin.posts.index')"><x-card-title icon="pen">Oʻzgarish</x-card-title><b>{{ $pendingCount }}</b><small>{{ $pendingCount ? 'yangilashni kutmoqda' : 'kutilayotgan yoʻq' }}</small></x-card>
                    <x-card class="d-st"><x-card-title icon="bookOpen">Soʻzlar</x-card-title><b>{{ $words }}</b><small>chop etilgan matnda</small></x-card>
                </div>
                <x-card tag="section" class="d-cont">
                    <x-card-title icon="list">Davom ettirish<a class="r" href="{{ route('admin.posts.index') }}">Barchasi →</a></x-card-title>
                    <div class="dl">
                        @forelse ($work as $item)
                            <a class="dl-r" href="{{ route('admin.posts.edit', $item['id']) }}"><span class="sdot {{ $item['status'] }}" aria-hidden="true"></span><span class="dl-t">{{ $item['title'] }}</span><span class="dl-s">{{ $item['statusLabel'] }}</span><span class="dl-a">{{ $item['ago'] }}</span></a>
                        @empty
                            <p class="dl-e"><x-icon name="check" /> Qoralama va kutilayotgan oʻzgarish yoʻq.</p>
                        @endforelse
                    </div>
                </x-card>
                <x-card tag="section" class="d-act">
                    <x-card-title icon="grid">Yozish faolligi<span class="r">26 hafta</span></x-card-title>
                    <div class="mhm d-hm" aria-label="Yozish faolligi">
                        @foreach ($activity['cells'] as $cell)
                            @if ($cell === null)
                                <i class="x"></i>
                            @else
                                <i data-l="{{ $cell['level'] }}" title="{{ $cell['title'] }}"></i>
                            @endif
                        @endforeach
                    </div>
                    <div class="d-af">
                        <span><b>{{ $activity['since'] === null ? '—' : ($activity['since'] === 0 ? 'Bugun' : $activity['since'].' kun') }}</b>{{ $activity['since'] === null ? 'chop etilgan yozuv yoʻq' : ($activity['since'] === 0 ? 'oxirgi yozuv' : 'oxirgi yozuvdan beri') }}</span>
                        <span><b>{{ $activity['month'] }}</b>bu oy</span>
                        <span><b>{{ $activity['active'] }}</b>faol kun</span>
                    </div>
                </x-card>
                <x-card tag="section" class="d-cat">
                    <x-card-title icon="sliders">Mavzular</x-card-title>
                    <div class="cat2"><div><b>{{ $thoughtCount }}</b><small>Fikrlar</small></div><div><b>{{ $devCount }}</b><small>Dasturlash</small></div></div>
                    @php $opacity = [1, .72, .52, .36, .24, .16, .1, .07]; $i = 0; @endphp
                    <div class="tbar3" aria-hidden="true">@foreach ($tags as $tag => $count)<i style="flex:{{ $count }};opacity:{{ $opacity[$i] ?? .07 }}"></i>@php $i++; @endphp @endforeach</div>
                    @php $i = 0; @endphp
                    <div class="tleg">@foreach (array_slice($tags, 0, 6, true) as $tag => $count)<span><i style="opacity:{{ $opacity[$i] }}"></i>{{ $tag }} <em>{{ $count }}</em></span>@php $i++; @endphp @endforeach</div>
                </x-card>
                <x-card tag="section" class="d-chk">
                    <x-card-title icon="info">Sayt holati<span class="r">{{ count(array_filter($checks, fn ($c) => $c[0])) }}/{{ count($checks) }}</span></x-card-title>
                    <div class="chk">
                        @foreach ($checks as [$ok, $label, $detail])
                            <a @class(['chk-r', 'ok' => $ok]) href="{{ route('admin.settings.edit') }}"><span class="chk-i">@if ($ok)<x-icon name="check" />@endif</span><span class="chk-t"><b>{{ $label }}</b><small>{{ $detail }}</small></span></a>
                        @endforeach
                    </div>
                </x-card>
                <x-card tag="section" class="d-prj">
                    <x-card-title icon="folder">Loyihalar<a class="r" href="{{ route('admin.projects.index') }}">{{ $projectCount }} ta →</a></x-card-title>
                    <div class="pl2">
                        @forelse ($projects as $project)
                            <a class="pi2" href="{{ route('admin.projects.edit', $project['id']) }}"><span class="app-m" aria-hidden="true">{{ $project['initials'] }}</span><span class="pj-b"><b>{{ $project['name'] }}</b><span class="pj-r">{{ $project['repo'] }}</span></span></a>
                        @empty
                            <p class="dl-e">Hali loyiha yoʻq.</p>
                        @endforelse
                    </div>
                    <a class="dbtn sm" href="{{ route('admin.projects.create') }}"><x-icon name="plus" />Yangi loyiha</a>
                </x-card>
            </div>
            <p class="ad-k"><span><kbd>N</kbd> yangi yozuv</span><span><kbd>Ctrl</kbd><kbd>S</kbd> saqlash</span><span><kbd>Ctrl</kbd><kbd>↵</kbd> chop etish</span></p>
        </div>
    </div>
</x-layouts.admin>
