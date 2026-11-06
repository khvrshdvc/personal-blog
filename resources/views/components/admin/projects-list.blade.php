@props(['projects', 'active' => null])
@php $items = $projects->map(fn ($p) => ['id' => $p->id, 'cat' => '', 'title' => mb_strtolower($p->name)])->values(); @endphp
<section class="a-list" id="alist" aria-label="Roʻyxat" data-section="projects" data-edit="{{ url('/admin/projects') }}" x-data="alist(@js($items))" :class="{ editing }">
    <header class="al-h">
        <div class="al-top">
            <h1>Loyihalar <span data-count="projects">{{ $projects->count() }}</span></h1>
            <div class="al-act">
                <button class="al-eb" :class="{ done: editing }" type="button" x-on:click="toggleEdit()" x-text="editing ? 'Tayyor' : 'Tahrirlash'">Tahrirlash</button>
                <a class="al-nb" href="{{ route('admin.projects.create') }}" aria-label="Yangi loyiha" title="Yangi loyiha"><x-icon name="plus" /></a>
            </div>
        </div>
    </header>
    <ul class="a-items" id="aitems" x-on:click="onClick($event)">
        @forelse ($projects as $project)
            <li class="ali" data-id="{{ $project->id }}" data-url="{{ route('admin.projects.destroy', $project) }}" @if ($project->id === $active) data-active="1" @endif x-show="vis({{ $project->id }})">
                <button class="minus" type="button" data-arm aria-label="{{ $project->name }} — oʻchirish"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M7.5 12h9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg></button>
                <a @class(['arow', 'pj', 'on' => $project->id === $active]) href="{{ route('admin.projects.edit', $project) }}" @if ($project->id === $active) aria-current="true" @endif>
                    <span class="app-m" aria-hidden="true">{{ \App\Support\Uz::initials($project->name) }}</span>
                    <span class="pj-b"><b class="rt">{{ $project->name }}</b><span class="pj-r">{{ $project->repo ?: 'repo koʻrsatilmagan' }}</span></span>
                    <span class="pj-c"><x-icon name="chevR" /></span>
                </a>
                <button class="delbtn" type="button" data-del>Oʻchirish</button>
            </li>
        @empty
            <li class="a-empty-l">Hali loyiha yoʻq</li>
        @endforelse
    </ul>
</section>
