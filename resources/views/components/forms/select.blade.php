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
        required="{{ $attributes->has('required') }}"
    />
    <div class="flex mt-2">
        <select {{ $attributes->merge(['class' => 'bg-[#171513] text-white rounded-md border border-[#2E2C29] block
        w-full p-2
        focus:outline-none focus:border-orange focus:ring-orange' . ($trailingAddOn ? '' : '')]) }}>
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
