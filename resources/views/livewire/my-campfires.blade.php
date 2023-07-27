<x-layouts.account>
    <div class="mb-6 flex items-center justify-between">
        <x-type.registration-h1>
            {{ __('My Fires') }}
        </x-type.registration-h1>

        <x-primary-button wire:click="startAFire">
            {{ __('Start a New Fire') }}
        </x-primary-button>
    </div>

    @unless ($campfires->count())
        <p class="text-center text-2xl">
            {{ __('You have no Fires currently.') }}
        </p>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($campfires as $campfire)
                <div x-data>
                    <a
                        href="{{ route('view.campfire', $campfire) }}"
                        class="relative z-40"
                    >
                        <x-campfire-card
                            class=""
                            :title="$campfire->name . ' Fire'"
                            :frequency="$campfire->frequency"
                            :location="$campfire->location"
                            :host-name="$campfire->host->full_name"
                            :profile-image="
                                auth()
                                    ->user()
                                    ->profilePhoto()
                            "
                        />
                    </a>
                    <div
                        class="relative -mt-2 flex justify-end space-x-4 rounded-lg bg-white/10 p-6 text-sm"
                    >
                        <button
                            class="flex items-center space-x-2 hover:text-orange"
                            wire:click="$emit('modal.open', 'delete-campfire', {'campfire' : '{{ $campfire->id }}'})"
                        >
                            <x-icons.trash />

                            <span class="hidden md:inline">
                                {{ __('Delete Fire') }}
                            </span>
                        </button>
                        <a
                            href="{{ route('edit.campfire', $campfire) }}"
                            class="flex items-center space-x-2 hover:text-orange"
                        >
                            <x-icons.pencil />

                            <span class="hidden md:inline">
                                {{ __('Edit Fire') }}
                            </span>
                        </a>
                        <button
                            class="flex items-center space-x-2 hover:text-orange"
                            onclick="copyLinkToClipboard('{{ $campfire->url }}')"
                        >
                            <x-icons.duplicate />

                            <span class="hidden md:inline">
                                {{ __('Copy Link') }}
                            </span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endunless
</x-layouts.account>

@push('scripts')
    <script>
        document.addEventListener("livewire:load", function () {
            document.title = "My Fires | {{ config('app.name') }}";
        });

        function copyLinkToClipboard(url) {
            // Create a temporary input element
            const tempInput = document.createElement("input");

            // Set its value to the text you want to copy
            tempInput.value = url;

            // Append the input element to the DOM
            document.body.appendChild(tempInput);

            // Select the text inside the input element
            tempInput.select();

            // Copy the selected text to the clipboard
            document.execCommand("copy");

            // Remove the temporary input element
            document.body.removeChild(tempInput);
            // alert('Copied URL ' + url + ' to clipboard')
            Livewire.emit("copiedLinkToClipboard");
        }
    </script>
@endpush
