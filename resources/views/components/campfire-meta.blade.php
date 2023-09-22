@props([
    /** @var \App\Models\Campfire */
    'campfire',
])

<div
    {{ $attributes->class(['mt-[25px] space-y-4 border-b border-t border-grey-darker py-8']) }}
>
    <div class="flex items-start">
        <x-icons.event-type class="flex-shrink-0" />
        <div class="ml-[24px] leading-[24px]">
            <p class="font-medium">Outdoor Event</p>
            <p class="text-sm text-[#6B7280]">
                This event is slated to be an outdoor event. Please dress
                accordingly.
            </p>
        </div>
    </div>
    <div class="flex items-start">
        <x-icons.calendar class="flex-shrink-0" />
        <div class="ml-[24px] leading-[24px]">
            <p class="font-medium">Frequency</p>
            <p class="text-sm text-[#6B7280]">
                The vision of Fires is that they’re monthly gatherings. However,
                each Host is free to schedule his Fire as best suits the local
                context.
            </p>
        </div>
    </div>
</div>
