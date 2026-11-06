<div class="pgrid">
    @forelse ($posts as $post)
        <x-post-card :post="$post" />
    @empty
        <p class="empty">Hali yozuv yoʻq.</p>
    @endforelse
</div>
