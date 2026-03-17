<script setup>
import { ref } from "vue";
import { Card, Subheading, Switch } from "@statamic/cms/ui";
import axios from "axios";

const props = defineProps({
    features: Array,
});

const localFeatures = ref([...props.features]);
const togglingFeatures = ref({});

async function toggleFeature(feature) {
    togglingFeatures.value[feature.name] = true;

    try {
        await axios.post(cp_url("command-center/features/toggle"), {
            feature: feature.name,
            active: !feature.active,
        });

        feature.active = !feature.active;

        const state = feature.active ? "activated" : "deactivated";
        Statamic.$toast.success(`${feature.name} ${state} for everyone`);
    } catch (e) {
        Statamic.$toast.error(`Failed to toggle ${feature.name}`);
    } finally {
        delete togglingFeatures.value[feature.name];
    }
}
</script>

<template>
    <div>
        <Subheading class="mb-2">Feature Flags</Subheading>

        <Card v-if="localFeatures.length === 0" class="p-4">
            <p class="text-gray-600">
                No feature flags found. Define features using
                <code>Feature::define()</code> in your application.
            </p>
        </Card>

        <Card v-else class="p-0">
            <div
                v-for="(feature, index) in localFeatures"
                :key="feature.name"
                class="flex items-center justify-between px-4 py-3"
                :class="{ 'border-t dark:border-dark-900': index > 0 }"
            >
                <div>
                    <p class="font-medium">{{ feature.name }}</p>
                </div>
                <Switch
                    :model-value="feature.active"
                    :disabled="togglingFeatures[feature.name] ?? false"
                    @update:model-value="toggleFeature(feature)"
                />
            </div>
        </Card>
    </div>
</template>
