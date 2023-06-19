<x-wire-elements-pro::tailwind.modal
    onSubmit="confirm"
    :contentPadding="false"
>
    <div class="my-6 space-y-6">
        <div class="px-4">
            <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                <button
                    type="button"
                    wire:click="$emit('modal.close')"
                    class="text-gray-400 hover:text-gray-500 focus:ring-indigo-500 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-offset-2"
                >
                    <span class="sr-only">
                        {{ __('wire-elements-pro::modal.confirmation.close') }}
                    </span>
                    <svg
                        class="h-6 w-6"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                <h3 class="leading-6 text-gray-900 text-lg font-medium">
                    {{ $prompt['title'] }}
                </h3>
                <div class="mt-2">
                    <p class="text-gray-500 text-sm">
                        {{ $prompt['message'] }}
                    </p>
                </div>
            </div>
        </div>

        @if ($tableData)
            <div class="border-gray-200 relative overflow-x-auto border-t">
                <table
                    class="text-gray-500 divide-gray-200 w-full divide-y text-left text-sm"
                >
                    <thead class="text-gray-700 bg-gray-50 text-xs uppercase">
                        <tr>
                            @foreach ($tableHeaders as $header)
                                <th scope="col" class="px-6 py-3">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tableData as $columns)
                            <tr class="border-b bg-white">
                                @foreach ($columns as $column)
                                    <td class="px-6 py-4">
                                        {{ $column }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($confirmPhrase)
            <div class="px-4 sm:px-6">
                <input
                    type="text"
                    id="confirm-phrase"
                    wire:model.defer="confirmPhraseInput"
                    class="border-gray-300 text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-lg border bg-white p-2.5 text-sm"
                    placeholder="{{ __('wire-elements-pro::modal.confirmation.please_enter_phrase_to_continue', ['phrase' => $confirmPhrase]) }}"
                    required
                />

                @error('confirmPhraseInput')
                    <p class="text-red-600 mt-2 text-sm">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>

    <x-slot name="buttons">
        <div class="flex w-full justify-between">
            <button
                type="button"
                wire:click="$emit('modal.close')"
                class="text-gray-900 border-gray-300 hover:bg-gray-100 focus:ring-gray-200 mb-2 rounded-lg border bg-white px-5 py-2.5 text-sm font-medium focus:outline-none focus:ring-4"
                wire:loading.attr="disabled"
            >
                {{ $prompt['cancel'] }}
            </button>

            <button
                type="submit"
                class="bg-indigo-700 hover:bg-indigo-800 focus:ring-indigo-300 mb-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-4"
                wire:loading.attr="disabled"
            >
                {{ $prompt['confirm'] }}
            </button>
        </div>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
