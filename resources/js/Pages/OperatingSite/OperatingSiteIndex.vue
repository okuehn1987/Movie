<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { OperatingSite } from '@/types/types';
import { fillNullishValues } from '@/utils';
import { useForm } from '@inertiajs/vue3';

defineProps<{
    operatingSites: OperatingSite[];
    success?: string;
}>();

const operatingSiteForm = useForm({
    address_suffix: '',
    city: '',
    country: '',
    email: '',
    fax: '',
    federal_state: '',
    house_number: '',
    is_head_quarter: false,
    phone_number: '',
    street: '',
    zip: '',
    name: '',
});

function submit() {
    operatingSiteForm.post(route('operatingSite.store'), {
        onSuccess: () => operatingSiteForm.reset(),
    });
}
</script>
<template>
    <AdminLayout title="Betriebsorte">
        <v-container>
            <v-alert class="mb-4" v-if="operatingSiteForm.wasSuccessful" closable color="success">Betriebsstätte wurde erfolgreich erstellt.</v-alert>
            <v-data-table-virtual
                hover
                :headers="[
                    { title: '#', key: 'id' },
                    { title: 'Name', key: 'name' },
                    { title: 'Straße', key: 'street' },
                    { title: 'Hausnummer', key: 'house_number' },
                    { title: 'Postleitzahl', key: 'zip' },
                    { title: 'Bundesland', key: 'federal_state' },
                    { title: 'Land', key: 'country' },
                    { title: 'Hauptsitz', key: 'is_head_quarter' },
                    { title: 'Email', key: 'email' },
                    { title: 'Telefonnummer', key: 'phone_number' },
                    { title: 'Fax', key: 'fax' },
                    { title: '', key: 'action', align: 'end' },
                ]"
                :items="operatingSites.map(o => fillNullishValues(o))"
            >
                <template v-slot:header.action>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card>
                                <v-form @submit.prevent="submit">
                                    <v-toolbar color="primary" class="mb-4" title="Betriebsstätte erstellen"></v-toolbar>
                                    <v-card-text>
                                        <h3>Kontaktinformationen</h3>
                                        <v-divider></v-divider>
                                    </v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Name"
                                                required
                                                :error-messages="operatingSiteForm.errors.street"
                                                v-model="operatingSiteForm.name"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="E-Mail "
                                                required
                                                :error-messages="operatingSiteForm.errors.email"
                                                v-model="operatingSiteForm.email"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Telefonnummer"
                                                required
                                                :error-messages="operatingSiteForm.errors.email"
                                                v-model="operatingSiteForm.phone_number"
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>
                                    <v-card-text>
                                        <h3>Adresse</h3>
                                        <v-divider></v-divider>
                                    </v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Straße"
                                                required
                                                :error-messages="operatingSiteForm.errors.street"
                                                v-model="operatingSiteForm.street"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Hausnummer"
                                                required
                                                :error-messages="operatingSiteForm.errors.house_number"
                                                v-model="operatingSiteForm.house_number"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Addresszusatz"
                                                :error-messages="operatingSiteForm.errors.address_suffix"
                                                v-model="operatingSiteForm.address_suffix"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Ort"
                                                required
                                                :error-messages="operatingSiteForm.errors.city"
                                                v-model="operatingSiteForm.city"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="PLZ"
                                                required
                                                :error-messages="operatingSiteForm.errors.zip"
                                                v-model="operatingSiteForm.zip"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Bundesland"
                                                required
                                                :error-messages="operatingSiteForm.errors.federal_state"
                                                v-model="operatingSiteForm.federal_state"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                variant="underlined"
                                                class="px-8"
                                                label="Land"
                                                required
                                                :error-messages="operatingSiteForm.errors.country"
                                                v-model="operatingSiteForm.country"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" md="6">
                                            <v-checkbox class="px-5" v-model="operatingSiteForm.is_head_quarter" label="Hauptsitz?"></v-checkbox>
                                        </v-col>
                                    </v-row>

                                    <v-card-actions>
                                        <div class="d-flex justify-end w-100">
                                            <v-btn color="error" variant="elevated" class="me-2" @click="isActive.value = false">Abbrechen</v-btn>
                                            <v-btn type="submit" color="primary" variant="elevated">Erstellen</v-btn>
                                        </div>
                                    </v-card-actions>
                                </v-form>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:item.action="{ item }">
                    <v-btn
                        color="primary"
                        :href="
                            route('operatingSite.show', {
                                operatingSite: item.id,
                            })
                        "
                    >
                        <v-icon icon="mdi-eye"></v-icon>
                    </v-btn>
                </template>
            </v-data-table-virtual>
        </v-container>
    </AdminLayout>
</template>
