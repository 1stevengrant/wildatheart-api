<div
    class="mb-4 text-right font-semibold"
    @keydown.escape="shareModal = false"
    x-data="{ 'shareModal': false }"
>
    <x-share-button class="hidden md:flex" @click="shareModal = true" />

    <!-- Modal -->
    <div
        class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
        x-show="shareModal"
    >
        <!-- Modal inner -->
        <div
            class="mx-auto max-w-3xl rounded bg-white px-6 py-4 text-left shadow-lg"
            @click.away="shareModal = false"
            x-transition:enter="duration-300 motion-safe:ease-out"
            x-transition:enter-start="scale-90 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
        >
            <!-- Title / Close-->
            <div class="mb-4 flex items-center justify-between">
                <h5 class="mr-3 max-w-none text-black">Share On</h5>

                <button
                    type="button"
                    class="z-50 cursor-pointer text-black"
                    @click="shareModal = false"
                >
                    <x-icons.close />
                </button>
            </div>

            <!-- content -->
            <div class="grid grid-cols-2 gap-2 text-black">
                <x-share-link
                    url="https://twitter.com/share?url={{ route('view.campfire', $campfire) }}&text={{ $campfire->name }}"
                >
                    Twitter
                </x-share-link>

                <!-- Facebook (url) -->
                <x-share-link
                    url="https://www.facebook.com/sharer/sharer.php?u={{ route('view.campfire', $campfire) }}"
                >
                    Facebook
                </x-share-link>

                <x-share-link
                    url="https://www.linkedin.com/shareArticle?url={{ route('view.campfire', $campfire)
                }}&title={{ $campfire->name }}&source={{ route('view.campfire', $campfire) }}"
                >
                    LinkedIn
                </x-share-link>

                <x-share-link
                    url="mailto:?subject=Check out this Fire from {{ $campfire->host->full_name }}&body={{
                route('view.campfire', $campfire) }}"
                >
                    Email
                </x-share-link>
            </div>
        </div>
    </div>
</div>
