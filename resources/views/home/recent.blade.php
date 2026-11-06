<x-card class="b-rec">
    <x-card-title icon="list">Fikrlar<a class="r" href="{{ route('fikrlar') }}">Barchasi →</a></x-card-title>
    <div class="rl">
        @foreach ($recent as $post)
            <a href="{{ url('/post/'.$post->slug) }}"><time datetime="{{ $post->published_at?->toDateString() }}">{{ \App\Support\Uz::short($post->published_at) }}</time><span>{{ $post->title }}</span></a>
        @endforeach
    </div>
</x-card>
