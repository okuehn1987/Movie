<script setup lang="ts">
import { CustomerNote, Tree } from '@/types/types';
import { filterTree } from '@/utils';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    customerNotes: CustomerNote[];
}>();

const mode = ref<'show' | 'edit'>('show');

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

const deleteNoteForm = useForm({
    noteId: null as CustomerNote['id'] | null,
});

const editNoteForm = useForm({
    noteId: null as CustomerNote['id'] | null,
    key: null as string | null,
    value: '',
});

function editNote(note: CustomerNote) {
    editNoteForm.noteId = note.id;
    editNoteForm.key = note.key;
    editNoteForm.value = note.value;
}
</script>
<template>
    <v-card title="Kundennotizen">
        <template #append>
            <v-btn
                icon="mdi-menu"
                color="primary"
                variant="text"
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
            ></v-btn>
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
                            editNoteForm.patch(route('customerNote.update', { customerNote: editNoteForm.noteId }), {
                                onSuccess: () => {
                                    editNoteForm.reset();
                                    mode = 'show';
                                },
                            })
                        "
                    >
                        <div class="d-flex ga-2 align-center">
                            <template v-if="item.type == 'complex'">
                                <v-text-field variant="outlined" hide-details v-model="editNoteForm.key"></v-text-field>
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
                                <v-text-field variant="outlined" hide-details v-model="editNoteForm.key"></v-text-field>

                                <v-text-field type="file" variant="outlined" hide-details v-model="editNoteForm.key"></v-text-field>
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
                    <span v-else-if="item.type == 'file'">{{ item.key }}</span>
                </template>
            </template>
            <template v-slot:append="{ item }">
                <div v-if="mode == 'edit' && !editNoteForm.noteId" class="d-flex ga-2">
                    <v-btn icon="mdi-pencil" @click.stop="editNote(item)" style="height: 40px" variant="text" color="primary"></v-btn>
                    <v-btn
                        icon="mdi-delete"
                        @click.stop="
                            () => {
                                deleteNoteForm.noteId = item.id;
                                deleteNoteForm.delete(route('customerNote.destroy', { customerNote: item.id }));
                            }
                        "
                        style="height: 40px"
                        variant="text"
                        color="error"
                        :loading="deleteNoteForm.processing && item.id == deleteNoteForm.noteId"
                    ></v-btn>
                </div>
            </template>
        </v-treeview>
    </v-card>
</template>
<style lang="scss" scoped></style>
