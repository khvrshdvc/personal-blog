@php
    $demoNotice = ['text' => 'Bu namuna — GitHub username kiriting', 'action' => ['label' => 'Sozlamalar', 'href' => url('/admin/settings')]];
@endphp
<div class="apps-grid" id="apps">
    @forelse ($projects as $project)
        <div class="c app">
            <div class="app-h">
                <span class="app-m" aria-hidden="true">{{ $project['initials'] }}</span>
                <span class="app-n">{{ $project['name'] }}</span>
                @if ($project['has_repo'] || $project['demo_url'])
                    <span class="app-ls">
                        @if ($project['has_repo'])
                            @if ($project['code_url'])
                                <a class="app-l" href="{{ $project['code_url'] }}" target="_blank" rel="noopener" aria-label="{{ $project['name'] }} — kod">Kod <x-icon name="arrowUR" /></a>
                            @else
                                <a class="app-l" href="#" x-on:click.prevent="notify(@js($demoNotice['text']), @js($demoNotice['action']))" aria-label="{{ $project['name'] }} — kod">Kod <x-icon name="arrowUR" /></a>
                            @endif
                        @endif
                        @if ($project['demo_url'])
                            <a class="app-l" href="{{ $project['demo_url'] }}" target="_blank" rel="noopener" aria-label="{{ $project['name'] }} — demo">Demo <x-icon name="arrowUR" /></a>
                        @endif
                    </span>
                @endif
            </div>
            @if ($project['description'])<p class="app-d">{{ $project['description'] }}</p>@endif
            @if ($project['run'] || $project['lang'] || $project['stars'] || $project['pushed'])
                <span class="a-meta">
                    @if ($project['run'])<span class="ci {{ $project['run']['status'] }}"><x-run-icon :status="$project['run']['status']" />{{ \App\Support\RunStatus::ci($project['run']['status']) }}</span>@endif
                    @if ($project['lang'])<span class="lang" style="--lc:{{ config('site.lang_colors.'.$project['lang'], '#8e8e93') }}">{{ $project['lang'] }}</span>@endif
                    @if ($project['stars'])<span><x-icon name="star" />{{ $project['stars'] }}</span>@endif
                    @if ($project['pushed'])<span>{{ \App\Support\Uz::ago($project['pushed']) }}</span>@endif
                </span>
            @endif
            @if (count($project['stack']))<span class="app-s">{{ implode(' · ', $project['stack']) }}</span>@endif
        </div>
    @empty
        <p class="empty">Hali loyiha qoʻshilmagan.</p>
    @endforelse
</div>
<div class="stk-grid">
    @foreach ($stack as $group)
        <x-card>
            <x-card-title :icon="$group['icon']">{{ $group['name'] }}</x-card-title>
            <div class="tags">@foreach ($group['items'] as $item)<span @class(['on' => $item['on']])>{{ $item['name'] }}</span>@endforeach</div>
        </x-card>
    @endforeach
</div>
