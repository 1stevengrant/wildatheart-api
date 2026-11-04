<script setup>
import { computed } from "vue";
import { Badge } from "@statamic/cms/ui";

const props = defineProps({
    status: { type: String, default: null },
});

const colors = {
    sent: "green",
    failed: "red",
};

const label = computed(
    () => props.status.charAt(0).toUpperCase() + props.status.slice(1)
);
</script>

<template>
    <Badge
        v-if="status"
        :text="label"
        :color="colors[status] ?? 'default'"
        pill
    />
</template>
