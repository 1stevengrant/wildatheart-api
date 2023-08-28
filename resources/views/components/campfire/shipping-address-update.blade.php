@props([
    /** @var \App\Models\Campfire */
    'campfire',
])
@if (! $campfire->hasRequestedGift() && $campfire->isHost())
    <div {{ $attributes->merge(['class' => 'my-6 md:my-10']) }}>
        <p class="mb-4">
            We want to fuel your Fire. Share your shipping address with us, and
            we’ll ship you a gift to get you going.
        </p>
        <x-primary-link href="{{ route('gift', $campfire) }}">
            {{ __('Confirm Shipping Address') }}
        </x-primary-link>
    </div>
@endif
