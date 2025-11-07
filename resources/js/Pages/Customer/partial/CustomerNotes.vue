<script setup lang="ts">
import { Customer, CustomerNoteEntry, CustomerNoteFolder, RelationPick } from '@/types/types';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { throttle } from '@/utils';
import { DateTime } from 'luxon';
import CreateEditCustomerNoteFolder from './CreateEditCustomerNoteFolder.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import CreateEditCustomerNoteEntry from './CreateEditCustomerNoteEntry.vue';

const props = defineProps<{
    customerNoteFolders: Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>[];
    customerNoteEntries: Record<
        CustomerNoteFolder['id'],
        (CustomerNoteEntry & RelationPick<'customerNoteEntry', 'user', 'first_name' | 'last_name'>)[]
    >;
    customer: Customer;
}>();

const selectedFolder = ref<CustomerNoteFolder['id'] | null>(props.customerNoteFolders[0]?.id ?? null);

const loadedNotes = ref<CustomerNoteFolder['id'][]>([]);
const loading = ref(false);

const reload = throttle(() => {
    if (!selectedFolder.value || loadedNotes.value.includes(selectedFolder.value)) return;
    router.reload({
        only: ['childNotes'],
        data: { selectedNote: selectedFolder.value },
        onStart: () => {
            if (selectedFolder.value) {
                loadedNotes.value.push(selectedFolder.value);
                loading.value = true;
            }
        },
        onError: () => (loadedNotes.value = loadedNotes.value.filter(e => e != selectedFolder.value)),
        onFinish: () => (loading.value = false),
    });
}, 500);
watch(selectedFolder, reload);

function isFile(value: string | null): boolean {
    if (!value) return false;
    return /\.(pdf|jpg|jpeg|png|gif|doc|docx|xls|xlsx|txt|csv|ppt|pptx|webp)$/i.test(value);
}

function openFile(note: CustomerNoteEntry) {
    const fileURL = route('customerNoteEntry.getFile', { customerNoteEntry: note.id });
    window.open(fileURL, '_blank');
}
</script>
<template>
    <v-card title="Kundennotizen">
        <template #append></template>
        <v-divider></v-divider>
        <div class="d-flex">
            <div class="flex-shrink-1" style="max-width: 40%">
                <v-tabs direction="vertical">
                    <div class="ma-2" @click.stop="() => {}">
                        <CreateEditCustomerNoteFolder
                            :categoryMode="'create'"
                            :customer
                            :customerNoteFolders
                            :customerNoteEntries
                        ></CreateEditCustomerNoteFolder>
                    </div>
                    <v-tab
                        v-for="note in customerNoteFolders"
                        :key="note.customer_id"
                        @click.stop="selectedFolder = note.id"
                        style="display: block; overflow: visible"
                    >
                        <div class="d-flex align-center justify-space-between w-100">
                            <span>{{ note.name }}</span>
                            <div>
                                <CreateEditCustomerNoteFolder
                                    :note="note"
                                    :customer="customer"
                                    :customerNoteFolders="customerNoteFolders"
                                    :customerNoteEntries="customerNoteEntries"
                                />
                                <ConfirmDelete
                                    :title="'Kategorie &quot;' + note.name + '&quot; löschen'"
                                    :content="
                                        'Bist du dir sicher, dass du die Kategorie &quot;' +
                                        note.name +
                                        '&quot; mit all ihren Inhalten löschen möchtest?'
                                    "
                                    :route="route('customerNoteFolder.destroy', { customerNoteFolder: note.id })"
                                ></ConfirmDelete>
                            </div>
                        </div>
                    </v-tab>
                </v-tabs>
            </div>
            <div class="flex-grow-1">
                <v-skeleton-loader v-if="loading" type="table"></v-skeleton-loader>
                <v-data-table
                    v-else-if="selectedFolder"
                    :items="
                        customerNoteEntries[selectedFolder]?.map(n => ({
                            ...n,
                            userName: n.user.first_name + ' ' + n.user.last_name,
                        }))
                    "
                    :headers="[
                        { title: 'Zuletzt aktualisiert', value: 'updated_at' },
                        { title: 'Titel', value: 'title' },
                        { title: 'Inhalt', value: 'value' },
                        { title: 'erstellt von', value: 'userName' },
                        { title: '', value: 'actions', width: '1px' },
                    ]"
                >
                    <template #header.actions>
                        <CreateEditCustomerNoteEntry :selectedFolder :customer="customer"></CreateEditCustomerNoteEntry>
                    </template>
                    <template #item.updated_at="{ item }">
                        {{ DateTime.fromISO(item.updated_at).toFormat("dd.MM.yyyy',' HH:mm 'Uhr'") }}
                    </template>
                    <template #item.value="{ item }">
                        <span v-if="!isFile(item.value)">{{ item.value }}</span>
                        <v-btn v-if="isFile(item.value)" @click.stop="openFile(item)" color="primary" variant="flat">
                            <v-icon>mdi-eye</v-icon>
                        </v-btn>
                    </template>
                    <template #item.actions="{ item }">
                        <div class="d-flex">
                            <CreateEditCustomerNoteEntry :selectedFolder :customer="customer" :item />
                            <ConfirmDelete
                                :title="'Notiz &quot;' + item.title + '&quot; löschen'"
                                :content="'Bist du dir sicher, dass du die Notiz &quot;' + item.title + '&quot; löschen möchtest?'"
                                :route="route('customerNoteEntry.destroy', { customerNoteEntry: item.id })"
                            ></ConfirmDelete>
                        </div>
                    </template>
                </v-data-table>
            </div>
        </div>
    </v-card>
</template>
<style lang="scss" scoped></style>
