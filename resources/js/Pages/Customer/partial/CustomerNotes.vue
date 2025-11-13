<script setup lang="ts">
import { Customer, CustomerNoteEntry, CustomerNoteFolder, RelationPick, Tree } from '@/types/types';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { filterTree, mapTree, throttle, useMaxScrollHeight } from '@/utils';
import { DateTime } from 'luxon';
import CreateEditCustomerNoteFolder from './CreateEditCustomerNoteFolder.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import CreateEditCustomerNoteEntry from './CreateEditCustomerNoteEntry.vue';

const props = defineProps<{
    customerNoteFolders: Tree<Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>, 'sub_folders'>[];
    customerNoteEntries: Record<
        CustomerNoteFolder['id'],
        (CustomerNoteEntry & RelationPick<'customerNoteEntry', 'user', 'first_name' | 'last_name'>)[]
    >;
    customer: Customer;
}>();

const selectedFolder = ref<CustomerNoteFolder['id'] | null>(props.customerNoteFolders[0]?.id ?? null);

const loadedNotes = ref<CustomerNoteFolder['id'][]>(props.customerNoteFolders[0] ? [props.customerNoteFolders[0]?.id] : []);
const loading = ref(false);

const reload = throttle(() => {
    if (!selectedFolder.value || loadedNotes.value.includes(selectedFolder.value)) return;
    router.reload({
        data: { selectedFolder: selectedFolder.value },
        only: ['customerNoteEntries'],
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

function openFile(note: CustomerNoteEntry) {
    const fileURL = route('customerNoteEntry.getFile', { customerNoteEntry: note.id });
    window.open(fileURL, '_blank');
}

const height = useMaxScrollHeight(48);

const opened = ref([]);

const noteFolders = computed(() => {
    const folders = filterTree(
        mapTree(props.customerNoteFolders, 'sub_folders', (f, level) => ({
            name: f.name,
            id: f.id,
            level,
        })),
        'sub_folders',
        () => true,
    );
    return folders;
});

const selected = ref();

function onActivate(value: CustomerNoteFolder['id']) {
    selectedFolder.value = (value as CustomerNoteFolder['id'] | null) || null;
}
</script>
<template>
    <v-card :style="{ height }" title="Kundennotizen">
        <template #append></template>
        <v-divider></v-divider>
        <div class="d-flex">
            <div class="flex-shrink-1" style="max-width: 40%">
                <v-tabs direction="vertical">
                    <div class="ma-2" @click.stop="() => {}">
                        <CreateEditCustomerNoteFolder :customer></CreateEditCustomerNoteFolder>
                    </div>
                    <v-treeview
                        :indentLines="true"
                        v-model:opened="opened"
                        v-model:selected="selected"
                        item-children="sub_folders"
                        :items="noteFolders"
                        item-title="name"
                        item-value="id"
                        activatable
                        @update:activated="onActivate(($event as [CustomerNoteFolder['id']])[0])"
                    >
                        <template v-slot:prepend>
                            <v-icon icon="mdi-folder"></v-icon>
                        </template>
                        <template v-slot:append="{ item }">
                            <CreateEditCustomerNoteFolder v-if="item.level < 2" :createSubFolder="item" :customer></CreateEditCustomerNoteFolder>
                            <CreateEditCustomerNoteFolder :editNoteFolder="item" :customer="customer" />
                            <ConfirmDelete
                                :title="'Kategorie &quot;' + item.name + '&quot; löschen'"
                                :content="
                                    'Bist du dir sicher, dass du die Kategorie &quot;' + item.name + '&quot; mit all ihren Inhalten löschen möchtest?'
                                "
                                :route="route('customerNoteFolder.destroy', { customerNoteFolder: item.id })"
                            ></ConfirmDelete>
                        </template>
                    </v-treeview>
                </v-tabs>
            </div>
            <div class="flex-grow-1">
                <v-skeleton-loader v-if="loading" type="table"></v-skeleton-loader>
                <v-data-table
                    v-else-if="selectedFolder"
                    :items="
                        customerNoteEntries[selectedFolder]?.map(n => ({
                            ...n,
                            userName: n.user.first_name + ' ' + n.user.last_name + ' am ' + DateTime.fromISO(n.updated_at).toFormat('dd.MM.yyyy'),
                        }))
                    "
                    :headers="[
                        { title: 'Titel', value: 'title' },
                        { title: 'Inhalt', value: 'value' },
                        { title: 'letzte Bearbeitung', value: 'userName' },
                        { title: '', value: 'actions', width: '1px', align: 'end' },
                    ]"
                >
                    <template #header.actions>
                        <CreateEditCustomerNoteEntry :selectedFolder :customer="customer"></CreateEditCustomerNoteEntry>
                    </template>
                    <template #item.updated_at="{ item }">
                        {{ DateTime.fromISO(item.updated_at).toFormat('dd.MM.yyyy') }}
                    </template>
                    <template #item.value="{ item }">
                        <span v-if="item.type == 'text'">{{ item.value }}</span>
                        <v-btn v-else @click.stop="openFile(item)" color="primary" variant="text" :icon="'mdi-file-document-outline'"></v-btn>
                    </template>
                    <template #item.actions="{ item }">
                        <div class="d-flex">
                            <CreateEditCustomerNoteEntry :selectedFolder :customer="customer" :customerNoteEntry="item" />
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
