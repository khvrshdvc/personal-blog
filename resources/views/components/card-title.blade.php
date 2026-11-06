@props(['icon' => null])
<span {{ $attributes->class('ck') }}>@if ($icon)<x-icon :name="$icon" :size="14" />@endif{{ $slot }}</span>
