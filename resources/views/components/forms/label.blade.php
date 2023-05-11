@props([
    'for',
    'label',
    'required' => false
])
<label class="block font-semibold text-sm mb-1"
       for="{{ $for }}">
    {{ $label }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>
