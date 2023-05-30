@props(['contentPadding' => true, 'onSubmit' => ''])
<form class="flex flex-col h-full bg-[#0B1419]"
      wire:submit.prevent="{{ $onSubmit }}">
    @if($title ?? false)
        <div class="p-4 sm:px-6 sm:py-5 text-white">
            <h3 class="text-lg leading-4 font-medium">{{ $title }}</h3>
        </div>
    @endif
    <div @class(['overflow-y-auto', 'px-4 pt-5 pb-4 sm:p-6 sm:pb-4' => $contentPadding])>
        {{ $slot }}
    </div>
    @if($buttons ?? false)
        <div class="px-4 py-3 sm:px-6 gap-x-2 space-y-2 sm:space-y-0 sm:flex sm:flex-row-reverse mt-auto">
            {{ $buttons }}
        </div>
    @endif
</form>
