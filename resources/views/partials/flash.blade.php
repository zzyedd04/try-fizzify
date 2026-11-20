@php
    $flashType = session('error') || $errors->any() ? 'error' : 'ok';
    $flashMsg = session('error') ?? session('success') ?? session('info') ?? $errors->first();
@endphp
<div id="toast" class="toast {{ $flashType === 'error' ? 'is-error' : '' }}" data-msg="{{ $flashMsg }}"></div>
