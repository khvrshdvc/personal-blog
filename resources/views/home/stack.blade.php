<x-card class="b-stk">
    <x-card-title icon="cube">Stack</x-card-title>
    <div class="tags">@foreach ($tags as $i => $tag)<span @class(['on' => $i < 2])>{{ $tag }}</span>@endforeach</div>
</x-card>
