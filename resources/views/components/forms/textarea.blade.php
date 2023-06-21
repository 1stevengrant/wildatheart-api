@props(['name', 'label', 'error' => false, 'instruction' => false, 'placeholder' => false])
<div>
    <x-forms.label
        label="{{ $label }}"
        for="{{ $name }}"
        required="{{ $attributes->has('required') }}"
    />

    <textarea
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        {{
            $attributes->merge([
                'class' => 'border border-grey-border w-full bg-form rounded-md
            focus:border-orange focus:outline-none focus:ring-orange text-white',
            ])
        }}
        id="{{ $name }}"
    ></textarea>

    @if ($error)
        <x-forms.error :error="$error" />
    @endif

    @if ($instruction)
        <x-forms.instruction :instruction="$instruction" />
    @endif
</div>
