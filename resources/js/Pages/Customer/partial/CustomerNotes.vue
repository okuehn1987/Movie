<script setup lang="ts">
import { Customer, CustomerNote, Relations } from '@/types/types';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { throttle } from '@/utils';
import { DateTime } from 'luxon';

const props = defineProps<{
    customerNotes: Pick<CustomerNote, 'id' | 'key'>[];
    childNotes: Record<CustomerNote['id'], (CustomerNote & Pick<Relations<'customerNote'>, 'modifier'>)[]>;
    customer: Customer;
}>();

const mode = ref<'show' | 'edit'>('show');
const selectedNote = ref<CustomerNote['id'] | null>(props.customerNotes[0]?.id ?? null);

const loadedNotes = ref<CustomerNote['id'][]>([]);
const loading = ref(false);

const reload = throttle(() => {
    if (!selectedNote.value || loadedNotes.value.includes(selectedNote.value)) return;
    router.reload({
        only: ['childNotes'],
        data: { selectedNote: selectedNote.value },
        onStart: () => {
            if (selectedNote.value) {
                loadedNotes.value.push(selectedNote.value);
                loading.value = true;
            }
        },
        onError: () => (loadedNotes.value = loadedNotes.value.filter(e => e != selectedNote.value)),
        onFinish: () => (loading.value = false),
    });
}, 500);
watch(selectedNote, reload);

const openDialog = ref(false);

const editNoteForm = useForm({
    noteId: null as CustomerNote['id'] | null,
    key: null as string | null,
    value: '' as string | null,
    file: null as File | null,
});

const createNoteForm = useForm({
    type: null as string | null,
    key: null,
    value: '' as string | null,
    parent_id: null as CustomerNote['id'] | null,
    file: null as File | null,
});

function editNote(note: CustomerNote) {
    editNoteForm.noteId = note.id;
    editNoteForm.key = note.key;
    editNoteForm.value = note.value;
    editNoteForm.file = note.file;
}
</script>
<template>
    <v-card title="Kundennotizen">
        <template #append>
            <v-dialog max-width="1000" v-model="openDialog">
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
                                    createNoteForm.reset('parent_id');
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
                                            createNoteForm.reset('parent_id');
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
                                            v-model="createNoteForm.key"
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
            </v-dialog>
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
        <v-row>
            <v-col cols="12" md="2">
                <v-tabs direction="vertical">
                    <v-tab v-for="note in customerNotes" :key="note.id" @click.stop="selectedNote = note.id">{{ note.key }}</v-tab>
                </v-tabs>
            </v-col>
            <v-col cols="12" md="10">
                <v-skeleton-loader v-if="loading" type="table"></v-skeleton-loader>
                <v-data-table
                    v-else-if="selectedNote"
                    :items="
                        childNotes[selectedNote]?.map(n => ({
                            ...n,
                            modifier: {
                                ...n.modifier,
                                name: n.modifier.first_name + ' ' + n.modifier.last_name,
                            },
                        }))
                    "
                    :headers="[
                        { title: 'Zuletzt aktualisiert', value: 'updated_at' },
                        { title: 'Inhalt', value: 'value' },
                        { title: 'erstellt von', value: 'modifier.name' },
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
            </v-col>
        </v-row>
    </v-card>
</template>
<style lang="scss" scoped></style>
