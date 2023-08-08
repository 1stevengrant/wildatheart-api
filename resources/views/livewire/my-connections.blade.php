<x-layouts.account>
    <x-type.registration-h1 class="mb-8">My Connections</x-type.registration-h1>

    <div class="space-y-8">
        @foreach ($campfires as $campfire)
            @if ($campfire->attendeeCount > 0)
                <div>
                    <div class="flex items-center space-x-4">
                        <h2 class="text-lg font-medium">
                            {{ $campfire->name }} Fire
                        </h2>
                        <x-primary-button
                            onclick="copyToClipboard('{{ $campfire->allAttendeeEmails() }}')"
                        >
                            {{ __('Copy Email Addresses') }}
                        </x-primary-button>
                    </div>
                    <ul
                        role="list"
                        class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2"
                    >
                        @foreach ($campfire->attendees as $connection)
                            <x-connection :connection="$connection" />
                        @endforeach
                    </ul>
                </div>
            @else
                <h2 class="text-lg font-medium">{{ $campfire->name }} Fire</h2>
                <p>No connections yet.</p>
            @endif
        @endforeach

        @if ($allConnections->count() > 0)
            <div class="flex items-center space-x-4">
                <h2 class="text-lg font-medium">All Connections</h2>
                <x-primary-button
                    onclick="copyToClipboard('{{ $allConnectionsEmails }}')"
                >
                    {{ __('Copy Email Addresses') }}
                </x-primary-button>
            </div>

            <ul role="list" class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                @foreach ($allConnections as $connection)
                    <x-connection :connection="$connection" />
                @endforeach
            </ul>
        @else
            <p>You have no Connections right now.</p>
        @endif
    </div>
</x-layouts.account>

@push('scripts')
    <script>
        function copyToClipboard(url, type = "copiedEmailsToClipboard") {
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
            Livewire.emit(type);
        }
    </script>
@endpush
