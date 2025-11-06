<script setup lang="ts">
import { Customer, CustomerNoteEntry, CustomerNoteFolder, RelationPick } from '@/types/types';
import { ref } from 'vue';

defineProps<{
    customerNoteFolders: Pick<CustomerNoteFolder, 'id' | 'customer_id' | 'name'>[];
    customerNoteEntries: Record<
        CustomerNoteFolder['id'],
        (CustomerNoteEntry & RelationPick<'customerNoteEntry', 'user', 'first_name' | 'last_name'>)[]
    >;
    customer: Customer;
    note?: Pick<CustomerNoteFolder, 'name' | 'id' | 'customer_id'>;
}>();

const openDialog = ref(false);

const createNoteFolderForm = useForm({
    name: null as string | null,
});
</script>
<template>
    <v-dialog max-width="1000" v-model="openDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-if="note" v-bind="activatorProps" color="primary" variant="text" title="Kategorie bearbeiten" :icon="'mdi-pencil'"></v-btn>
            <v-btn v-else v-bind="activatorProps" color="primary" variant="flat" title="Neue Kategorie anlegen">
                <v-icon>{{ 'mdi-plus' }}</v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-form
                @submit.prevent="
                    note
                        ? createNoteFolderForm.patch(route('customerNoteFolder.update', { customerNoteFolder: note.id }), {
                              onSuccess: () => {
                                  isActive.value = false;
                              },
                          })
                        : createNoteFolderForm.post(route('customer.customerNoteFolder.store', { customer: customer.id }), {
                              onSuccess: () => {
                                  isActive.value = false;
                              },
                          })
                "
            >
                <v-card :title="note ? 'Kategorie &quot;' + note?.name + '&quot; bearbeiten' : 'Kategorie anlegen'">
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
