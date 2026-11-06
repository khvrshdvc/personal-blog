<x-layouts.admin title="Loyihalar" section="projects" pane="list">
    <x-slot:list><x-admin.projects-list :projects="$projects" :active="$active" /></x-slot:list>
    <div class="a-empty">
        <div class="c ae-c">
            <span class="ae-i"><x-icon name="folder" /></span>
            <h2>Loyihalar</h2>
            <p>Bu yerdagi loyihalar Dasturlash boʻlimida koʻrinadi. GitHub repo nomini yozsangiz, Actions natijalari avtomatik chiqadi.</p>
            <div class="d-acts">
                <a class="dbtn pri" href="{{ route('admin.projects.create') }}"><x-icon name="plus" />Yangi loyiha</a>
                <a class="dbtn" href="{{ route('loyihalar') }}"><x-icon name="eye" />Saytda koʻrish</a>
            </div>
        </div>
    </div>
</x-layouts.admin>
