<script setup lang="ts">
import type { Chat, ChatMessage } from '@/types/types';
import ChatMessageComp from './ChatMessage.vue';
import { useForm, router } from '@inertiajs/vue3';
import { watch, toRefs, ref, nextTick } from 'vue';
import { DateTime } from 'luxon';

const props = defineProps<{
    chat: Pick<Chat, 'id'> & {
        chat_messages: Pick<ChatMessage, 'id' | 'role' | 'created_at' | 'msg'>[];
    };
    reachedMonthlyTokenLimit: boolean;
    showChat: boolean;
}>();

const { chat: chatProp } = toRefs(props);

const chat = ref(chatProp.value);
watch(chatProp, value => (chat.value = value));
watch(
    () => props.showChat,
    () => {
        if (props.showChat) setTimeout(scrollToBottom, 200);
    },
);

const chatMessageForm = useForm({
    msg: '',
});

function submitChatMessage() {
    chatMessageForm.clearErrors();
    chatMessageForm.post(route('isa.message'), {
        preserveScroll: true,
    });
    chat.value?.chat_messages.push({
        id: -1 as ChatMessage['id'],
        created_at: DateTime.local().toISO() as ChatMessage['created_at'],
        msg: chatMessageForm.msg.replaceAll('\n', '<br>'),
        role: 'user',
    });
    chatMessageForm.reset();
}

const currentPartialChatResponse = ref('');
watch(
    chat,
    () => {
        currentPartialChatResponse.value = '';
        nextTick(scrollToBottom);
    },
    { immediate: true, deep: true },
);
watch(currentPartialChatResponse, scrollToBottom, { immediate: true });

function scrollToBottom() {
    const elem = document.getElementById('messages');
    if (elem) elem.scrollTo({ top: elem.scrollHeight, behavior: 'instant' });
}

// if (chat.value) {
//     window.Echo.channel('chat.' + chat.value.id).listen('.ChatMessageDelta', ({ msg }: { msg: string }) => {
//         currentPartialChatResponse.value += msg;
//     });
// }
</script>

<template>
    <div class="chat-details">
        <div id="messages" class="messages">
            <v-container style="max-width: 100%" class="m-0 px-0 pb-0 pt-2">
                <template v-if="chat && chat.chat_messages.length > 0">
                    <div
                        v-for="(chatMessage, index) of chat.chat_messages.filter(e => e.role != 'annotation')"
                        :key="index.toString()"
                        :id="'ChatMessage-' + chatMessage.id.toString()"
                    >
                        <ChatMessageComp :chatMessage="chatMessage" />
                    </div>
                </template>
                <template v-if="!chatMessageForm.processing">
                    <div class="bg-white">
                        <v-alert variant="tonal" color="error" v-if="$page.props.errors['openai']">
                            <div class="d-flex justify-space-between">
                                <span>
                                    {{ $page.props.errors['openai'] }}
                                </span>
                                <v-btn
                                    v-if="$page.props.errors['error_code'] == 'OPENAI_SERVER_ERROR'"
                                    color="primary"
                                    @click.stop="router.post(route('chat.retryLastRun', { chat: chat.id }))"
                                >
                                    Erneut senden
                                </v-btn>
                            </div>
                        </v-alert>
                        <v-alert variant="tonal" color="error" v-if="$page.props.errors['msg']">{{ $page.props.errors['msg'] }}</v-alert>
                        <v-alert variant="tonal" color="error" v-if="reachedMonthlyTokenLimit">
                            Dieser Service ist derzeit leider nicht verfügbar, bitte wenden Sie sich an den technischen Support
                        </v-alert>
                    </div>
                </template>
                <ChatMessageComp
                    v-if="chatMessageForm.processing || currentPartialChatResponse"
                    :chatMessage="
                        {
                            msg: currentPartialChatResponse,
                            role: 'assistant',
                            id: -1,
                        } as ChatMessage
                    "
                    :isLoading="true"
                    :reached_token_limit="null"
                />
            </v-container>
        </div>
        <form class="composer mt-2 w-100" @submit.prevent="submitChatMessage">
            <v-textarea
                label="Geben Sie hier Ihre Frage ein..."
                v-model="chatMessageForm.msg"
                required
                auto-grow
                maxlength="2000"
                rows="1"
                hide-details
                autocomplete="none"
                :disabled="chatMessageForm.processing || reachedMonthlyTokenLimit"
                :error-messages="chatMessageForm.errors.msg"
                @keydown.enter.ctrl.exact.prevent="submitChatMessage"
                style="user-select: unset"
                title="Nachricht senden (Strg + Enter)"
            >
                <template #append-inner>
                    <v-icon
                        style="opacity: 1"
                        color="primary"
                        icon="mdi-send-variant"
                        class="pa-6 ps-13 ms-n6"
                        @touchstart.stop.prevent="submitChatMessage"
                        @mousedown.stop="submitChatMessage"
                        @click.stop=""
                    ></v-icon>
                </template>
            </v-textarea>
        </form>
    </div>
</template>

<style scoped>
.chat-details {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.messages {
    flex: 1;
    overflow: auto;
    min-height: 0;
}

.composer {
    position: static;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
