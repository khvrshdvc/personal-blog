<x-card class="b-tch">
    <div>
        <div class="tv">{{ $teach['v'] }}</div>
        <div class="tt">{{ $teach['t'] }}</div>
    </div>
    <div class="tdots" aria-hidden="true">@for ($i = 0; $i < 12; $i++)<i></i>@endfor</div>
</x-card>
