<x-card tag="a" class="b-gh" :href="route('faollik')">
    <x-card-title icon="branch">GitHub<span class="r">{{ $github['streak'] ? $github['streak'].' kun seriya' : '' }}</span></x-card-title>
    <div class="gn"><span>{{ $github['total'] }}</span><small>hissa · soʻnggi yil</small></div>
    <div class="mhm" aria-hidden="true">
        @foreach ($github['cells'] as $level)
            @if ($level === null)
                <i class="x"></i>
            @else
                <i data-l="{{ $level }}"></i>
            @endif
        @endforeach
    </div>
</x-card>
