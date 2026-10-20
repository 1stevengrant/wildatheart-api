<script setup>
import { nextTick, onMounted, ref } from "vue";
import { Head } from "@statamic/cms/inertia";
import { Button, Card, Header, Textarea } from "@statamic/cms/ui";
import axios from "axios";
import { renderMarkdown } from "../markdown.js";

const props = defineProps({
    conversations: Array,
    initialQuestion: String,
});

const starters = [
    "How many fires are published in the United States right now?",
    "Which fire hosts have no upcoming events?",
    "Attendee signups by event type this year",
    "Top 10 countries by number of published fires",
];

const conversationList = ref([...props.conversations]);
const activeConversationId = ref(null);
const messages = ref([]);
const draft = ref("");
const sending = ref(false);
const loadingConversation = ref(false);
const thread = ref(null);

async function scrollToBottom() {
    await nextTick();
    thread.value?.scrollTo({
        top: thread.value.scrollHeight,
        behavior: "smooth",
    });
}

function startNewConversation() {
    activeConversationId.value = null;
    messages.value = [];
    draft.value = "";
}

async function openConversation(id) {
    if (sending.value) {
        return;
    }

    loadingConversation.value = true;

    try {
        const response = await axios.get(
            cp_url(`command-center/ask/conversations/${id}`)
        );

        activeConversationId.value = id;
        messages.value = response.data.messages;
        await scrollToBottom();
    } catch (e) {
        Statamic.$toast.error("Could not load that conversation");
    } finally {
        loadingConversation.value = false;
    }
}

async function send(question = draft.value) {
    const message = question.trim();

    if (!message || sending.value) {
        return;
    }

    sending.value = true;
    draft.value = "";
    messages.value.push({
        id: `pending-${Date.now()}`,
        role: "user",
        content: message,
    });
    await scrollToBottom();

    try {
        const response = await axios.post(
            cp_url("command-center/ask/messages"),
            {
                message,
                conversation_id: activeConversationId.value,
            }
        );

        messages.value.push({
            id: `answer-${Date.now()}`,
            role: "assistant",
            content: response.data.answer,
        });

        rememberConversation(response.data.conversation_id, message);
    } catch (e) {
        messages.value.pop();
        draft.value = message;

        const status = e.response?.status;

        if (status === 429) {
            Statamic.$toast.error(
                "You've asked a lot of questions. Wait a minute and try again."
            );
        } else if (status === 422) {
            Statamic.$toast.error(
                e.response.data.message ?? "That question could not be sent"
            );
        } else {
            Statamic.$toast.error(
                "Something went wrong answering that question"
            );
        }
    } finally {
        sending.value = false;
        await scrollToBottom();
    }
}

function rememberConversation(id, firstMessage) {
    activeConversationId.value = id;

    const existing = conversationList.value.find(
        (conversation) => conversation.id === id
    );
    const others = conversationList.value.filter(
        (conversation) => conversation.id !== id
    );

    conversationList.value = [
        existing ?? { id, title: firstMessage.slice(0, 60) },
        ...others,
    ];
}

function onKeydown(event) {
    if (event.key === "Enter" && !event.shiftKey) {
        event.preventDefault();
        send();
    }
}

onMounted(() => {
    if (props.initialQuestion) {
        send(props.initialQuestion);
    }
});
</script>

<template>
    <Head title="Ask the Platform" />

    <Header title="Ask the Platform" icon="ai-spark">
        <template #actions>
            <Button
                text="New question"
                icon="plus"
                @click="startNewConversation"
            />
        </template>
    </Header>

    <div class="flex flex-col gap-6 lg:flex-row">
        <aside class="lg:w-64 lg:shrink-0">
            <Card class="p-2">
                <p
                    v-if="conversationList.length === 0"
                    class="p-2 text-sm text-gray-500"
                >
                    Your past conversations will appear here.
                </p>
                <button
                    v-for="conversation in conversationList"
                    :key="conversation.id"
                    type="button"
                    class="block w-full truncate rounded px-2 py-1.5 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
                    :class="{
                        'bg-gray-100 font-medium dark:bg-gray-800':
                            conversation.id === activeConversationId,
                    }"
                    @click="openConversation(conversation.id)"
                >
                    {{ conversation.title }}
                </button>
            </Card>
        </aside>

        <section class="flex min-w-0 flex-1 flex-col gap-4">
            <Card class="p-0">
                <div
                    ref="thread"
                    class="max-h-[65vh] min-h-[16rem] space-y-4 overflow-y-auto p-4"
                >
                    <div
                        v-if="loadingConversation"
                        class="text-sm text-gray-500"
                    >
                        Loading conversation...
                    </div>

                    <div v-else-if="messages.length === 0" class="space-y-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Ask a question about events, hosts or attendees.
                            Answers come from live platform data and can include
                            contact details.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="starter in starters"
                                :key="starter"
                                size="sm"
                                :text="starter"
                                @click="send(starter)"
                            />
                        </div>
                    </div>

                    <template v-else>
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="
                                message.role === 'user'
                                    ? 'flex justify-end'
                                    : ''
                            "
                        >
                            <div
                                v-if="message.role === 'user'"
                                class="max-w-[80%] whitespace-pre-wrap rounded-lg bg-blue-600 px-3 py-2 text-sm text-white"
                            >
                                {{ message.content }}
                            </div>
                            <div
                                v-else
                                class="ask-answer text-sm text-gray-900 dark:text-gray-100"
                                v-html="renderMarkdown(message.content)"
                            />
                        </div>

                        <div
                            v-if="sending"
                            class="flex items-center gap-2 text-sm text-gray-500"
                        >
                            <span
                                class="inline-block size-2 animate-pulse rounded-full bg-gray-400"
                            />
                            Looking that up...
                        </div>
                    </template>
                </div>
            </Card>

            <div @keydown="onKeydown">
                <Textarea
                    v-model="draft"
                    :rows="3"
                    elastic
                    :disabled="sending"
                    placeholder="e.g. How many fires started in Texas this year?"
                />
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Enter to send, Shift+Enter for a new line. Answers are
                        read-only and may take up to a minute.
                    </p>
                    <Button
                        variant="primary"
                        text="Ask"
                        :loading="sending"
                        :disabled="sending || !draft.trim()"
                        @click="send()"
                    />
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.ask-answer :deep(p),
.ask-answer :deep(ul),
.ask-answer :deep(ol),
.ask-answer :deep(table) {
    margin-bottom: 0.75rem;
}

.ask-answer :deep(ul) {
    list-style: disc;
    padding-left: 1.25rem;
}

.ask-answer :deep(ol) {
    list-style: decimal;
    padding-left: 1.25rem;
}

.ask-answer :deep(h1),
.ask-answer :deep(h2),
.ask-answer :deep(h3) {
    font-weight: 600;
    margin: 1rem 0 0.5rem;
}

.ask-answer :deep(table) {
    display: block;
    max-width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
}

.ask-answer :deep(th),
.ask-answer :deep(td) {
    border: 1px solid rgb(0 0 0 / 0.1);
    padding: 0.25rem 0.5rem;
    text-align: left;
    white-space: nowrap;
}

.ask-answer :deep(th) {
    font-weight: 600;
    background: rgb(0 0 0 / 0.03);
}

.ask-answer :deep(code) {
    font-size: 0.8em;
    word-break: break-word;
}

.ask-answer :deep(pre) {
    overflow-x: auto;
    padding: 0.5rem;
    background: rgb(0 0 0 / 0.04);
    border-radius: 0.25rem;
    margin-bottom: 0.75rem;
}

.ask-answer :deep(a) {
    text-decoration: underline;
}
</style>
