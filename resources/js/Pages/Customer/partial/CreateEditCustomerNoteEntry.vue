<script setup lang="ts">
import type { Customer, CustomerNoteEntry, CustomerNoteFolder } from '@/types/types';
import { ref, watch } from 'vue';

const props = defineProps<{
    selectedFolder: CustomerNoteFolder['id'] | null;
    customer: Customer;
    customerNoteEntry?: Pick<CustomerNoteEntry, 'id' | 'type' | 'title' | 'value' | 'metadata'>;
}>();

const openDialog = ref(false);

const noteEntryForm = useForm({
    type: null as string | null,
    title: null as string | null,
    value: null as string | null,
    file: null as File | null,
    selectedFolder: props.selectedFolder,
    metadata: [] as string[],
});

watch(openDialog, isOpen => {
    if (!isOpen) return noteEntryForm.reset();

    const entry = props.customerNoteEntry;

    if (entry) {
        noteEntryForm.type = entry.type ?? '';
        noteEntryForm.title = entry.title ?? '';
        noteEntryForm.value = entry.value ?? '';
        noteEntryForm.metadata = entry.metadata ?? [];
    } else {
        noteEntryForm.reset();
    }
});

watch(
    () => props.customerNoteEntry,
    value => {
        if (value?.metadata) {
            noteEntryForm.metadata = [...value.metadata];
        }
    },
    { immediate: true },
);
</script>
<template>
    <v-dialog max-width="1000" v-model="openDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-if="customerNoteEntry" v-bind="activatorProps" color="primary" variant="text" icon="mdi-pencil"></v-btn>
            <v-btn v-else v-bind="activatorProps" color="primary" variant="flat"><v-icon>mdi-plus</v-icon></v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-form
                @submit.prevent="
                    customerNoteEntry
                        ? noteEntryForm
                              .transform(data => ({ ...data, _method: 'patch' }))
                              .post(route('customerNoteEntry.update', { customerNoteEntry: customerNoteEntry.id }), {
                                  onSuccess: () => {
                                      isActive.value = false;
                                  },
                              })
                        : noteEntryForm
                              .transform(data => ({ ...data, metadata: data.metadata.length == 0 ? null : data.metadata }))
                              .post(route('customer.customerNoteEntry.store', { customer: customer.id, selectedFolder: selectedFolder }), {
                                  onSuccess: () => {
                                      isActive.value = false;
                                  },
                              })
                "
            >
                <v-card :title="customerNoteEntry ? 'Kategorie &quot;' + customerNoteEntry?.title + '&quot; bearbeiten' : 'Notiz anlegen'">
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
                                    :model-value="noteEntryForm.type"
                                    @update:model-value="
                                        value => {
                                            noteEntryForm.type = value;
                                            noteEntryForm.value = '';
                                            noteEntryForm.file = null;
                                        }
                                    "
                                    :items="[
                                        { title: 'Einzelnotiz', value: 'text' },
                                        { title: 'Datei', value: 'file' },
                                    ]"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    label="Bezeichnung"
                                    v-model="noteEntryForm.title"
                                    :error-messages="noteEntryForm.errors.title"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" v-if="noteEntryForm.type == 'text'">
                                <v-textarea
                                    label="Inhalt"
                                    v-model="noteEntryForm.value"
                                    max-rows="14"
                                    auto-grow
                                    :error-messages="noteEntryForm.errors.value"
                                />
                                <v-alert v-if="customerNoteEntry?.type == 'file' && noteEntryForm.type == 'text'" type="warning">
                                    Wird der Anlegungstyp auf „Einzelnotiz“ geändert, wird beim Speichern die zuvor vorhandene Datei gelöscht.
                                </v-alert>
                            </v-col>
                            <v-col cols="12" v-if="noteEntryForm.type == 'file'">
                                <v-file-input label="Dateiupload" v-model="noteEntryForm.file" :error-messages="noteEntryForm.errors.value" />
                            </v-col>
                            <v-col cols="12">
                                <v-combobox
                                    label="Suchbegriffe"
                                    autocomplete="off"
                                    multiple
                                    chips
                                    closable-chips
                                    clearable
                                    v-model="noteEntryForm.metadata"
                                    :error-messages="noteEntryForm.errors.metadata"
                                ></v-combobox>
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
<style scoped></style>
