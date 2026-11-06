<nav class="tmini" :class="{ show: mini }" id="tmini" aria-label="Boʻlimlar (ixcham)">
    <a class="tmi-h" href="{{ route('home') }}" aria-label="Bosh sahifa"><span class="av">U</span></a>
    @foreach ($navItems as $item)
        @php $active = $item['active']; @endphp
        <a @class(['tmi', 'on' => $active]) href="{{ $item['href'] }}" aria-label="{{ $item['label'] }}" @if ($active) aria-current="page" @endif>
            <x-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span>
        </a>
    @endforeach
    <span class="tmi-sep" aria-hidden="true"></span>
    <button class="tmi ic" type="button" @click="openPalette()" aria-label="Qidirish"><x-icon name="search" /></button>
    <button class="tmi ic th" type="button" @click="toggleTheme()" aria-label="Mavzuni almashtirish"><x-icon name="moon" class="t-moon" /><x-icon name="sun" class="t-sun" /></button>
</nav>
