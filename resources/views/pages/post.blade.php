@php
    $isDev = $post->category === \App\Enums\PostCategory::Dev;
    $home = $isDev ? route('dasturlash') : route('fikrlar');
@endphp
<x-layouts.site :title="$post->title.' — Utkirbek'" :description="$post->excerpt ?: 'Utkirbek — '.$post->title" route="post">
    <div class="progress" id="progress"></div>
    <article @class(['read', 'dev' => $isDev, 'fikr' => ! $isDev])>
        <div class="r-top">
            <a class="tlink" href="{{ $home }}"><x-icon name="chevL" /> {{ $post->category->label() }}</a>
            <button class="ibtn" type="button" x-on:click="share()" aria-label="Ulashish" title="Ulashish"><x-icon name="share" /></button>
        </div>
        @if ($draft)<p class="draft-note">Qoralama — buni faqat siz koʻryapsiz</p>@endif
        <div class="r-meta">
            <span class="sdot" style="--c:{{ $post->tagColor() }}"></span><span>{{ $post->tag }}</span><span>·</span><span>{{ \App\Support\Uz::long($post->published_at) }}</span><span>·</span><span>{{ $post->readMinutes() }} daqiqa oʻqish</span>
        </div>
        <h1 class="r-title">{{ $post->title }}</h1>
        @if ($post->excerpt)<p class="r-lead">{{ $post->excerpt }}</p>@endif
        @if ($post->coverUrl())<div class="r-cover"><div class="cover"><img src="{{ $post->coverUrl() }}" alt=""></div></div>@endif
        <div class="r-body">
            @if ($isDev && count($headings) >= 2)
                <div id="toc">
                    <details class="disc">
                        <summary><span>Mundarija</span><span class="rd">{{ count($headings) }} boʻlim</span><span class="chev"><x-icon name="chevR" /></span></summary>
                        <ol>@foreach ($headings as $heading)<li><a href="#{{ $heading['id'] }}" @class(['lv3' => $heading['level'] === 3])>{{ $heading['text'] }}</a></li>@endforeach</ol>
                    </details>
                </div>
            @endif
            <div class="prose" id="prose">{!! $html !!}</div>
        </div>
        @auth
            <div class="r-actions"><a class="capbtn gray sm" href="{{ url('/admin/posts/'.$post->id) }}"><x-icon name="pen" /> Tahrirlash</a></div>
        @endauth
        <div class="r-end">
            <x-card tag="a" class="r-au" :href="route('men')"><span class="av">U</span><span class="r-au-t"><b>Utkirbek</b><small>Backend dasturchi · Toshkent</small></span><span class="arr sm"><x-icon name="arrowUR" /></span></x-card>
            @foreach ([['Keyingi', $newer], ['Oldingi', $older]] as [$label, $item])
                @if ($item)
                    <a @class(['c', 'r-nx', 'dev' => $item->category === \App\Enums\PostCategory::Dev]) href="{{ route('post.show', $item->slug) }}"><span class="ck">{{ $label }}<span class="r">{{ \App\Support\Uz::short($item->published_at) }}</span></span><h4>{{ $item->title }}</h4></a>
                @endif
            @endforeach
        </div>
    </article>
</x-layouts.site>
