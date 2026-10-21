<script setup>
import { Fieldtype } from "@statamic/cms";
import EmailLogStatusBadge from "./EmailLogStatusBadge.vue";

const props = defineProps(Fieldtype.props);
const emit = defineEmits(Fieldtype.emits);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <EmailLogStatusBadge :status="value" />
</template>
