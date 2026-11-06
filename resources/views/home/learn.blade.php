@if (count($learn))
    <x-card class="b-lrn" x-data="rotator({{ count($learn) }})">
        @foreach ($learn as $i => $item)
            <div class="lp{{ $i === 0 ? ' on' : '' }}" :class="{ on: cur === {{ $i }}, was: prev === {{ $i }} && cur !== {{ $i }} }">
                <span class="ring">
                    <svg viewBox="0 0 62 62" aria-hidden="true"><circle class="tr" cx="31" cy="31" r="26"/><circle class="pr" cx="31" cy="31" r="26" stroke-dasharray="163.4" stroke-dashoffset="{{ number_format(163.4 * (1 - $item['percent'] / 100), 1, '.', '') }}"/></svg>
                    <b>{{ $item['percent'] }}%</b>
                </span>
                <span><span class="lt1">Oʻrganyapman</span><span class="lt2">{{ $item['name'] }}</span></span>
            </div>
        @endforeach
        @if (count($learn) > 1)
            <span class="ldots" aria-hidden="true">@foreach ($learn as $i => $item)<i :class="{ on: cur === {{ $i }} }"></i>@endforeach</span>
        @endif
    </x-card>
@endif
