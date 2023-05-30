@props([
    'for',
    'label',
    'required' => false
])
<label class="block font-semibold text-sm text-[#B8B6B6] mb-3"
       for="{{ $for }}">
    {{ $label }}
    @if($required)
        <span class="text-red">*</span>
    @endif
</label>
