<nav class="tnav" id="tnav" aria-label="Boʻlimlar">
    <x-card class="tn-me">
        <a class="tn-h" href="{{ route('home') }}" aria-label="Utkirbek — bosh sahifa">
            <span class="av">U</span>
            <span>
                <b>Utkirbek</b>
                <small><span @class(['tn-st', 'off' => ! $isOpen])><i></i><span>{{ $isOpen ? 'Ishga ochiq' : 'Hozircha band' }}</span></span> · Toshkent</small>
            </span>
        </a>
        <button class="tn-ib" type="button" x-on:click="openPalette()" aria-label="Qidirish"><x-icon name="search" /></button>
        <button class="tn-ib" type="button" x-on:click="toggleTheme()" aria-label="Mavzuni almashtirish"><x-icon name="moon" class="t-moon" /><x-icon name="sun" class="t-sun" /></button>
    </x-card>
    @foreach ($navItems as $item)
        @php $active = $item['active']; @endphp
        <x-card tag="a" :href="$item['href']" class="tn{{ $active ? ' on' : '' }}" :aria-current="$active ? 'page' : null">
            <span class="tn-t"><x-icon :name="$item['icon']" /><span>{{ $item['count'] }}</span></span>
            <b>{{ $item['label'] }}</b>
        </x-card>
    @endforeach
    <x-card tag="button" class="tn-x" type="button" x-on:click="openPalette()" aria-label="Qidirish (Ctrl+K)"><x-icon name="search" /><small>⌘K</small></x-card>
    <x-card tag="button" class="tn-x" type="button" x-on:click="toggleTheme()" aria-label="Mavzuni almashtirish"><x-icon name="moon" class="t-moon" /><x-icon name="sun" class="t-sun" /><small>Mavzu</small></x-card>
</nav>
