<div
    x-data="{
        messages: [],
        remove(message) {
            this.messages.splice(this.messages.indexOf(message), 1)
        },
    }"
    @notify.window="let message = $event.detail.message;
    let timeout = $event.detail.timeout;
    messages.push(message);
    if (timeout !== null) {
        setTimeout(() => { remove(message) }, timeout)
    }
    "
    class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end justify-center space-y-4 px-4 py-6 sm:justify-start sm:p-6"
>
    <template
        x-for="(message, messageIndex) in messages"
        :key="messageIndex"
        hidden
    >
        <div
            x-transition:enter="transform transition duration-300 ease-out"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-2 sm:translate-y-0"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition duration-100 ease-in"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full max-w-sm rounded-lg bg-white shadow-lg"
        >
            <div class="shadow-xs overflow-hidden rounded-lg">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-6 w-6 text-green-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p
                                x-text="message"
                                class="leading-5 text-gray-900 text-sm font-medium"
                            ></p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button
                                @click="remove(message)"
                                class="text-gray-400 focus:text-gray-500 inline-flex transition duration-150 ease-in-out focus:outline-none"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
