<script setup>
import { Head } from "@statamic/cms/inertia";
import { Header } from "@statamic/cms/ui";
import FeatureFlags from "../components/FeatureFlags.vue";
import AstroBuild from "../components/AstroBuild.vue";

defineProps({
    features: Array,
});
</script>

<template>
    <Head title="Command Center" />

    <Header title="Command Center" icon="dashboard" />

    <div class="space-y-6">
        <FeatureFlags :features="features" />
        <AstroBuild />
    </div>
</template>
