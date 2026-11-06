<div class="kp-back" x-show="palette.open" x-cloak x-transition.opacity.duration.150ms @click.self="closePalette()" @keydown.escape.window="closePalette()">
    <div class="kp" role="dialog" aria-modal="true" aria-label="Qidiruv" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()">
        <div class="kp-in">
            <x-icon name="search" />
            <input x-ref="paletteInput" x-model="palette.q" @input.debounce.180ms="search()" type="search" placeholder="Fikrlar, yozuvlar, loyihalar…" autocomplete="off" role="combobox" aria-expanded="true" aria-controls="kpl" :aria-activedescendant="palette.items.length ? 'kpo' + palette.i : ''">
            <kbd>esc</kbd>
        </div>
        <div class="kp-l" id="kpl" role="listbox">
            <template x-for="(item, k) in palette.items" :key="k">
                <div>
                    <h6 x-show="k === 0 || palette.items[k - 1].g !== item.g" x-text="item.g"></h6>
                    <a class="kp-r" :class="{ on: k === palette.i }" :href="item.h" :id="'kpo' + k" role="option" :aria-selected="k === palette.i" @mousemove="palette.i = k">
                        <span class="ic" x-html="icons[item.i]"></span>
                        <span class="t" x-text="item.t"></span>
                        <small x-show="item.s" x-text="item.s"></small>
                    </a>
                </div>
            </template>
            <div class="kp-e" x-show="palette.loaded && !palette.items.length">Hech narsa topilmadi</div>
        </div>
        <div class="kp-f">
            <span><kbd>↑</kbd><kbd>↓</kbd> tanlash</span>
            <span><kbd>↵</kbd> ochish</span>
            <span><kbd>esc</kbd> yopish</span>
        </div>
    </div>
</div>
