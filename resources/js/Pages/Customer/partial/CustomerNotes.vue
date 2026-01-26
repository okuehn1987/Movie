<script setup lang="ts">
import { Customer, CustomerNoteEntry, CustomerNoteFolder, RelationPick, Tree } from '@/types/types';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { filterTree, mapTree, throttle, useClickHandler, useMaxScrollHeight } from '@/utils';
import { DateTime } from 'luxon';
import CreateEditCustomerNoteFolder from './CreateEditCustomerNoteFolder.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import CreateEditCustomerNoteEntry from './CreateEditCustomerNoteEntry.vue';

const props = defineProps<{
    customerNoteFolders: Tree<Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>, 'sub_folders'>[];
    customerNoteEntries: (CustomerNoteEntry & RelationPick<'customerNoteEntry', 'user', 'first_name' | 'last_name'>)[];
    customer: Customer;
}>();

const selectedFolder = ref<CustomerNoteFolder['id'] | null>(props.customerNoteFolders[0]?.id ?? null);

const loading = ref(false);

const reload = throttle(() => {
    if (!selectedFolder.value) return;
    router.reload({
        data: { selectedFolder: selectedFolder.value },
        only: ['customerNoteEntries'],
        onStart: () => {
            if (selectedFolder.value) {
                loading.value = true;
            }
        },
        onFinish: () => (loading.value = false),
    });
}, 500);
watch(selectedFolder, reload);

const height = useMaxScrollHeight(48);

const opened = ref<CustomerNoteFolder['id'][]>([]);

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

const { clickHandler: doubleClickHandler } = useClickHandler();
function onActivate(value: CustomerNoteFolder['id']) {
    doubleClickHandler(
        () => {
            selectedFolder.value = (value as CustomerNoteFolder['id'] | null) ?? selectedFolder.value;
        },
        state => {
            if (opened.value.includes(value ?? state)) opened.value = opened.value.filter(e => e != (value ?? state));
            else opened.value.push(value ?? state);
        },
        value,
    );
}
</script>
<template>
    <v-card :style="{ height }" title="Kundennotizen">
        <template #append></template>
        <v-divider></v-divider>
        <v-alert color="warning" v-if="customerNoteFolders.length == 0">
            <div class="d-flex align-center">
                <v-icon icon="mdi-information" class="me-2"></v-icon>
                <span class="me-2">Es existiert noch kein Ordner, hier kannst du einen anlegen:</span>
                <CreateEditCustomerNoteFolder :customer :hasFolders="false"></CreateEditCustomerNoteFolder>
            </div>
        </v-alert>
        <div v-else class="d-flex">
            <div class="flex-shrink-1" style="max-width: 40%">
                <div class="ma-2">
                    <CreateEditCustomerNoteFolder :customer :hasFolders="true"></CreateEditCustomerNoteFolder>
                </div>
                <v-treeview
                    :indentLines="true"
                    v-model:opened="opened"
                    item-children="sub_folders"
                    :items="noteFolders"
                    item-value="id"
                    activatable
                    @update:activated="onActivate(($event as [CustomerNoteFolder['id']])[0])"
                >
                    <template v-slot:prepend>
                        <v-icon icon="mdi-folder"></v-icon>
                    </template>
                    <template v-slot:title="{ item }">
                        <span style="user-select: none">{{ item.name }}</span>
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
            </div>
            <div class="flex-grow-1">
                <v-skeleton-loader v-if="loading" type="table"></v-skeleton-loader>
                <v-data-table
                    v-else-if="selectedFolder"
                    :items="
                        customerNoteEntries?.map(n => ({
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
                        <v-btn
                            v-else
                            color="primary"
                            variant="text"
                            :icon="'mdi-file-document-outline'"
                            :href="route('customerNoteEntry.getFile', { customerNoteEntry: item.id })"
                            :download="item.title"
                        ></v-btn>
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
