@props(['status'])
@switch($status)
    @case('success')
        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M7.6 12.4l2.9 2.9 5.9-6.1" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        @break
    @case('failure')
        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M9 9l6 6M15 9l-6 6" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
        @break
    @case('running')
        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-opacity=".25" stroke-width="2.6"/><path class="arc" d="M12 3a9 9 0 0 1 9 9" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg>
        @break
    @default
        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path d="M8 12h8" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
@endswitch
