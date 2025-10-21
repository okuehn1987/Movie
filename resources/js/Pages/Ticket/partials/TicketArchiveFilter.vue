<script setup lang="ts">
import { Customer } from '@/types/types';
import { CustomerProp, OperatingSiteProp, UserProp } from './ticketTypes';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
}>();

const filterForm = useForm({
    customer_id: null as Customer['id'] | null,
    assignees: [],
    start: null as string | null,
    end: null as string | null,
});

function resetFilter() {
    filterForm.reset();
    search();
}
function search() {
    router.reload({
        only: ['archiveTickets'],
        data: { page: 1, tab: 'archive', ...filterForm.data() },
        onSuccess: () => {
            showDialog.value = false;
        },
    });
}

const showDialog = ref(false);
</script>
<template>
    <v-dialog max-width="1000" v-model="showDialog">
        <template #activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" variant="flat" color="primary"><v-icon>mdi-filter</v-icon></v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card>
                <template #title>
                    <div class="d-flex justify-space-between align-center w-100">
                        <span>Im Archiv suchen</span>
                    </div>
                </template>
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click="
                            isActive.value = false;
                            filterForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form @submit.prevent="search">
                        <v-row>
                            <v-col cols="12">
                                <v-autocomplete
                                    clearable
                                    label="Kunde wählen"
                                    :items="customers.map(c => ({ value: c.id, title: c.name }))"
                                    v-model="filterForm.customer_id"
                                    :error-messages="filterForm.errors.customer_id"
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="12">
                                <v-autocomplete
                                    label="Zuweisung"
                                    multiple
                                    clearable
                                    chips
                                    :items="
                                        users.map(u => ({
                                            value: u.id,
                                            title: `${u.first_name} ${u.last_name}`,
                                        }))
                                    "
                                    :error-messages="filterForm.errors.assignees"
                                    v-model="filterForm.assignees"
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    type="date"
                                    label="von"
                                    v-model="filterForm.start"
                                    :error-messages="filterForm.errors.start"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field type="date" label="bis" v-model="filterForm.end" :error-messages="filterForm.errors.end"></v-text-field>
                            </v-col>
                        </v-row>
                        <div class="d-flex ga-2 justify-end">
                            <v-btn titel="Abbrechen" color="error" @click.stop="resetFilter">Filter zurücksetzen</v-btn>
                            <v-btn titel="Suchen" type="submit" color="primary">Tickets anzeigen</v-btn>
                        </div>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
