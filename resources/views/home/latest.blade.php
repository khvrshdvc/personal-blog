@if ($latest)
    <x-card tag="a" class="b-post" :href="url('/post/'.$latest->slug)">
        <span class="qm" aria-hidden="true">“</span>
        <x-card-title icon="bookOpen">Soʻnggi fikr · {{ \App\Support\Uz::long($latest->published_at) }}</x-card-title>
        <h2>{{ $latest->title }}</h2>
        @if ($latest->excerpt)<p>{{ $latest->excerpt }}</p>@endif
        <span class="cf">
            <span>{{ $latest->tag }} · {{ $latest->readMinutes() }} daqiqa oʻqish{{ $thoughtCount > 1 ? ' · yana '.($thoughtCount - 1).' ta fikr' : '' }}</span>
            <span class="arr"><x-icon name="arrowUR" /></span>
        </span>
    </x-card>
@else
    <x-card class="b-post">
        <x-card-title icon="bookOpen">Fikrlar</x-card-title>
        <h2>Hali fikr yozilmagan</h2>
    </x-card>
@endif
