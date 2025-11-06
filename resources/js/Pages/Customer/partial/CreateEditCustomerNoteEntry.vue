<script setup lang="ts">
import { ref } from 'vue';
import type { Customer, CustomerNoteFolder } from '@/types/types';

defineProps<{
    selectedFolder: CustomerNoteFolder['id'] | null;
    customer: Customer;
}>();

const openDialog = ref(false);

const createNoteEntryForm = useForm({
    type: null as string | null,
    title: null as string | null,
    value: null as string | null,
    file: null as File | null,
    selectedFolder: null as number | null,
});
</script>
<template>
    <v-dialog max-width="1000" v-model="openDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="primary" variant="flat"><v-icon>mdi-plus</v-icon></v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-form
                @submit.prevent="
                    createNoteEntryForm.selectedFolder = selectedFolder;
                    createNoteEntryForm.post(route('customer.customerNoteEntry.store', { customer: customer.id, selectedFolder: selectedFolder }), {
                        onSuccess: () => {
                            isActive.value = false;
                        },
                    });
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
                                    :model-value="createNoteEntryForm.type"
                                    @update:model-value="
                                        value => {
                                            createNoteEntryForm.type = value;
                                            createNoteEntryForm.value = '';
                                            createNoteEntryForm.file = null;
                                        }
                                    "
                                    :items="[
                                        { title: 'Einzelnotiz', value: 'primitive' },
                                        { title: 'Datei', value: 'file' },
                                    ]"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    label="Bezeichnung"
                                    v-model="createNoteEntryForm.title"
                                    :error-messages="createNoteEntryForm.errors.title"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" v-if="createNoteEntryForm.type == 'primitive'">
                                <v-text-field label="Inhalt" v-model="createNoteEntryForm.value" :error-messages="createNoteEntryForm.errors.value" />
                            </v-col>
                            <v-col cols="12" v-if="createNoteEntryForm.type == 'file'">
                                <v-file-input
                                    label="Dateiupload"
                                    v-model="createNoteEntryForm.file"
                                    :error-messages="createNoteEntryForm.errors.value"
                                />
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
