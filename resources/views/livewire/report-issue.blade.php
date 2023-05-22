<x-wire-elements-pro::tailwind.modal
    on-submit="handle">
    <x-slot name="title">
        Report an Issue
    </x-slot>

    <div class="space-y-4">

        <div class="space-y-4">
            <x-forms.input
                required
                name="name"
                label="Name"
                wire:model.defer="name"
                :error="$errors->first('name')"
            />
            <x-forms.input
                required
                name="email"
                label="Email"
                wire:model.defer="email"
                :error="$errors->first('email')"
            />
            <x-forms.input
                required
                name="confirm_email"
                label="Confirm Email"
                wire:model.defer="confirm_email"
                :error="$errors->first('confirm_email')"
            />
            <x-forms.input
                required
                name="subject"
                label="Subject"
                wire:model.defer="subject"
                :error="$errors->first('subject')"
            />
            <x-forms.textarea
                required
                name="description"
                label="Description"
                rows="10"
                wire:model.defer="description"
                :error="$errors->first('description')"
            />
        </div>
    </div>

    <x-slot name="buttons">
        <button type="submit"
                class="p-4 bg-black text-white font-semibold">
            Report Issue
        </button>
        <button
            wire:click="$emit('modal.close')"
            class="bg-black text-white font-semibold p-4 inline-block">
            Cancel
        </button>
    </x-slot>

</x-wire-elements-pro::tailwind.modal>
