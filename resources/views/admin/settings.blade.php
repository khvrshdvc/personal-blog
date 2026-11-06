<x-layouts.admin title="Sozlamalar" section="settings" pane="detail">
    <div class="d-scroll" x-data="settingsForm(@js($config + ['activityUrl' => route('faollik')]))">
        <div class="adash">
            <header class="ad-h">
                <div><p class="eyebrow">Sozlamalar</p><h1>Sayt va profil. <span>Oʻzgarishlar avtomatik saqlanadi.</span></h1></div>
                <span class="sv" :class="{ on: saved }" aria-live="polite" x-text="saveText"></span>
            </header>
            <div class="sgrid">
                <x-card tag="section" class="sc sc-bio">
                    <x-card-title icon="person">Men haqimda</x-card-title>
                    <label class="fld"><span>Bio · boʻsh qator bilan xatboshilarga ajrating</span><textarea class="inp" rows="9" x-model="bio" x-on:input="schedule()" aria-label="Men haqimda matni"></textarea></label>
                </x-card>
                <x-card tag="section" class="sc sc-st">
                    <x-card-title icon="sliders">Holat va koʻrinish</x-card-title>
                    <div class="sw-r">
                        <span><b id="sopen-l">Ishga ochiq</b><small>Saytda yashil belgi bilan koʻrinadi</small></span>
                        <button class="switch" type="button" role="switch" :aria-checked="open" aria-labelledby="sopen-l" x-on:click="toggleOpen()"></button>
                    </div>
                    <div class="sw-r">
                        <span><b>Mavzu</b><small>Sayt va admin panel uchun</small></span>
                        <div class="segc" x-data="seg($store.theme.mode)" role="group" aria-label="Mavzu">
                            <span class="knob" aria-hidden="true"></span>
                            @foreach (['light' => 'Yorugʻ', 'dark' => 'Qorongʻi', 'auto' => 'Avto'] as $value => $label)
                                <button type="button" x-on:click="pick('{{ $value }}'); $store.theme.set('{{ $value }}')" :aria-pressed="cur === '{{ $value }}'" aria-pressed="false">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <label class="fld"><span>GitHub username</span><span class="ipx"><em>github.com/</em><input class="inp" x-model="github" x-on:change="saveGithub()" x-on:keydown.enter.prevent="saveGithub(); $el.blur()" placeholder="username" spellcheck="false" autocomplete="off" enterkeyhint="done"></span></label>
                    <p class="hint">Faollik xaritasi, Actions va loyihalar shu akkauntdan olinadi.</p>
                </x-card>
                <x-card tag="section" class="sc">
                    <x-card-title icon="sparkle">Hozir</x-card-title>
                    <div class="kv">
                        <template x-for="(item, i) in now" :key="i">
                            <div class="kv-r">
                                <input class="inp now-k" x-model="item.k" x-on:input="schedule()" placeholder="Sarlavha" :aria-label="(i + 1) + '-band sarlavhasi'">
                                <input class="inp now-v" x-model="item.v" x-on:input="schedule()" placeholder="Matn" :aria-label="(i + 1) + '-band matni'">
                            </div>
                        </template>
                    </div>
                    <p class="hint">“Men haqimda” sahifasida chiqadi. Boʻsh qatorlar koʻrinmaydi.</p>
                </x-card>
                <x-card tag="section" class="sc">
                    <x-card-title icon="book">Oʻrganyapman</x-card-title>
                    <div class="kv">
                        <template x-for="(item, i) in learn" :key="i">
                            <div class="kv-r lr">
                                <input class="inp ln-n" x-model="item.n" x-on:input="schedule()" placeholder="Mavzu" :aria-label="(i + 1) + '-mavzu nomi'">
                                <span class="ipx pcn"><input class="inp ln-p" type="number" min="0" max="100" inputmode="numeric" placeholder="0" x-model="item.p" x-on:input="schedule()" :aria-label="(i + 1) + '-mavzu foizi'"><em>%</em></span>
                                <i class="lbar" aria-hidden="true"><b :style="'width:' + bar(i) + '%'"></b></i>
                            </div>
                        </template>
                    </div>
                    <p class="hint">Bosh sahifadagi halqada navbat bilan koʻrinadi.</p>
                </x-card>
                <x-card tag="section" class="sc sc-tg">
                    <x-card-title icon="send">Telegram kanal</x-card-title>
                    <div class="two">
                        <label class="fld"><span>Username</span><span class="ipx"><em>t.me/</em><input class="inp" x-model="tg.u" x-on:input="schedule()" placeholder="username" spellcheck="false" autocomplete="off"></span></label>
                        <label class="fld"><span>Kanal nomi</span><input class="inp" x-model="tg.n" x-on:input="schedule()" placeholder="Kanal nomi"></label>
                    </div>
                    <div class="kv">
                        <template x-for="(post, i) in tg.posts" :key="i">
                            <div class="kv-r tp">
                                <input class="inp tgp-t" x-model="post.t" x-on:input="schedule()" :placeholder="(i + 1) + '-post matni'" :aria-label="(i + 1) + '-post matni'">
                                <input class="inp tgp-d" type="date" x-model="post.d" x-on:input="schedule()" :aria-label="(i + 1) + '-post sanasi'">
                            </div>
                        </template>
                    </div>
                    <p class="hint">Bosh sahifadagi kartada chiqadi. Soʻnggi postlar kanaldan avtomatik olinishi 7-bosqichda qoʻshiladi.</p>
                </x-card>
                <x-card tag="section" class="sc sc-dz">
                    <x-card-title icon="info">Hisob</x-card-title>
                    <div class="dz">
                        <span><b>Hisobdan chiqish</b><small>Admin sessiyasi yopiladi.</small></span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="dbtn" type="submit"><x-icon name="logout" />Chiqish</button>
                        </form>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.admin>
