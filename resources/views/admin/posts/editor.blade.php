<x-layouts.admin title="Yozuv" section="posts" pane="detail">
    <x-slot:list><x-admin.posts-list :posts="$posts" :active="$active" /></x-slot:list>
    <div style="display:contents" x-data="editor(@js($config))">
        <header class="d-nav">
            <div class="d-l">
                <a class="gbtn glass cap only-sm" href="{{ route('admin.posts.index') }}"><x-icon name="chevL" /><span>Yozuvlar</span></a>
                <span class="pill" :class="pillClass" x-text="statusLabel"></span>
                <span class="save-st" aria-live="polite" x-text="saveText"></span>
            </div>
            <div class="d-c">
                <div class="segc" x-data="seg" role="group" aria-label="Rejim">
                    <span class="knob" aria-hidden="true"></span>
                    <button type="button" x-on:click="setMode('write')" :aria-pressed="mode === 'write'" aria-pressed="true">Yozish</button>
                    <button type="button" x-on:click="setMode('preview')" :aria-pressed="mode === 'preview'" aria-pressed="false">Koʻrinish</button>
                </div>
            </div>
            <div class="d-r">
                <button class="gbtn glass" type="button" x-on:click="sheet = true" aria-label="Yozuv sozlamalari" title="Yozuv sozlamalari"><x-icon name="sliders" /></button>
                <span class="d-r">
                    <a class="gbtn glass" :href="url" x-show="published && url" x-cloak aria-label="Saytda koʻrish" title="Saytda koʻrish"><x-icon name="eye" /></a>
                    <button class="capbtn plain sm" type="button" x-show="published && hasPending" x-cloak x-on:click="discard()">Bekor qilish</button>
                    <button class="capbtn filled sm" type="button" x-show="published" x-cloak :disabled="!hasPending" x-on:click="publish()">Yangilash</button>
                    <button class="capbtn filled sm" type="button" x-show="!published" x-on:click="publish()">Chop etish</button>
                </span>
            </div>
        </header>
        <div class="d-scroll" id="edscroll">
            <div class="ed" :class="cat" id="edwrap">
                <label class="sr" for="ft">Sarlavha</label>
                <textarea class="ed-title" id="ft" rows="1" placeholder="Sarlavha" x-ref="title" x-model="title" x-show="mode === 'write'" x-on:input="titleInput()" x-on:keydown.enter.prevent="$refs.body.focus()"></textarea>
                <div class="ed-meta" x-show="mode === 'write'">
                    <div class="segc" x-data="seg" role="group" aria-label="Boʻlim">
                        <span class="knob" aria-hidden="true"></span>
                        <button type="button" x-on:click="setCat('fikr')" :aria-pressed="cat === 'fikr'" aria-pressed="false">Fikr</button>
                        <button type="button" x-on:click="setCat('dev')" :aria-pressed="cat === 'dev'" aria-pressed="false">Dasturlash</button>
                    </div>
                    <label class="menubtn">
                        <span class="sdot" :style="'--c:' + tagColor"></span><span class="sr">Teg</span>
                        <select id="ftag" x-model="tag" x-on:change="changed()">
                            <template x-for="t in tags[cat]" :key="t"><option :value="t" :selected="t === tag" x-text="t"></option></template>
                        </select>
                        <x-icon name="chevUD" />
                    </label>
                    <span class="ed-words" x-text="wordCount + ' soʻz · ' + readTime + ' daqiqa'"></span>
                </div>
                <div class="ed-tools glass" id="edtools" role="toolbar" aria-label="Formatlash" x-show="mode === 'write'">
                    <button class="tb" type="button" x-on:click="md('h2')" title="Sarlavha" aria-label="Sarlavha">H2</button>
                    <button class="tb" type="button" x-on:click="md('bold')" title="Qalin (Ctrl+B)" aria-label="Qalin">B</button>
                    <button class="tb" type="button" x-on:click="md('italic')" title="Kursiv (Ctrl+I)" aria-label="Kursiv"><i>I</i></button>
                    <button class="tb" type="button" x-on:click="md('quote')" title="Iqtibos" aria-label="Iqtibos"><x-icon name="quote" /></button>
                    <button class="tb" type="button" x-on:click="md('list')" title="Roʻyxat" aria-label="Roʻyxat"><x-icon name="list" /></button>
                    <span class="tsep"></span>
                    <button class="tb" type="button" x-on:click="md('link')" title="Havola" aria-label="Havola"><x-icon name="link" /></button>
                    <button class="tb" type="button" x-on:click="md('image')" title="Rasm" aria-label="Rasm"><x-icon name="image" /></button>
                    <button class="tb" type="button" x-on:click="md('code')" title="Kod" aria-label="Kod"><x-icon name="code" /></button>
                    <button class="tb" type="button" x-on:click="md('block')" title="Kod bloki" aria-label="Kod bloki">{ }</button>
                </div>
                <label class="sr" for="fb">Matn</label>
                <textarea class="ed-body" id="fb" placeholder="Fikringizni yozing…" x-ref="body" x-model="body" x-show="mode === 'write'" x-on:input="bodyInput()" x-on:keydown="bodyKey($event)"></textarea>
                <div class="ed-prev" :class="cat === 'fikr' ? 'fikr' : 'dev'" x-show="mode === 'preview'" x-cloak>
                    <div class="r-meta"><span class="sdot" :style="'--c:' + tagColor"></span><span x-text="tag"></span><span>·</span><span x-text="dateLong"></span><span>·</span><span x-text="readTime + ' daqiqa oʻqish'"></span></div>
                    <h1 class="r-title" x-text="title || 'Sarlavhasiz'"></h1>
                    <p class="r-lead" x-show="excerpt" x-text="excerpt"></p>
                    <div class="r-cover" x-show="coverUrl"><div class="cover"><img :src="coverUrl" alt=""></div></div>
                    <div class="prose" x-html="previewHtml || '<p><em>Hali hech narsa yozilmagan.</em></p>'"></div>
                </div>
            </div>
        </div>
        <div class="sheet-back" x-show="sheet" x-cloak x-transition.opacity.duration.190ms x-on:click.self="sheet = false">
            <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="sh-t">
                <div class="grabber" aria-hidden="true"></div>
                <div class="sheet-h"><span></span><h2 id="sh-t">Yozuv sozlamalari</h2><button class="gbtn tint" type="button" x-on:click="sheet = false" aria-label="Tayyor" title="Tayyor"><x-icon name="check" /></button></div>
                <div class="sheet-b">
                    <div class="fgroup">
                        <h3 class="fg-h">Havola</h3>
                        <div class="list"><label class="row frow"><span class="pfx">/post/</span><input id="fslug" x-model="slug" x-on:input="slugInput()" spellcheck="false" autocomplete="off" aria-label="Havola"></label></div>
                        <p class="fg-f err" x-text="slug && slugTaken ? 'Bu havola band — boshqasini tanlang' : ''"></p>
                        <p class="fg-f" x-text="slugTouched ? '' : 'Sarlavhadan avtomatik yaratiladi'"></p>
                    </div>
                    <div class="fgroup">
                        <h3 class="fg-h">Qisqacha tavsif</h3>
                        <div class="list"><label class="row frow"><textarea id="fex" rows="3" maxlength="200" x-model="excerpt" x-on:input="changed()" placeholder="Boʻsh qolsa, matndan avtomatik olinadi" aria-label="Qisqacha tavsif"></textarea></label></div>
                        <p class="fg-f" x-text="excerpt.length + ' / 200'"></p>
                    </div>
                    <div class="fgroup"><div class="list"><label class="row frow"><span class="rt">Sana</span><input type="date" x-model="date" x-on:input="changed()" aria-label="Sana"></label></div></div>
                    <div class="fgroup">
                        <h3 class="fg-h">Muqova · ixtiyoriy</h3>
                        <div class="sh-cover" :class="{ drag }" x-on:dragover.prevent="drag = true" x-on:dragleave="drag = false" x-on:drop.prevent="drag = false; uploadCover($event.dataTransfer.files[0])">
                            <template x-if="coverUrl"><div class="cover"><img :src="coverUrl" alt=""></div></template>
                            <template x-if="!coverUrl"><div class="cover-empty"><x-icon name="image" /><span>Muqova yoʻq</span></div></template>
                        </div>
                        <div class="sh-cbtns">
                            <label class="capbtn gray sm"><x-icon name="upload" /> Rasm yuklash<input class="sr" type="file" accept="image/jpeg,image/png,image/webp" x-on:change="uploadCover($event.target.files[0]); $event.target.value = ''"></label>
                            <button class="capbtn plain sm" type="button" x-on:click="removeCover()">Olib tashlash</button>
                        </div>
                        <p class="fg-f">Rasmni shu yerga tortib tashlash ham mumkin.</p>
                    </div>
                    <div class="fgroup"><div class="list"><div class="row"><span class="rt" style="flex:1">Soʻzlar</span><span class="rd" x-text="wordCount"></span></div><div class="row"><span class="rt" style="flex:1">Oʻqish vaqti</span><span class="rd" x-text="readTime + ' daqiqa'"></span></div></div></div>
                    <div class="fgroup" x-show="id">
                        <div class="list">
                            <button class="row rbtn" type="button" x-show="published" x-on:click="unpublish()">Qoralamaga olish</button>
                            <button class="row rbtn red" type="button" x-on:click="remove()">Yozuvni oʻchirish</button>
                        </div>
                        <p class="fg-f" x-show="published">Qoralamaga olinsa, yozuv saytdan yashiriladi, lekin oʻchmaydi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
