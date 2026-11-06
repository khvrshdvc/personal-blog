@php
    $todo = fn (string $name) => "notify('".$name." havolasi hali qoʻshilmagan')";
@endphp
<x-layouts.site title="Men haqimda — Utkirbek" route="men">
    <div class="wrapx">
        <div class="mgrid">
            <x-card tag="section" class="m-pro">
                <div class="m-top">
                    <span class="av" aria-hidden="true">U</span>
                    <span @class(['status', 'off' => ! $isOpen])><i></i><span>{{ $isOpen ? 'Ishga ochiq' : 'Hozircha band' }}</span></span>
                </div>
                <h1>Utkirbek</h1>
                <p>Backend dasturchi · Toshkent, Oʻzbekiston</p>
                <div class="acts">
                    <a class="act" href="#" x-on:click.prevent="{{ $todo('Telegram') }}"><x-icon name="send" />Telegram</a>
                    @if ($telegram)
                        <a class="act" href="https://t.me/{{ $telegram }}" target="_blank" rel="noopener"><x-icon name="mega" />Kanal</a>
                    @else
                        <a class="act" href="#" x-on:click.prevent="{{ $todo('Telegram kanal') }}"><x-icon name="mega" />Kanal</a>
                    @endif
                    @if ($github)
                        <a class="act" href="https://github.com/{{ $github }}" target="_blank" rel="noopener"><x-icon name="branch" />GitHub</a>
                    @else
                        <a class="act" href="#" x-on:click.prevent="notify('Bu namuna — GitHub username kiriting', { label: 'Sozlamalar', href: @js(url('/admin/settings')) })"><x-icon name="branch" />GitHub</a>
                    @endif
                    <a class="act" href="#" x-on:click.prevent="{{ $todo('Email') }}"><x-icon name="mail" />Email</a>
                </div>
            </x-card>
            @if (count($now))
                <x-card tag="section" class="m-now">
                    <x-card-title icon="sparkle">Hozir</x-card-title>
                    <div class="nl">@foreach ($now as $item)<div><small>{{ $item['k'] }}</small><b>{{ $item['v'] }}</b></div>@endforeach</div>
                </x-card>
            @endif
            @if (count($paragraphs))
                <x-card tag="section" class="m-bio">
                    <x-card-title icon="person">Men haqimda</x-card-title>
                    @foreach ($paragraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                </x-card>
            @endif
            <div class="m-side">
                <x-card tag="a" class="mlk" :href="route('loyihalar')"><span class="ricon"><x-icon name="folder" /></span><b>Loyihalarim</b><small>{{ $projectCount }}</small></x-card>
                <x-card tag="a" class="mlk" :href="route('fikrlar')"><span class="ricon"><x-icon name="bookOpen" /></span><b>Fikrlarim</b><small>{{ $thoughtCount }}</small></x-card>
                <x-card tag="a" class="mlk" href="#" x-on:click.prevent="{{ $todo('CV') }}"><span class="ricon"><x-icon name="download" /></span><b>CV yuklab olish</b><small>PDF</small></x-card>
                <x-card class="thm">
                    <b>Mavzu</b>
                    <div class="segc" x-data="seg($store.theme.mode)" role="group" aria-label="Mavzu">
                        <span class="knob" aria-hidden="true"></span>
                        @foreach (['light' => 'Yorugʻ', 'dark' => 'Qorongʻi', 'auto' => 'Avto'] as $value => $label)
                            <button type="button" x-on:click="pick('{{ $value }}'); $store.theme.set('{{ $value }}')" :aria-pressed="cur === '{{ $value }}'" aria-pressed="false">{{ $label }}</button>
                        @endforeach
                    </div>
                </x-card>
            </div>
        </div>
        <p class="adm-l"><a href="{{ route('admin.dashboard') }}">Admin panel</a></p>
    </div>
</x-layouts.site>
