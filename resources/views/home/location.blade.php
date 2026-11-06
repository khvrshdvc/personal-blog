@php $time = now('Asia/Tashkent')->format('H:i'); @endphp
<x-card class="b-loc" x-data="clock">
    <x-card-title icon="pin">Joylashuv<span class="r">UTC+5</span></x-card-title>
    <span class="radar" aria-hidden="true"></span>
    <span class="pin" aria-hidden="true"><i></i><i></i><i></i></span>
    <div class="lb">
        <span><b>Toshkent</b><small>41.31° N · 69.28° E</small></span>
        <span class="tm" id="ltm" x-text="time" :aria-label="'Toshkentda soat ' + time" aria-label="Toshkentda soat {{ $time }}">{{ $time }}</span>
    </div>
</x-card>
