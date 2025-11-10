<script setup lang="ts">
import { Customer, CustomerNoteFolder, Tree } from '@/types/types';
import { ref, watch } from 'vue';

const props = defineProps<{
    customer: Customer;
    noteFolder?: Tree<Pick<CustomerNoteFolder, 'id' | 'name'>, 'sub_folders'>;
}>();

const openDialog = ref(false);

const createNoteFolderForm = useForm({
    name: null as string | null,
});

watch(openDialog, isOpen => {
    if (!isOpen) return createNoteFolderForm.reset();

    const folder = props.noteFolder;

    if (folder) {
        createNoteFolderForm.name = folder.name ?? '';
    } else {
        createNoteFolderForm.reset();
    }
});
</script>
<template>
    <v-dialog max-width="1000" v-model="openDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-if="noteFolder" v-bind="activatorProps" color="primary" variant="text" title="Kategorie bearbeiten" :icon="'mdi-pencil'"></v-btn>
            <v-btn v-else v-bind="activatorProps" color="primary" variant="flat" title="Neue Kategorie anlegen">Kategorie anlegen</v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-form
                @submit.prevent="
                    noteFolder
                        ? createNoteFolderForm.patch(route('customerNoteFolder.update', { customerNoteFolder: noteFolder.id }), {
                              onSuccess: () => {
                                  isActive.value = false;
                              },
                          })
                        : createNoteFolderForm.post(route('customer.customerNoteFolder.store', { customer: customer.id }), {
                              onSuccess: () => {
                                  createNoteFolderForm.reset();
                                  isActive.value = false;
                              },
                          })
                "
            >
                <v-card :title="noteFolder ? 'Kategorie &quot;' + noteFolder?.name + '&quot; bearbeiten' : 'Kategorie anlegen'">
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
    </v-dialog>
</template>
>
