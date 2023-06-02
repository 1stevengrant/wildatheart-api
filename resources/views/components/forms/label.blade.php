@props([
    'for',
    'label',
    'required' => false
])
<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-grey-200 mb-3']) }}
       for="{{ $for }}">
    {{ $label }}
    @if($required)
        <span class="text-red">*</span>
    @endif
</label>
