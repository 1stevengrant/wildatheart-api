<div wire:ignore {{ $attributes }}>
    <x-profile.menu-item slug="profile" :href="route('my.profile')">
        <x-slot name="icon">
            <x-icons.user />
        </x-slot>
        {{ __('My Fire Host Profile ') }}
    </x-profile.menu-item>

    <x-profile.menu-item slug="fires*" :href="route('my.campfires')">
        <x-slot name="icon">
            <x-icons.fire />
        </x-slot>
        {{ __('My Fires') }}
    </x-profile.menu-item>

    <x-profile.menu-item slug="connections" :href="route('my.connections')">
        <x-slot name="icon">
            <x-icons.megaphone />
        </x-slot>
        {{ __('My Connections') }}
    </x-profile.menu-item>

    {{-- <x-profile.menu-item slug="addresses" :href="route('my.addresses')"> --}}
    {{-- <x-slot name="icon"> --}}
    {{-- <x-icons.address /> --}}
    {{-- </x-slot> --}}
    {{-- {{ __('My Addresses') }} --}}
    {{-- </x-profile.menu-item> --}}

    <form
        method="POST"
        class="flex w-full rounded-lg px-4 py-5"
        action="{{ route('logout') }}"
    >
        @csrf
        <button
            onclick="event.preventDefault(); this.closest('form').submit();"
            class="flex w-full items-center space-x-4"
        >
            <x-icons.logout />
            <span class="text-sm text-white">
                {{ __('Logout') }}
            </span>
        </button>
    </form>
</div>
