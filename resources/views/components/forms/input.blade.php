@props([
    'type' => false,
    'name',
    'label',
    'error' => false,
    'instruction' => false,
    'placeholder' => false,
])
<div {{ $attributes->merge(['class' => '']) }}>
    <x-forms.label
        label="{{ $label }}"
        for="{{ $name }}"
        required="{{ $attributes->has('required') }}"
    />

    <input
        @if($placeholder)
            placeholder="{{ $placeholder }}"
        @endif
        type="{{ $type ?? 'text' }}"
        {{ $attributes->merge(['class' => 'p-2 border border-[#2E2C29] w-full bg-[#171513] rounded-md
        focus:border-orange focus:outline-none focus:ring-orange text-white']) }}
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
