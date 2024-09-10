<template>
    <div>
        <v-dialog v-model="modelValue" @click.stop="updateValue">
            <v-container
                style="max-width: 1500px"
                class="d-flex justify-center"
            >
                <v-card
                    @click.stop="() => {}"
                    :link="false"
                    style="max-height: calc(100vh - 80px); overflow-y: auto"
                    :style="{ width: width ?? '100%' }"
                >
                    <v-card-title
                        class="text-center font-weight-bold py-6 text-h5 text-primary text-uppercase"
                        v-if="title"
                    >
                        {{ title }}
                    </v-card-title>
                    <div class="px-6 pb-6" :class="{ 'pt-6': !title }">
                        <slot></slot>
                    </div>
                </v-card>
            </v-container>
        </v-dialog>
    </div>
</template>
<script setup lang="ts">
import { toRefs } from "vue";

const props = defineProps<{
    modelValue: boolean;
    title?: string;
    width?: string;
}>();
const { modelValue } = toRefs(props);

const emit = defineEmits<{
    "update:modelValue": [value: boolean];
}>();
function updateValue() {
    emit("update:modelValue", !modelValue.value);
}
</script>
