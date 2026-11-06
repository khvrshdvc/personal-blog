@php
    $initial = [
        'id' => $project?->id,
        'name' => (string) old('name', $project?->name ?? ''),
        'repo' => (string) old('repo', $project?->repo ?? ''),
        'description' => (string) old('description', $project?->description ?? ''),
        'stack' => (string) old('stack', implode(', ', $project?->stack ?? [])),
        'demo' => (string) old('demo_url', $project?->demo_url ?? ''),
        'deleteUrl' => $project ? route('admin.projects.destroy', $project) : null,
    ];
@endphp
<x-layouts.admin :title="$project ? $project->name : 'Yangi loyiha'" section="projects" pane="detail">
    <x-slot:list><x-admin.projects-list :projects="$projects" :active="$active" /></x-slot:list>
    <div style="display:contents" x-data="projectForm(@js($initial))">
        <header class="d-nav">
            <div class="d-l"><a class="gbtn glass cap only-sm" href="{{ route('admin.projects.index') }}"><x-icon name="chevL" /><span>Loyihalar</span></a><b class="d-title">{{ $project ? $project->name : 'Yangi loyiha' }}</b></div>
            <div class="d-c"></div>
            <div class="d-r">
                @if ($project)<button class="capbtn plain sm" type="button" x-on:click="remove()">Oʻchirish</button>@endif
                <button class="capbtn filled sm" type="submit" form="pform">Saqlash</button>
            </div>
        </header>
        <div class="d-scroll">
            <div class="pf">
                <form class="c pf-f" id="pform" method="POST" action="{{ $project ? route('admin.projects.update', $project) : route('admin.projects.store') }}" novalidate>
                    @csrf
                    @if ($project)@method('PUT')@endif
                    <x-card-title icon="folder">{{ $project ? 'Loyiha maʼlumotlari' : 'Yangi loyiha' }}</x-card-title>
                    <label class="fld"><span>Nomi</span><input class="inp" name="name" x-model="name" placeholder="Masalan, CRM tizimi" autocomplete="off" @if (! $project) autofocus @endif></label>
                    <p class="err" id="pnerr">{{ $errors->first('name') }}</p>
                    <label class="fld"><span>GitHub repozitoriy</span><span class="ipx"><em>github.com/</em><input class="inp" name="repo" x-model="repo" placeholder="repo-nomi yoki owner/repo" spellcheck="false" autocomplete="off"></span></label>
                    <p class="hint">Actions natijalari, til va yangilanish vaqti shu repodan olinadi.</p>
                    <label class="fld"><span>Tavsif</span><textarea class="inp" name="description" x-model="description" rows="3" maxlength="220" placeholder="Loyiha nima qiladi — bir-ikki gap"></textarea></label>
                    @error('description')<p class="err">{{ $message }}</p>@enderror
                    <div class="two">
                        <label class="fld"><span>Stack · vergul bilan</span><input class="inp" name="stack" x-model="stack" placeholder="Laravel, MySQL, Redis" autocomplete="off"></label>
                        <label class="fld"><span>Demo havola · ixtiyoriy</span><input class="inp" name="demo_url" x-model="demo" type="url" placeholder="https://" autocomplete="off"></label>
                    </div>
                </form>
                <aside class="pf-p">
                    <p class="pf-l"><x-icon name="eye" /> Saytda shunday koʻrinadi</p>
                    <div id="pprev">
                        <div class="c app">
                            <div class="app-h">
                                <span class="app-m" aria-hidden="true" x-text="initials"></span>
                                <span class="app-n" x-text="name.trim() || 'Loyiha nomi'"></span>
                                <span class="app-ls" x-show="repo.trim() || demo.trim()">
                                    <a class="app-l" href="#" x-show="repo.trim()" x-on:click.prevent>Kod <x-icon name="arrowUR" /></a>
                                    <a class="app-l" href="#" x-show="demo.trim()" x-on:click.prevent>Demo <x-icon name="arrowUR" /></a>
                                </span>
                            </div>
                            <p class="app-d" x-text="description.trim() || 'Qisqa tavsif'"></p>
                            <span class="app-s" x-show="stackList.length" x-text="stackList.join(' · ')"></span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-layouts.admin>
