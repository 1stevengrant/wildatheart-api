<x-wire-elements-pro::tailwind.modal
    on-submit="handle">
    <x-slot name="title">
        Request More Books
    </x-slot>

   <div>
       <p>Are you sure you wish to request more books for this 🔥 on {{ prettyDate($fire->date) }}?</p>
       @if($fire->book_request_count > 0)
           <p> You have
               requested {{ $fire->book_request_count }} times already</p>
       @endif
   </div>

    <x-slot name="buttons">
        <button type="submit"
                class="p-4 bg-green-600 text-white font-semibold">
            Yes
        </button>
        <button
            wire:click="$emit('modal.close')"
            class="bg-white text-black border border-black font-semibold p-4 inline-block">
            Cancel
        </button>
    </x-slot>

</x-wire-elements-pro::tailwind.modal>
