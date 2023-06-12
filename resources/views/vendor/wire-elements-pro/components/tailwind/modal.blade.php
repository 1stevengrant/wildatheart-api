@props(['contentPadding' => true, 'onSubmit' => ''])
<form
    class="flex h-full flex-col bg-[#0B1419]"
    wire:submit.prevent="{{ $onSubmit }}"
>
    @if ($title ?? false)
        <div class="p-4 text-white sm:px-6 sm:py-5">
            <h3 class="leading-4 text-lg font-medium">{{ $title }}</h3>
        </div>
    @endif

    <div
        @class(['overflow-y-auto', 'px-4 pb-4 pt-5 sm:p-6 sm:pb-4' => $contentPadding])
    >
        {{ $slot }}
    </div>
    @if ($buttons ?? false)
        <div
            class="mt-auto gap-x-2 space-y-2 px-4 py-3 sm:flex sm:flex-row-reverse sm:space-y-0 sm:px-6"
        >
            {{ $buttons }}
        </div>
    @endif
</form>
