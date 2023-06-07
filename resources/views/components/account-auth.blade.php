@auth
    <div
        {{ $attributes->merge(['class' => 'relative']) }}
        x-data="{
            open: false,
            toggle() {
                if (this.open) {
                    return this.close()
                }

                this.$refs.button.focus()

                this.open = true
            },
            close(focusAfter) {
                if (! this.open) return

                this.open = false

                focusAfter && focusAfter.focus()
            }
        }"
        x-on:keydown.escape.prevent.stop="close($refs.button)"
        x-on:focusin.window="! $refs.panel.contains($event.target) && close()"
        x-id="['dropdown-button']"
        class="relative"
    >
        <!-- Button -->
        <button
            x-ref="button"
            x-on:click="toggle()"
            :aria-expanded="open"
            :aria-controls="$id('dropdown-button')"
            type="button"
            class="w-12 h-12 bg-white flex items-center justify-center rounded-full"
        >
            <img src="{{ auth()->user()->profilePhoto() }}"
                 class="rounded-full w-9 h-9"
                 alt="">
        </button>

        <!-- Panel -->
        <div
            x-ref="panel"
            x-show="open"
            x-transition.origin.top.left
            x-on:click.outside="close($refs.button)"
            :id="$id('dropdown-button')"
            style="display: none;"
            class="absolute left-0 mt-2 w-40 rounded-md bg-white shadow-md"
        >
            <form method="POST"
                  class="flex"
                  action="{{ route('logout') }}">
                @csrf

                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
               this.closest('form').submit();"
                   class="flex items-center gap-2 w-full first-of-type:rounded-t-md last-of-type:rounded-b-md px-4 py-2.5 text-left text-sm hover:bg-gray-50 disabled:text-gray-500">
                    {{ __('Logout') }}
                </a>
            </form>
        </div>
    </div>
@endauth
