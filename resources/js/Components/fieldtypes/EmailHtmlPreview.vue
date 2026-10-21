<script setup>
import { ref } from "vue";
import { Fieldtype } from "@statamic/cms";

const props = defineProps(Fieldtype.props);
const emit = defineEmits(Fieldtype.emits);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const height = ref(400);

function resize(event) {
    height.value = event.target.contentDocument.documentElement.scrollHeight;
}
</script>

<template>
    <iframe
        v-if="value"
        :srcdoc="value"
        sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
        class="w-full rounded border bg-white"
        :style="{ height: `${height}px` }"
        @load="resize"
    />
    <p v-else class="text-sm text-gray-600">
        No email content was recorded.
    </p>
</template>
