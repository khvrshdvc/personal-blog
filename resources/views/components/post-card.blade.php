@props(['post', 'big' => false, 'filter' => false])
@php $dev = $post->category === \App\Enums\PostCategory::Dev; @endphp
<a {{ $attributes->class(['c', 'pc', 'big' => $big, 'dev' => $dev]) }} @if ($filter) x-show="shown(@js($post->slug), @js($post->tag))" @endif href="{{ route('post.show', $post->slug) }}">
    @if ($big)<span class="qm" x-show="featured" aria-hidden="true">“</span>@endif
    <span class="ck">{{ $post->tag }} · {{ \App\Support\Uz::long($post->published_at) }}<span class="r">{{ $post->readMinutes() }} daqiqa</span></span>
    <h3>{{ $post->title }}</h3>
    @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
</a>
