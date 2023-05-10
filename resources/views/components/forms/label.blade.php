@props([
    'for',
    'label'
])
<label class="block font-semibold text-sm mb-1"
       for="{{ $for }}">
    {{ $label }}
</label>
