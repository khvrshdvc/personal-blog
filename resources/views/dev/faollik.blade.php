<div x-data="heatmap">
    @unless ($real)
        <ul class="list icons" style="margin-bottom:12px">
            <li>
                <a class="row" href="{{ url('/admin/settings') }}">
                    <span class="ricon" style="--c:var(--tint)"><x-icon name="info" /></span>
                    <span class="rbody"><span class="rt">Namuna maʼlumot</span><span class="rs2">Haqiqiy faollik uchun admin panelda GitHub username kiriting.</span></span>
                    <span class="chev"><x-icon name="chevR" /></span>
                </a>
            </li>
        </ul>
    @endunless
    <div class="sum">
        <div><b>{{ $total }}</b><span>hissa</span></div>
        <div><b>{{ $activeDays }}</b><span>faol kun</span></div>
        <div><b>{{ $streak }}</b><span>kunlik seriya</span></div>
    </div>
    <div class="c hmcard">
        <div class="hm-top"><b>Faollik</b><span>{{ $range }}</span></div>
        <div class="hm-wrap" x-ref="wrap">
            <div class="hm">
                <div class="hm-months" aria-hidden="true">@foreach ($months as $month)<span style="grid-column:{{ $month['column'] }}/span 3">{{ $month['label'] }}</span>@endforeach</div>
                <div class="hm-body">
                    <div class="hm-days" aria-hidden="true"><span>Du</span><span></span><span>Ch</span><span></span><span>Ju</span><span></span><span></span></div>
                    <div class="hm-grid" role="img" aria-label="GitHub faollik xaritasi" x-on:mouseover="show($event)" x-on:mouseleave="hide()">
                        @foreach ($cells as $cell)
                            @if ($cell === null)
                                <i class="x"></i>
                            @else
                                <i data-l="{{ $cell['level'] }}" data-c="{{ $cell['c'] }}" data-d="{{ $cell['d'] }}"></i>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="hm-foot">
                    <span>Kataklar ustiga olib boring</span>
                    <div class="legend"><span>Kam</span><i data-l="0"></i><i data-l="1"></i><i data-l="2"></i><i data-l="3"></i><i data-l="4"></i><span>Koʻp</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="group">
        <h3 class="group-h">GitHub Actions</h3>
        <ul class="list" id="runs" style="border-radius:24px">
            @forelse ($runs as $run)
                <li>
                    @if (! empty($run['url']))
                        <a class="row" href="{{ $run['url'] }}" target="_blank" rel="noopener">
                    @else
                        <a class="row" href="#" x-on:click.prevent="notify('Bu namuna — GitHub username kiriting', { label: 'Sozlamalar', href: @js(url('/admin/settings')) })">
                    @endif
                        <span class="rstat {{ $run['status'] }}" title="{{ \App\Support\RunStatus::label($run['status']) }}"><x-run-icon :status="$run['status']" /><span class="sr">{{ \App\Support\RunStatus::label($run['status']) }}</span></span>
                        <span class="rbody">
                            <span class="rt">{{ $run['title'] }}</span>
                            <span class="rsub">{{ $run['workflow'] }} · {{ $run['repo'] }}{{ $run['branch'] ? ' · '.$run['branch'] : '' }}{{ $run['num'] ? ' · #'.$run['num'] : '' }}</span>
                        </span>
                        <span class="rright"><span>{{ \App\Support\Uz::ago($run['at']) }}</span><span class="dur">{{ $run['status'] === 'running' ? 'ishlamoqda' : \App\Support\Uz::duration($run['dur']) }}</span></span>
                    </a>
                </li>
            @empty
                <li class="empty-row">Loyihalaringizda hali GitHub Actions ishga tushmagan</li>
            @endforelse
        </ul>
    </div>
    <div class="tip" x-show="tip.show" x-cloak :style="{ left: tip.x + 'px', top: tip.y + 'px' }" x-text="tip.text" x-on:scroll.window.passive="hide()"></div>
</div>
