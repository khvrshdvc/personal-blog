<x-card tag="a" class="b-proj" :href="route('loyihalar')">
    <x-card-title icon="folder">Loyihalar<span class="r">{{ $projectCount }}</span></x-card-title>
    <div class="pl">
        @forelse ($projects as $project)
            <span class="pi">
                <span class="pm" aria-hidden="true">{{ $project['initials'] }}</span>
                <span class="pn">{{ $project['name'] }}</span>
                <span @class(['pd', $project['status']['status'] ?? null]) @if ($project['status']) title="{{ $project['status']['label'] }}" @endif></span>
            </span>
        @empty
            <span class="pn">Hali loyiha yoʻq</span>
        @endforelse
    </div>
</x-card>
