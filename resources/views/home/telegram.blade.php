<x-card tag="section" class="b-tg" aria-label="Telegram kanal">
    <div class="tg-l">
        <x-card-title icon="send">Telegram kanal</x-card-title>
        <div class="tg-ch">
            <span class="tg-av" aria-hidden="true"><x-icon name="send" /><i></i></span>
            <b class="tg-n">{{ $telegram['name'] ?: 'Telegram kanal' }}</b>
            <span class="tg-u">{{ $telegram['username'] ? 't.me/'.$telegram['username'] : 'kanal · ingliz tilida' }}</span>
        </div>
        @if ($telegram['username'])
            <a class="tg-btn" href="https://t.me/{{ $telegram['username'] }}" target="_blank" rel="noopener"><x-icon name="plus" />Obuna boʻlish</a>
        @else
            <a class="tg-btn" href="#" x-on:click.prevent="notify('Telegram kanal havolasi hali qoʻshilmagan')"><x-icon name="plus" />Obuna boʻlish</a>
        @endif
    </div>
    <div class="tg-chat" aria-label="Soʻnggi postlar">
        <span class="tg-day">Soʻnggi postlar</span>
        @forelse ($telegram['posts'] as $post)
            <div class="tg-b"><p>{{ $post['t'] }}</p><small>{{ \App\Support\Uz::short($post['d'] ?? null) }}</small></div>
        @empty
            <div class="tg-b"><p>Hali post qoʻshilmagan.</p></div>
        @endforelse
    </div>
</x-card>
