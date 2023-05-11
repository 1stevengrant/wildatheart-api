@props([
    'name',
    'label',
    'error' => false,
    'min' => false,
    'max' => false,
    'instruction' => false,
])
<div>
    <x-forms.label
        label="{{ $label }}"
        for="{{ $name }}"
        required="{{ $attributes->has('required') }}"
    />

    <input
        type="date"
        {{ $attributes->merge(['class' => 'p-2 border border-gray-200']) }}
        id="{{ $name }}"
        @if($min)
            min="{{ $min }}"
        @endif
        @if($max)
            max="{{ $max }}"
        @endif
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
