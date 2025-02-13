<template>
    <div>
        <button
            class="btn btn-primary w-full"
            @click.prevent="displayModal = !displayModal"
        >
            Trigger Astro Build
        </button>
        <modal
            name="astro-build-stack"
            @closed="displayModal = false"
            v-if="displayModal"
        >
            <div class="p-4">
                <h2 class="font-bold text-lg mb-4">Trigger Build?</h2>
                <p class="mb-4 text-gray-700">
                    Are you sure you want to trigger the Astro build?
                </p>
                <div class="space-x-4">
                    <button
                        class="btn"
                        @click.prevent="displayModal = false">
                        No, changed my mind
                    </button>
                    <button
                        class="btn btn-primary"
                        :disabled="astroBuildTriggered"
                        @click.prevent="triggerAstroBuild">
                        Yes, let's do it!
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
import axios from "axios";
export default {
    mixins: [Fieldtype],

    data() {
        return {
            displayModal: false,
            astroBuildTriggered: false,
        };
    },

    methods: {
        async triggerAstroBuild() {
            console.log('triggerAstroBuild');
            this.astroBuildTriggered = true;

            try {

                const response = await axios.post('/wild-at-heart/trigger-astro-build');

                if (response.status === 200) {
                    this.$toast.success('Astro build triggered successfully');
                }
            } catch (e) {
                console.log(e);
                this.$toast.error('Failed to trigger Astro build');
            } finally {
                this.astroBuildTriggered = false;
                this.displayModal = false;
            }

        },
    },
};
</script>
