@props([
    'placeholder' => null,
    'trailingAddOn' => null,
    'error' => false,
    'instruction' => false,
    'name',
    'label',
])
<div>
    <x-forms.label
        label="{{ $label }}"
        for="{{ $name }}"
    />
    <div class="flex">
        <select {{ $attributes->merge(['class' => 'border border-gray-200 block mt-1 w-full p-2' .
        ($trailingAddOn ? '' : '')]) }}>
            @if ($placeholder)
                <option disabled value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>

        @if ($trailingAddOn)
            {{ $trailingAddOn }}
        @endif
    </div>
    @if($error)
        <x-forms.error :error="$error" />
    @endif

    @if($instruction)
        <x-forms.instruction
            :instruction="$instruction"/>
    @endif
</div>
