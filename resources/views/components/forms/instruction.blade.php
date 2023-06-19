@props([
    'instruction' => null,
])
<div {{ $attributes->merge(['class' => 'text-sm text-grey-200 mt-4']) }}>
    {{ $instruction }}
</div>
