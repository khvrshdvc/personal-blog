@php
    $symbols = ['success' => '<span class="ok">✓</span>', 'failure' => '<span class="bad">✕</span>', 'running' => '<span class="ok">●</span>', 'skipped' => '<em>–</em>'];
    $runs = $github['runs'];
@endphp
<x-card tag="a" class="b-term" :href="route('faollik')" aria-label="GitHub Actions — soʻnggi ishlar">
    <div class="tbar2" aria-hidden="true"><i></i><i></i><i></i><span>~/utkirbek — zsh</span></div>
    <div class="tc" id="tc">
        <div><b>$ gh run list --limit 4</b></div>
        @forelse ($runs as $i => $run)
            <div style="animation-delay:{{ number_format(.15 + $i * .18, 2, '.', '') }}s">{!! $symbols[$run['status']] !!} <b>{{ $run['workflow'] }}</b> {{ $run['title'] }} <em>· {{ $run['repo'] }} · {{ \App\Support\Uz::ago($run['at']) }}</em></div>
        @empty
            <div style="animation-delay:.15s"><em>hali ishga tushmagan</em></div>
        @endforelse
        <div style="animation-delay:{{ number_format(.3 + count($runs) * .18, 2, '.', '') }}s"><b>$</b><span class="cur"></span></div>
    </div>
</x-card>
