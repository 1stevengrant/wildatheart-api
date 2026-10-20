<script setup>
import { ref } from "vue";
import { Button, Input, Widget } from "@statamic/cms/ui";

const props = defineProps({
    url: String,
});

const question = ref("");

function ask() {
    const value = question.value.trim();

    window.location.href = value
        ? `${props.url}?q=${encodeURIComponent(value)}`
        : props.url;
}
</script>

<template>
    <Widget title="Ask the Platform" icon="ai-spark" :href="url">
        <form class="flex gap-2 p-4" @submit.prevent="ask">
            <Input
                v-model="question"
                class="flex-1"
                placeholder="e.g. Which fire hosts have no upcoming events?"
            />
            <Button type="submit" variant="primary" text="Ask" />
        </form>
    </Widget>
</template>
