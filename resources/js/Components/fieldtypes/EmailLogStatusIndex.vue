<script setup>
import { IndexFieldtype } from "@statamic/cms";
import EmailLogStatusBadge from "./EmailLogStatusBadge.vue";

const props = defineProps(IndexFieldtype.props);
</script>

<template>
    <EmailLogStatusBadge :status="value" />
</template>
