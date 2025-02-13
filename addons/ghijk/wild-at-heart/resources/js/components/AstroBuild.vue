<template>
    <div>
        <h1 class="mb-6">Astro Build</h1>

        <div class="p-4 card content">
            <p>
                Some content isn't dynamic and so you might need to trigger an
                Astro build manually. This button does exactly what it says on the tin.
            </p>
            <button @click.prevent="triggerAstroBuild"
                class="btn btn-primary w-full"
                    :disabled="astroBuildTriggered"
            >
                Trigger Astro Build
            </button>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            astroBuildTriggered: false
        }
    },
    methods: {
        async triggerAstroBuild() {
            this.astroBuildTriggered = true;
            try {

                const response = await axios.post(route('wild-at-heart.trigger-astro-build'));

                if (response.status === 200) {
                    this.$toast.success('Astro build triggered successfully');
                }
            } catch (e) {
                this.$toast.error('Failed to trigger Astro build');
            } finally {
                this.astroBuildTriggered = false;
            }

        },
    },
};
</script>
