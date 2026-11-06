<x-layouts.site title="Fikrlar — Utkirbek" route="fikrlar">
    <div class="wrapx" x-data="fikrlar(@js($posts->map(fn ($p) => ['slug' => $p->slug, 'tag' => $p->tag])->values()), @js(route('fikrlar.search')))">
        <header class="ph"><p class="eyebrow">Fikrlar</p><h1>Oʻrganish, ustozlik, til va hayot haqida. <span>{{ $posts->count() }} ta yozuv.</span></h1></header>
        <div class="tools">
            <div class="ftabs" role="group" aria-label="Mavzular">
                <button class="scope" type="button" x-on:click="tag = 'all'" :aria-pressed="tag === 'all'" aria-pressed="true">Barchasi</button>
                @foreach ($tags as $tag)
                    <button class="scope" type="button" x-on:click="tag = @js($tag)" :aria-pressed="tag === @js($tag)" aria-pressed="false">{{ $tag }}</button>
                @endforeach
            </div>
            <label class="fs">
                <x-icon name="search" />
                <span class="sr">Fikrlardan qidirish</span>
                <input id="bq" type="search" placeholder="Qidirish" autocomplete="off" enterkeyhint="search" x-model="q" x-on:input.debounce.200ms="query()">
                <button class="fx" type="button" aria-label="Qidiruvni tozalash" x-show="q" x-cloak x-on:click="clear()"><x-icon name="x" /></button>
            </label>
        </div>
        <div class="pgrid" id="bres">
            @forelse ($posts as $post)
                @if ($loop->first)
                    <x-post-card :post="$post" :big="true" :filter="true" x-bind:class="{ big: featured }" />
                @else
                    <x-post-card :post="$post" :filter="true" />
                @endif
            @empty
                <p class="empty">Hali fikr yozilmagan.</p>
            @endforelse
            @if ($posts->isNotEmpty())
                <p class="empty" x-show="visibleCount === 0" x-cloak>Hech narsa topilmadi. Boshqa soʻz bilan qidirib koʻring.</p>
            @endif
        </div>
    </div>
</x-layouts.site>
