<script setup lang="ts">
import { Customer } from '@/types/types';
import { CustomerProp, OperatingSiteProp, TicketProp, UserProp } from './ticketTypes';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
}>();

const form = useForm({
    customer_id: null as Customer['id'] | null,
    assignees: [],
    start: null as string | null,
    end: null as string | null,
});
</script>

<template>
    <v-dialog max-width="1000">
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
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form>
                        <v-row>
                            <v-col cols="12">
                                <v-autocomplete
                                    clearable
                                    label="Kunde wählen"
                                    :items="customers.map(c => ({ value: c.id, title: c.name }))"
                                    v-model="form.customer_id"
                                    :error-messages="form.errors.customer_id"
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="12">
                                <v-autocomplete
                                    label="Zuweisung"
                                    multiple
                                    clearable
                                    required
                                    chips
                                    :items="
                                        users.map(u => ({
                                            value: u.id,
                                            title: `${u.first_name} ${u.last_name}`,
                                            // props: { subtitle: u.job_role ?? '' },
                                        }))
                                    "
                                    :error-messages="form.errors.assignees"
                                    v-model="form.assignees"
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field type="date" label="von" v-model="form.start" :error-messages="form.errors.start"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field type="date" label="bis" v-model="form.end" :error-messages="form.errors.end"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-form>
                    <div class="d-flex ga-2 justify-end">
                        <v-btn titel="Abbrechen" color="error" @click.stop="form.reset()">Filter zurücksetzen</v-btn>
                        <v-btn titel="Suchen" color="primary">Tickets anzeigen</v-btn>
                    </div>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
