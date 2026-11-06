<x-layouts.admin title="Yozuvlar" section="posts" pane="list">
    <x-slot:list><x-admin.posts-list :posts="$posts" :active="$active" /></x-slot:list>
    <div class="a-empty">
        <div class="c ae-c">
            <span class="ae-i"><x-icon name="compose" /></span>
            <h2>Bugun nima haqida yozasiz?</h2>
            <p>Chapdan yozuvni tanlang yoki yangisini boshlang. Yozganlaringiz avtomatik saqlanadi.</p>
            <div class="d-acts">
                <a class="dbtn pri" href="{{ route('admin.posts.create', 'fikr') }}"><x-icon name="plus" />Yangi fikr</a>
                <a class="dbtn" href="{{ route('admin.posts.create', 'dev') }}"><x-icon name="code" />Dasturlash posti</a>
            </div>
            <p class="ae-k"><span><kbd>N</kbd> yangi yozuv</span><span><kbd>Ctrl</kbd><kbd>S</kbd> saqlash</span><span><kbd>Ctrl</kbd><kbd>↵</kbd> chop etish</span></p>
        </div>
    </div>
</x-layouts.admin>
