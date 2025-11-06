<script setup lang="ts">
import { Customer, CustomerNoteEntry, CustomerNoteFolder, RelationPick } from '@/types/types';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { throttle } from '@/utils';
import { DateTime } from 'luxon';
import CreateEditCustomerNoteFolder from './CreateEditCustomerNoteFolder.vue';

const props = defineProps<{
    customerNoteFolders: Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>[];
    customerNoteEntries: Record<
        CustomerNoteFolder['id'],
        (CustomerNoteEntry & RelationPick<'customerNoteEntry', 'user', 'first_name' | 'last_name'>)[]
    >;
    customer: Customer;
}>();

const mode = ref<'show' | 'edit'>('show');
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

const editNoteForm = useForm({
    noteId: null as CustomerNoteEntry['id'] | null,
    title: null as string | null,
    value: null as string | null,
});

function editNote(note: CustomerNoteEntry) {
    editNoteForm.noteId = note.id;
    editNoteForm.title = note.title;
    editNoteForm.value = note.value;
}
</script>
<template>
    <v-card title="Kundennotizen">
        <template #append>
            <!-- <v-dialog max-width="1000" v-model="openDialog">
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn
                        v-bind="activatorProps"
                        append-icon="mdi-plus"
                        color="primary"
                        variant="flat"
                        class="mr-2"
                        v-if="mode == 'edit' && can('customer', 'update')"
                    >
                        Notiz anlegen
                    </v-btn>
                </template>
                <template v-slot:default="{ isActive }">
                    <v-form
                        @submit.prevent="
                            createNoteForm.post(route('customer.customerNote.store', { customer: customer.id }), {
                                onSuccess: () => {
                                    isActive.value = false;
                                },
                            })
                        "
                    >
                        <v-card title="Notiz anlegen">
                            <template #append>
                                <v-btn
                                    icon
                                    variant="text"
                                    @click.stop="
                                        () => {
                                            isActive.value = false;
                                        }
                                    "
                                >
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </template>
                            <v-card-text>
                                <v-row>
                                    <v-col cols="12">
                                        <v-select
                                            label="Anlegungstyp"
                                            :model-value="createNoteForm.type"
                                            @update:model-value="
                                                value => {
                                                    createNoteForm.type = value;
                                                    createNoteForm.value = '';
                                                    createNoteForm.file = null;
                                                }
                                            "
                                            :items="[
                                                { title: 'Einzelnotiz', value: 'primitive' },
                                                { title: 'Ordnerstruktur', value: 'complex' },
                                                { title: 'Datei', value: 'file' },
                                            ]"
                                            clearable
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" v-if="createNoteForm.type !== 'file'">
                                        <v-text-field
                                            label="Bezeichnung"
                                            v-model="createNoteForm.name"
                                            :error-messages="createNoteForm.errors.key"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" v-if="createNoteForm.type == 'primitive'">
                                        <v-text-field
                                            label="Inhalt"
                                            v-model="createNoteForm.value"
                                            :error-messages="createNoteForm.errors.value"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" v-if="createNoteForm.type == 'file'">
                                        <v-file-input
                                            label="Dateiupload"
                                            v-model="createNoteForm.file"
                                            :error-messages="createNoteForm.errors.file"
                                        ></v-file-input>
                                    </v-col>
                                    <v-col cols="12" class="text-end">
                                        <v-btn color="primary" variant="flat" type="submit">Speichern</v-btn>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-form>
                </template>
            </v-dialog> -->
            <v-btn
                append-icon="mdi-swap-horizontal"
                color="primary"
                variant="flat"
                @click.stop="
                    () => {
                        if (mode == 'show') {
                            mode = 'edit';
                            editNoteForm.reset();
                        } else {
                            mode = 'show';
                            editNoteForm.reset();
                        }
                    }
                "
                :disabled="!can('customer', 'update')"
            >
                {{ mode == 'show' ? 'Ansichtsmodus' : 'Bearbeitungsmodus' }}
            </v-btn>
        </template>
        <v-divider></v-divider>
        <div class="d-flex">
            <div class="flex-shrink-1" style="max-width: 40%">
                <!-- <v-dialog max-width="1000" v-model="openDialog">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="error" variant="flat" class="mr-2" v-if="mode == 'edit' && can('customer', 'update')">
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </template>
                    <template v-slot:default="{ isActive }">
                        <v-form
                            @submit.prevent="
                                createNoteFolderForm.post(route('customer.customerNoteFolder.store', { customer: customer.id }), {
                                    onSuccess: () => {
                                        isActive.value = false;
                                    },
                                })
                            "
                        >
                            <v-card title="Kategorie anlegen">
                                <template #append>
                                    <v-btn
                                        icon
                                        variant="text"
                                        @click.stop="
                                            () => {
                                                isActive.value = false;
                                            }
                                        "
                                    >
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </template>
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12">
                                            <v-text-field
                                                label="Bezeichnung"
                                                v-model="createNoteFolderForm.name"
                                                :error-messages="createNoteFolderForm.errors.name"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" class="text-end">
                                            <v-btn color="primary" variant="flat" type="submit">Speichern</v-btn>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-form>
                    </template>
                </v-dialog> -->
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
                            <CreateEditCustomerNoteFolder
                                :categoryMode="'edit'"
                                :note="note"
                                :customer="customer"
                                :customerNoteFolders="customerNoteFolders"
                                :customerNoteEntries="customerNoteEntries"
                            />
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
                        { title: 'Inhalt', value: 'value' },
                        { title: 'erstellt von', value: 'userName' },
                        { title: '', value: 'actions', width: '1px' },
                    ]"
                >
                    <template #item.updated_at="{ item }">
                        {{ DateTime.fromISO(item.updated_at).toFormat("dd.MM.yyyy',' HH:mm 'Uhr'") }}
                    </template>
                    <template #item.actions="{ item }">
                        <v-btn color="primary" variant="text" @click.stop="editNote(item)"><v-icon>mdi-pencil</v-icon></v-btn>
                    </template>
                </v-data-table>
            </div>
        </div>
    </v-card>
</template>
<style lang="scss" scoped></style>
