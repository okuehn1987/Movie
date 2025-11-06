<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ChatAssistant, ChatFile, RelationPick } from '@/types/types';
import { router, useForm } from '@inertiajs/vue3';
import { ref, toRefs } from 'vue';
import { DateTime } from 'luxon';
import { useDisplay } from 'vuetify';

const props = defineProps<{
    chatAssistant: Pick<ChatAssistant, 'id' | 'created_at' | 'updated_at' | 'monthly_cost_limit'> &
        RelationPick<'chatAssistant', 'chat_files', 'id'> & {
            current_monthly_cost: number;
        };
    files: Pick<ChatFile, 'created_at' | 'id' | 'name'>[];
}>();

const display = useDisplay();

const assistantForm = useForm({
    chatFile_ids: props.chatAssistant.chat_files.map(f => f.id),
    monthly_cost_limit: props.chatAssistant.monthly_cost_limit,
});

const { files: filesProp } = toRefs(props);

const fileForm = useForm<{ files: ChatFile[] }>({
    files: [],
});

const fileInputRef = ref<HTMLInputElement | null>(null);

function openFileInput() {
    fileInputRef.value?.click();
}

function submitFile(files: ChatFile[]) {
    fileForm.files = files;
    fileForm.post(route('file.store'), {
        onSuccess: () => {
            fileForm.reset();
        },
        onFinish: () => {
            if (fileInputRef.value) fileInputRef.value.value = '';
        },
    });
}

function deleteFile(file: Pick<ChatFile, 'id'>) {
    router.delete(route('file.destroy', file.id), {});
}

function limitColor(current: number, limit: number): string {
    const percentage = (current / limit) * 100;
    return percentage >= 90 ? 'error' : percentage >= 70 ? 'warning' : 'success';
}
</script>

<template>
    <AdminLayout :title="display.mdAndUp.value ? 'Ihr Interner-Such-Assistent' : 'Ihre ISA'" tooltip="TooltipChatAssistantIndex">
        <v-card class="mb-4">
            <v-card-title class="my-6">Einstellungen für ISA</v-card-title>
            <v-card-text>
                {{ display.mdAndUp }}
                <v-form @submit.prevent="assistantForm.patch(route('isa.update', { isa: chatAssistant.id }))">
                    <v-row>
                        <v-col cols="12">
                            <v-progress-linear
                                :title="
                                    ' ' +
                                    Math.floor((chatAssistant.current_monthly_cost / chatAssistant.monthly_cost_limit) * 100) +
                                    '% des monatlichen Kostenlimits sind erreicht'
                                "
                                rounded
                                :max="chatAssistant.monthly_cost_limit"
                                v-model="chatAssistant.current_monthly_cost"
                                :color="limitColor(chatAssistant.current_monthly_cost, chatAssistant.monthly_cost_limit)"
                                height="15"
                            ></v-progress-linear>
                        </v-col>
                        <v-col cols="12">
                            <v-text-field
                                v-model="assistantForm.monthly_cost_limit"
                                label="Monatliches Kostenlimit in €"
                                required
                                :errorMessages="assistantForm.errors.monthly_cost_limit"
                                hide-details
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12">
                            <v-select
                                color="primary"
                                label="Dateien"
                                :items="files.map(f => ({ title: f.name, value: f.id }))"
                                v-model="assistantForm.chatFile_ids"
                                multiple
                                chips
                                no-data-text="Es wurden keine Dateien hochgeladen"
                                :errorMessages="assistantForm.errors.chatFile_ids"
                                hide-details
                            ></v-select>
                        </v-col>
                        <v-col cols="12" class="text-end">
                            <v-btn color="primary" type="submit" :disabled="assistantForm.processing" :loading="assistantForm.processing">
                                Speichern
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
        </v-card>
        <v-card>
            <input
                hidden
                ref="fileInputRef"
                type="file"
                accept="application/pdf"
                multiple
                @change="event => submitFile((event.target as any).files)"
            />
            <v-data-table-virtual
                fixed-header
                no-data-text="Es wurden noch keine Dateien Hochgeladen"
                class="mx-auto"
                :headers="[
                    {
                        title: 'Dateiname',
                        value: 'name',
                    },
                    {
                        title: 'Hinzugefügt',
                        value: 'created_at',
                    },
                    {
                        title: 'Status',
                        value: 'is_used_text',
                    },
                    {
                        title: '',
                        value: 'actions',
                        align: 'end',
                    },
                ]"
                :items="
                    filesProp.map(file => {
                        const is_used = !!chatAssistant.chat_files.find(f => f.id == file.id);
                        return {
                            ...file,
                            created_at: DateTime.fromISO(file.created_at).toFormat('dd.MM.yyyy hh:mm'),
                            is_used,
                            is_used_text: is_used ? 'In Benutzung' : 'Nicht verwendet',
                        };
                    })
                "
            >
                <template v-slot:header.actions>
                    <v-btn title="Neue Datei hochladen" color="primary" @click.stop="openFileInput"><v-icon icon="mdi-plus"></v-icon></v-btn>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn :href="route('file.show', { file: item.id.toString() })" color="darkPrimary">Anzeigen</v-btn>
                    <v-btn
                        :disabled="item.is_used"
                        @click.stop="deleteFile(item)"
                        class="ms-2"
                        color="error"
                        style="pointer-events: auto"
                        :style="{ cursor: item.is_used ? 'default' : 'pointer' }"
                        :title="item.is_used ? 'Verwendete Dateien können nicht gelöscht werden' : 'Datei entfernen'"
                    >
                        <v-icon icon="mdi-delete"></v-icon>
                    </v-btn>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
