@props(['posts', 'active' => null])
@php
    $items = $posts->map(fn ($p) => ['id' => $p->id, 'cat' => $p->category->value, 'title' => mb_strtolower($p->view()['title'])])->values();
@endphp
<section class="a-list" id="alist" aria-label="Roʻyxat" data-section="posts" data-edit="{{ url('/admin/posts') }}" x-data="alist(@js($items))" :class="{ editing }" x-on:post-saved.window="upsert($event.detail)">
    <header class="al-h">
        <div class="al-top">
            <h1>Yozuvlar <span data-count="posts">{{ $posts->count() }}</span></h1>
            <div class="al-act">
                <button class="al-eb" :class="{ done: editing }" type="button" x-on:click="toggleEdit()" x-text="editing ? 'Tayyor' : 'Tahrirlash'">Tahrirlash</button>
                <a class="al-nb" href="{{ route('admin.posts.create') }}" aria-label="Yangi yozuv" title="Yangi yozuv"><x-icon name="plus" /></a>
            </div>
        </div>
        <label class="fs"><x-icon name="search" /><span class="sr">Yozuvlarni qidirish</span><input id="aq" type="search" placeholder="Qidirish" autocomplete="off" x-model="q"></label>
        <div id="afilter">
            <div class="segc" x-data="seg('all')" role="group" aria-label="Boʻlim boʻyicha">
                <span class="knob" aria-hidden="true"></span>
                @foreach (['all' => 'Barchasi', 'fikr' => 'Fikrlar', 'dev' => 'Dasturlash'] as $value => $label)
                    <button type="button" x-on:click="pick('{{ $value }}'); filter = '{{ $value }}'" :aria-pressed="cur === '{{ $value }}'" aria-pressed="{{ $value === 'all' ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
    </header>
    <ul class="a-items" id="aitems" x-on:click="onClick($event)">
        @foreach ($posts as $post)
            @php $view = $post->view(); @endphp
            <li class="ali" data-id="{{ $post->id }}" data-url="{{ route('admin.posts.destroy', $post) }}" @if ($post->id === $active) data-active="1" @endif x-show="vis({{ $post->id }})">
                <button class="minus" type="button" data-arm aria-label="{{ $view['title'] ?: 'Sarlavhasiz' }} — oʻchirish"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M7.5 12h9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg></button>
                <a @class(['arow', 'on' => $post->id === $active]) href="{{ route('admin.posts.edit', $post) }}" @if ($post->id === $active) aria-current="true" @endif>
                    <span class="ar-top"><span class="sdot {{ $post->statusKey() }}" aria-hidden="true"></span><b class="rt">{{ $view['title'] ?: 'Sarlavhasiz' }}</b><span class="rd">{{ \App\Support\Uz::short($view['published_at']) }}</span></span>
                    <span class="rs2">{{ $view['excerpt'] ?: (\App\Support\Markdown::excerpt($view['body']) ?: 'Matn yoʻq') }}</span>
                    <span class="ar-st">{{ \App\Enums\PostCategory::from($view['category'])->one() }} · {{ $view['tag'] }} · {{ $post->statusLabel() }}</span>
                </a>
                <button class="delbtn" type="button" data-del>Oʻchirish</button>
            </li>
        @endforeach
        <li class="a-empty-l" x-show="empty" x-cloak>Yozuv topilmadi</li>
    </ul>
</section>
