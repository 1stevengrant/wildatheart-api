<script setup>
import { ref } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Button, ConfirmationModal } from "@statamic/cms/ui";
import axios from "axios";

const props = defineProps(Fieldtype.props);
const emit = defineEmits(Fieldtype.emits);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const showConfirmation = ref(false);
const triggering = ref(false);

async function triggerAstroBuild() {
    triggering.value = true;

    try {
        await axios.post(cp_url("command-center/astro-build"));
        Statamic.$toast.success("Astro build triggered successfully");
    } catch (e) {
        Statamic.$toast.error("Failed to trigger Astro build");
    } finally {
        triggering.value = false;
        showConfirmation.value = false;
    }
}
</script>

<template>
    <div>
        <Button
            variant="primary"
            text="Trigger Astro Build"
            class="w-full"
            @click="showConfirmation = true"
        />

        <ConfirmationModal
            v-model:open="showConfirmation"
            title="Trigger Astro Build?"
            body-text="Are you sure you want to trigger the Astro build?"
            button-text="Yes, trigger build"
            :busy="triggering"
            @confirm="triggerAstroBuild"
        />
    </div>
</template>
