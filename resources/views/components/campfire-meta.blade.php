@props([
    /** @var \App\Models\Campfire */
    'campfire'
])

<div {{ $attributes->class(['border-t border-b border-grey-darker py-8 mt-[25px] space-y-4']) }}>
    <div class="flex items-start">
        <x-icons.event-type/>
        <div class="ml-[24px] leading-[24px]">
            <p class="font-medium">{{ $campfire->indoorOrOutdoor() }} event</p>
            <p class="text-sm text-[#6B7280]">Ths event is slated to be an {{ $campfire->indoorOrOutdoor() }} event.
                Please dress
                accordingly.</p>
        </div>
    </div>
    <div class="flex items-start">
        <x-icons.calendar/>
        <div class="ml-[24px] leading-[24px]">
            <p class="font-medium">Scheduled</p>
            <p class="text-sm text-[#6B7280]">
                {{ $campfire->frequency->name }}
            </p>
        </div>
    </div>
</div>
