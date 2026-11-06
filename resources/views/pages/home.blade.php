<x-layouts.site title="Utkirbek" route="home">
    <div class="wrapx">
        <section class="hero">
            <h1>Backend dasturchi. Ustoz. <span>Fikrlarini yozib boradigan odam.</span></h1>
            <p>PHP va Laravelʼni mustaqil oʻrganyapman, bolalarga dars beraman va bu yerda fikrlarimni yozaman.</p>
        </section>
        <div class="bento">
            @include('home.latest')
            @include('home.location')
            @include('home.github')
            @include('home.learn')
            @include('home.teach')
            @include('home.terminal')
            @include('home.projects')
            @include('home.stack')
            @include('home.recent')
            @include('home.telegram')
        </div>
    </div>
</x-layouts.site>
