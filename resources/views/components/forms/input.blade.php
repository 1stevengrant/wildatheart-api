@props([
    'type' => false,
    'name',
    'label',
    'error' => false,
    'instruction' => false,
    'placeholder' => false,
    'append' => false,
    'maxlength' => false,
    'minlength' => false,
])
<div {{ $attributes->merge(['class' => '']) }}>
    <x-forms.label
        label="{{ $label }}"
        for="{{ $name }}"
        required="{{ $attributes->has('required') }}"
    />

    <div class="flex items-center">
        <input
            autocomplete
            @if($placeholder)
                placeholder="{{ $placeholder }}"
            @endif
            type="{{ $type ?? 'text' }}"
            {{
                $attributes->merge(['class' => 'p-2 border border-grey-border w-full bg-form rounded-md
                focus:border-orange focus:outline-none focus:ring-orange text-white'])
            }}
            @if($maxlength)
                    maxlength="{{ $maxlength }}"
                @endif
            @if($minlength)
                minlength="{{ $minlength }}"
            @endif
            id="{{ $name }}"
        />
        @if ($append)
            <div class="ml-4">{{ $append }}</div>
        @endif
    </div>

    @if ($error)
        <x-forms.error :error="$error" />
    @endif

    @if ($instruction)
        <x-forms.instruction :instruction="$instruction" />
    @endif
</div>
