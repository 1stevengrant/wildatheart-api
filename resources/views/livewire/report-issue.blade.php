<x-wire-elements-pro::tailwind.modal on-submit="handle">
    <x-slot name="title">Report an Issue or Provide Feedback</x-slot>

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
        <x-primary-button>Submit</x-primary-button>
        <x-secondary-button wire:click="$emit('modal.close')">
            Cancel
        </x-secondary-button>
    </x-slot>
</x-wire-elements-pro::tailwind.modal>
