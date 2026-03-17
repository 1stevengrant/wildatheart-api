<script setup>
import { ref } from "vue";
import { Button, Card, ConfirmationModal, Subheading } from "@statamic/cms/ui";
import axios from "axios";

const showConfirmation = ref(false);
const triggering = ref(false);

async function triggerBuild() {
    triggering.value = true;

    try {
        const response = await axios.post(cp_url("command-center/astro-build"));

        if (response.status === 200) {
            Statamic.$toast.success("Astro build triggered successfully");
        }
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
        <Subheading class="mb-2">Astro Build</Subheading>

        <Card class="p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Trigger a new build of the Astro frontend via Cloudflare deploy hook.
                </p>
                <Button
                    variant="primary"
                    text="Trigger Build"
                    @click="showConfirmation = true"
                />
            </div>
        </Card>

        <ConfirmationModal
            v-model:open="showConfirmation"
            title="Trigger Astro Build?"
            body-text="Are you sure you want to trigger the Astro build?"
            button-text="Yes, trigger build"
            :busy="triggering"
            @confirm="triggerBuild"
        />
    </div>
</template>
