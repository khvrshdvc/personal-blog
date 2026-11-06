<div class="alert-back" x-show="$store.dialog.open" x-cloak x-on:click.self="$store.dialog.done(false)" x-on:keydown.escape.window="$store.dialog.done(false)" x-on:keydown.tab.prevent="$store.dialog.open && $store.dialog.trap($event)">
    <div class="alert glass" role="alertdialog" aria-modal="true" aria-labelledby="al-t">
        <h3 id="al-t" x-text="$store.dialog.title"></h3>
        <p x-show="$store.dialog.message" x-text="$store.dialog.message"></p>
        <div class="alert-btns">
            <button type="button" data-a="0" x-on:click="$store.dialog.done(false)" x-text="$store.dialog.cancel"></button>
            <button type="button" data-a="1" class="destructive" x-on:click="$store.dialog.done(true)" x-text="$store.dialog.ok"></button>
        </div>
    </div>
</div>
