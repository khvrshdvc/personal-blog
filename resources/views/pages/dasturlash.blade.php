@php
    $tabs = [
        'yozuvlar' => ['Yozuvlar', route('dasturlash')],
        'loyihalar' => ['Loyihalar', route('loyihalar')],
        'faollik' => ['Faollik', route('faollik')],
    ];
@endphp
<x-layouts.site title="Dasturlash — Utkirbek" route="dasturlash">
    <div class="wrapx">
        <header class="ph"><p class="eyebrow">Dasturlash</p><h1>Backend yoʻlim. <span>PHP, Laravel va SQL.</span></h1></header>
        <div class="seg-wrap">
            <div class="segc" x-data="seg" role="group" aria-label="Dasturlash boʻlimi">
                <span class="knob" aria-hidden="true"></span>
                @foreach ($tabs as $key => [$label, $href])
                    <a href="{{ $href }}" @if ($tab === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div id="dbody">
            @include('dev.'.$tab)
        </div>
    </div>
</x-layouts.site>
