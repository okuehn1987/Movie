<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import { Customer, CustomerNote, Tree } from '@/types/types';
import { filterTree } from '@/utils';
import { computed, ref } from 'vue';

const props = defineProps<{
    customerNotes: CustomerNote[];
    customer: Customer;
}>();

const mode = ref<'show' | 'edit'>('show');

const openDialog = ref(false);

const noteTree = computed(() => {
    const map = new Map<number, any>();
    props.customerNotes.forEach(note => {
        map.set(note.id, { ...note, children: [] });
    });
    const tree: Tree<CustomerNote, 'children'>[] = [];
    map.forEach(note => {
        if (note.parent_id) {
            const parent = map.get(note.parent_id);
            if (parent) {
                parent.children.push(note);
            }
        } else {
            tree.push(note);
        }
    });
    return filterTree(tree, 'children', () => true);
});

const editNoteForm = useForm({
    noteId: null as CustomerNote['id'] | null,
    key: null as string | null,
    value: '',
    file: null as File | null,
});

const createNoteForm = useForm({
    type: null as string | null,
    key: null,
    value: '',
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
                    <v-btn v-bind="activatorProps" append-icon="mdi-plus" color="primary" variant="flat" class="mr-2">Notiz anlegen</v-btn>
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
                                    <v-col cols="12">
                                        <v-text-field
                                            label="Bezeichnung"
                                            v-model="createNoteForm.key"
                                            :error-messages="createNoteForm.errors.key"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" v-if="createNoteForm.type != 'file'">
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
            >
                {{ mode == 'show' ? 'Ansichtsmodus' : 'Bearbeitungsmodus' }}
            </v-btn>
        </template>
        <v-divider></v-divider>
        <v-treeview :items="noteTree" item-value="id" activatable open-on-click separateRoots :indent-lines="true">
            <template v-slot:prepend="{ item, isOpen }">
                <v-icon v-if="item.type == 'complex'" :icon="isOpen ? 'mdi-folder-open' : 'mdi-folder'"></v-icon>
            </template>
            <template v-slot:title="{ item }">
                <template v-if="editNoteForm.noteId == item.id && mode == 'edit'">
                    <v-form
                        @submit.prevent="
                            editNoteForm
                                .transform(data => ({ ...data, _method: 'patch' }))
                                .post(route('customerNote.update', { customerNote: editNoteForm.noteId }), {
                                    onSuccess: () => {
                                        editNoteForm.reset();
                                        mode = 'show';
                                    },
                                })
                        "
                    >
                        <div class="d-flex ga-2 align-center" @click.stop="() => {}">
                            <template v-if="item.type == 'complex'">
                                <v-row>
                                    <v-col cols="12">
                                        <v-text-field variant="outlined" hide-details v-model="editNoteForm.key"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" class="text-end">
                                        <v-btn type="submit" color="primary" class="me-2"><v-icon>mdi-close</v-icon></v-btn>
                                        <v-btn type="submit" color="primary"><v-icon>mdi-content-save</v-icon></v-btn>
                                    </v-col>
                                </v-row>
                            </template>
                            <template v-else-if="item.type == 'primitive'">
                                <v-row>
                                    <v-col cols="12" md="2">
                                        <v-text-field variant="outlined" hide-details v-model="editNoteForm.key"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="10">
                                        <v-textarea rows="1" variant="outlined" hide-details v-model="editNoteForm.value" auto-grow></v-textarea>
                                    </v-col>
                                    <v-col cols="12" class="justify-end d-flex">
                                        <v-btn type="submit" color="primary">Speichern</v-btn>
                                    </v-col>
                                </v-row>
                            </template>
                            <template v-else-if="item.type == 'file'">
                                <v-row>
                                    <v-col cols="12" md="2">
                                        <v-text-field variant="outlined" hide-details v-model="editNoteForm.key"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="10">
                                        <v-file-input
                                            label="Datei ersetzen"
                                            v-model="editNoteForm.file"
                                            :error-messages="createNoteForm.errors.file"
                                        ></v-file-input>
                                    </v-col>
                                    <v-col cols="12" class="justify-end d-flex">
                                        <v-btn type="submit" color="primary">Speichern</v-btn>
                                    </v-col>
                                </v-row>
                            </template>
                        </div>
                    </v-form>
                </template>
                <template v-else>
                    <span v-if="item.type == 'complex'">{{ item.key }}</span>
                    <span v-else-if="item.type == 'primitive'">
                        <v-row>
                            <v-col cols="12" md="2">{{ item.key }}:</v-col>
                            <v-col cols="12" md="10">
                                <pre>{{ item.value }}</pre>
                            </v-col>
                        </v-row>
                    </span>
                    <span v-else-if="item.type == 'file'">
                        <v-row>
                            <v-col cols="12" md="2">{{ item.key }}:</v-col>
                            <v-col cols="12" md="10">
                                <v-btn
                                    color="primary"
                                    :href="route('customerNote.getFile', { customerNote: item.id, file: item.value })"
                                    target="_blank"
                                >
                                    Öffnen
                                </v-btn>
                            </v-col>
                        </v-row>
                    </span>
                </template>
            </template>
            <template v-slot:append="{ item }">
                <div v-if="mode == 'edit' && !editNoteForm.noteId" class="d-flex ga-2">
                    <v-btn
                        v-if="item.type == 'complex'"
                        @click.stop="
                            {
                                openDialog = true;
                                createNoteForm.parent_id = item.id;
                            }
                        "
                        icon="mdi-plus"
                        color="primary"
                        variant="text"
                    ></v-btn>
                    <v-btn icon="mdi-pencil" @click.stop="editNote(item)" variant="text" color="primary"></v-btn>
                    <ConfirmDelete
                        title="Notiz löschen"
                        :route="route('customerNote.destroy', { customerNote: item.id })"
                        content="test"
                    ></ConfirmDelete>
                </div>
            </template>
        </v-treeview>
    </v-card>
</template>
<style lang="scss" scoped></style>
