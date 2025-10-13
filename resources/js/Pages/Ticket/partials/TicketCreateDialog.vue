<script setup lang="ts">
import { PRIORITIES } from '@/types/types';
import { CustomerProp, OperatingSiteProp, UserProp } from './ticketTypes';
import { usePage } from '@inertiajs/vue3';
import { Ref } from 'vue';

const props = defineProps<{
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
}>();

const ticketForm = useForm({
    title: '',
    description: '',
    priority: 'medium',
    customer_id: props.customers.length == 1 ? props.customers.map(c => ({ title: c.name, value: c.id }))[0] : null,
    assignees: [usePage().props.auth.user.id],
    start: null as string | null,
    duration: '00:00',
    resources: '',
    appointment_at: null as string | null,
    files: [] as File[],
    tab: 'ticket' as 'ticket' | 'expressTicket',
});

function submit(isActive: Ref<boolean>) {
    ticketForm.post(route('ticket.store'), {
        onSuccess: () => {
            ticketForm.reset();
            isActive.value = false;
        },
    });
}
</script>
<template>
    <v-dialog max-width="600px">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn title="Auftrag erstellen" v-bind="activatorProps" color="primary">
                <v-icon icon="mdi-plus"></v-icon>
            </v-btn>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card title="Auftrag hinzufügen">
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-tabs v-model="ticketForm.tab">
                    <v-tab class="w-50" value="ticket">Auftrag</v-tab>
                    <v-tab class="w-50" value="expressTicket">Einzelauftrag</v-tab>
                </v-tabs>
                <v-card-text style="max-height: 750px; overflow-y: auto">
                    <v-form @submit.prevent="submit(isActive)">
                        <v-tabs-window v-model="ticketForm.tab">
                            <v-tabs-window-item value="ticket">
                                <v-row>
                                    <v-col cols="12" md="8">
                                        <v-autocomplete
                                            label="Kunde wählen"
                                            required
                                            :items="customers.map(c => ({ value: c.id, title: c.name }))"
                                            v-model="ticketForm.customer_id"
                                            :error-messages="ticketForm.errors.customer_id"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-select
                                            :items="PRIORITIES"
                                            label="Priorität"
                                            required
                                            :error-messages="ticketForm.errors.priority"
                                            v-model="ticketForm.priority"
                                        >
                                            <template v-slot:selection="{ item }">
                                                {{ item.raw.title }}
                                                <v-icon class="ms-2" :icon="item.raw.icon" :color="item.raw.color"></v-icon>
                                            </template>
                                            <template v-slot:item="{ props: itemProps, item }">
                                                <v-list-item v-bind="itemProps">
                                                    <template #append>
                                                        <v-icon :icon="item.raw.icon" :color="item.raw.color"></v-icon>
                                                    </template>
                                                </v-list-item>
                                            </template>
                                        </v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-autocomplete
                                            label="Zuweisung"
                                            required
                                            multiple
                                            chips
                                            :items="
                                                users.map(u => ({
                                                    value: u.id,
                                                    title: `${u.first_name} ${u.last_name}`,
                                                    props: { subtitle: u.job_role ?? '' },
                                                }))
                                            "
                                            :error-messages="ticketForm.errors.assignees"
                                            v-model="ticketForm.assignees"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" md="7">
                                        <v-text-field
                                            label="Betreff"
                                            required
                                            :error-messages="ticketForm.errors.title"
                                            v-model="ticketForm.title"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="5">
                                        <v-text-field
                                            label="Termin"
                                            type="datetime-local"
                                            v-model="ticketForm.appointment_at"
                                            :error-Messages="ticketForm.errors.appointment_at"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-textarea
                                            label="Beschreibung"
                                            reqiuired
                                            :error-messages="ticketForm.errors.description"
                                            v-model="ticketForm.description"
                                        ></v-textarea>
                                    </v-col>
                                </v-row>
                            </v-tabs-window-item>
                            <v-tabs-window-item value="expressTicket">
                                <v-row>
                                    <v-col cols="12">
                                        <v-autocomplete
                                            label="Kunde wählen"
                                            required
                                            :items="customers.map(c => ({ value: c.id, title: c.name }))"
                                            v-model="ticketForm.customer_id"
                                            :error-messages="ticketForm.errors.customer_id"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field
                                            label="Start"
                                            type="datetime-local"
                                            :model-value="ticketForm.start"
                                            @update:model-value="val => (ticketForm.start = val)"
                                            :error-messages="ticketForm.errors.start"
                                            required
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field
                                            type="time"
                                            label="Auftragsdauer (in Stunden)"
                                            min="0"
                                            v-model="ticketForm.duration"
                                            :error-messages="ticketForm.errors.duration"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-text-field
                                            label="Betreff"
                                            required
                                            :error-messages="ticketForm.errors.title"
                                            v-model="ticketForm.title"
                                        ></v-text-field>
                                    </v-col>

                                    <v-col cols="12">
                                        <v-textarea
                                            auto-grow
                                            label="Beschreibung"
                                            required
                                            :error-messages="ticketForm.errors.description"
                                            v-model="ticketForm.description"
                                        ></v-textarea>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-textarea
                                            rows="1"
                                            label="Ressourcen"
                                            :error-messages="ticketForm.errors.resources"
                                            v-model="ticketForm.resources"
                                            auto-grow
                                        ></v-textarea>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-file-input
                                            label="Dateien anhängen"
                                            multiple
                                            v-model="ticketForm.files"
                                            :error-messages="ticketForm.errors['files.0']"
                                            accept="image/jpg, image/png, image/jpeg, image/avif, image/tiff, image/svg+xml, application/pdf"
                                        ></v-file-input>
                                    </v-col>
                                </v-row>
                            </v-tabs-window-item>
                        </v-tabs-window>
                        <v-col cols="12" class="text-end"><v-btn type="submit" color="primary">Speichern</v-btn></v-col>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
