@props(['label', 'required' => false, 'optional' => false])
<div class="form-row">
    <div class="form-row-label">
        <span class="text-sm font-medium text-text-primary">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
            @if($optional)<span class="text-text-secondary font-normal">(Optional)</span>@endif
        </span>
    </div>
    <div class="form-row-input">
        {{ $slot }}
    </div>
</div>
