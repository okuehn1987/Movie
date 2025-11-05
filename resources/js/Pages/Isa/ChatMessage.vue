<script setup lang="ts">
import { ChatMessage } from '@/types/types';
import { DateTime } from 'luxon';
import sanitizeHtml from 'sanitize-html';
import showdown from 'showdown';
import { computed, nextTick, ref, toRefs, watchEffect } from 'vue';

const converter = new showdown.Converter();
converter.setOption('tables', true);
converter.setOption('headerLevelStart', 2);

const props = defineProps<{
    chatMessage: Pick<ChatMessage, 'id' | 'role' | 'msg' | 'created_at'>;
    isLoading?: boolean;
}>();

const { chatMessage } = toRefs(props);
const isUser = chatMessage.value.role == 'user';

const ANNOTATION_REGEX = /(【.*?】)/g;

const parsedMarkdownMessage = computed(() =>
    sanitizeHtml(
        converter.makeHtml(chatMessage.value.msg.replace(/( )+\./g, '.')).replace(/<a[^>]*href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/g, (_, g1, g2) => {
            return `<a href="${g1}" target="_blank" rel="noopener noreferrer">${g2}</a>`;
        }),
    ).replace(ANNOTATION_REGEX, ''),
);

const loadingDots = ref<HTMLElement | null>(null);

watchEffect(() => {
    if (props.isLoading) {
        nextTick(() => {
            loadingDots.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
});
</script>
<template>
    <v-list-item class="mb-2 p-0 rounded">
        <div class="d-flex w-100 align-center">
            <v-img style="flex-grow: 0; flex-basis: 30px" v-if="!isUser" width="30px" height="30px" src="/img/Isa-klein.png" class="me-2"></v-img>
            <div v-else class="flex-grow-1" style="min-width: 60px"></div>
            <div
                class="pa-2 rounded speech flex-shrink-1 me-2 text-black"
                :class="{
                    left: !isUser,
                    right: isUser,
                }"
            >
                <span class="message-content" style="white-space: pre-wrap" v-html="parsedMarkdownMessage"></span>
                <div v-if="isLoading" ref="loadingDots" indeterminate class="loading-dots">
                    <div v-for="i in 3" :key="i" class="dot" style="background-color: #fefefe"></div>
                </div>
                <template v-else>
                    <div class="float-end d-flex mt-1 mb-n1 text-caption ps-3" style="color: grey; vertical-align: bottom">
                        <span class="ms-2" style="margin-top: 2px; color: #aaaaaa">
                            {{ DateTime.fromISO(chatMessage.created_at).toFormat('HH:mm') }}
                        </span>
                    </div>
                </template>
            </div>
            <div style="flex-grow: 0; flex-basis: 20px" v-if="!isUser" width="30px" height="30px" class="ms-2 flex-0"></div>
        </div>
    </v-list-item>
</template>

<style scoped lang="scss">
.speech {
    position: relative;
    --triangle-size: 7px;
    max-width: 90%;
}
.speech::after {
    content: '';
    border: var(--triangle-size) solid transparent;
    position: absolute;
}
.left {
    background-color: #efefff;
}
.left.speech::after {
    border-left: 0;
    left: calc(-1 * var(--triangle-size));
    margin-top: calc(-1 * var(--triangle-size));
    top: 50%;
    content: '';
    border-right-color: #efefff;
}
.right {
    background-color: #e9ffd5;
}
.right.speech::after {
    border-right: 0;
    right: calc(-1 * var(--triangle-size));
    top: 50%;
    margin-top: calc(-1 * var(--triangle-size));

    border-left-color: #e9ffd5;
}

.loading-dots {
    display: flex;
    align-items: center;
}

.dot {
    width: 10px;
    height: 10px;
    margin: 0 5px;
    border-radius: 50%;
    animation: bounce 1.5s infinite;
}

.dot:nth-child(2) {
    animation-delay: 0.3s;
}

.dot:nth-child(3) {
    animation-delay: 0.6s;
}

@keyframes bounce {
    0%,
    80%,
    100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}
</style>
<style lang="scss">
.message-content {
    a {
        color: rgb(var(--v-theme-primary)) !important;
    }
    word-break: break-word;
    ol,
    ul {
        padding-inline-start: 35px !important;
    }
    pre:has(> code) {
        overflow-x: auto;
    }
}
</style>
