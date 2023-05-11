@props([
    'type' => false,
    'name',
    'label',
    'error' => false,
    'instruction' => false,
    'placeholder' => false,
])
<div>
    <x-forms.label
        label="{{ $label }}"
        for="{{ $name }}"
    />

    <input
        @if($placeholder)
            placeholder="{{ $placeholder }}"
        @endif
        type="{{ $type ?? 'text' }}"
        {{ $attributes->merge(['class' => 'p-2 border border-gray-200 w-full']) }}
        id="{{ $name }}"
    />

    @if ($error)
        <x-forms.error
            :error="$error"/>
    @endif

    @if($instruction)
        <x-forms.instruction
            :instruction="$instruction"/>
    @endif
</div>
