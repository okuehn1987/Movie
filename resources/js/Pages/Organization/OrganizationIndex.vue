<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Organization } from "@/types/types";
import { useForm } from "@inertiajs/vue3";
import { DateTime } from "luxon";

defineProps<{
    organizations: Organization[];
}>();

const organizationForm = useForm({
    organization_name: "",
    organization_street: "",
    organization_house_number: "",
    organization_address_suffix: "",
    organization_country: "",
    organization_city: "",
    organization_zip: "",
    organization_federal_state: "",
    first_name: "",
    last_name: "",
    email: "",
    password: "",
    date_of_birth: "",
});

function submit() {
    console.log("submit");
    organizationForm.post(route("organization.store"), {
        onSuccess: () => organizationForm.reset(),
    });
}
</script>
<template>
    <AdminLayout title="Organisationen">
        <v-container>
            <v-data-table-virtual
                hover
                :headers="[
                    { title: 'id', key: 'id' },
                    { title: 'owner_id', key: 'owner_id' },
                    { title: 'name', key: 'name' },
                    { title: 'created_at', key: 'created_at' },
                    { title: '', key: 'action', align: 'end' },
                ]"
                :items="
                    organizations.map((o) => ({
                        ...o,
                        created_at: DateTime.fromISO(o.created_at).toFormat(
                            'dd.MM.yyyy'
                        ),
                    }))
                "
            >
                <template v-slot:header.action>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card title="Organisation erstellen">
                                <v-form @submit.prevent="submit">
                                    <v-row
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Firmenname"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_name
                                                "
                                                v-model="
                                                    organizationForm.organization_name
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Straße"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_street
                                                "
                                                v-model="
                                                    organizationForm.organization_street
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Hausnummer"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_house_number
                                                "
                                                v-model="
                                                    organizationForm.organization_house_number
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Addresszusatz"
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_address_suffix
                                                "
                                                v-model="
                                                    organizationForm.organization_address_suffix
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Ort"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_city
                                                "
                                                v-model="
                                                    organizationForm.organization_city
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="PLZ"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_zip
                                                "
                                                v-model="
                                                    organizationForm.organization_zip
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Bundesland"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_federal_state
                                                "
                                                v-model="
                                                    organizationForm.organization_federal_state
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Land"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .organization_country
                                                "
                                                v-model="
                                                    organizationForm.organization_country
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Vorname "
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .first_name
                                                "
                                                v-model="
                                                    organizationForm.first_name
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Nachname "
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .last_name
                                                "
                                                v-model="
                                                    organizationForm.last_name
                                                "
                                            ></v-text-field> </v-col
                                        ><v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="E-Mail "
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .email
                                                "
                                                v-model="organizationForm.email"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                label="Password"
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .password
                                                "
                                                v-model="
                                                    organizationForm.password
                                                "
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                class="px-8"
                                                type="date"
                                                label="Geburtsdatum "
                                                required
                                                :error-messages="
                                                    organizationForm.errors
                                                        .date_of_birth
                                                "
                                                v-model="
                                                    organizationForm.date_of_birth
                                                "
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>

                                    <v-card-actions>
                                        <div
                                            class="d-flex justify-space-between w-100"
                                        >
                                            <v-btn
                                                color="error"
                                                variant="elevated"
                                                @click="isActive.value = false"
                                            >
                                                Abbrechen
                                            </v-btn>
                                            <v-btn
                                                type="submit"
                                                color="primary"
                                                variant="elevated"
                                                >Erstellen
                                            </v-btn>
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
                            route('organization.show', {
                                organization: item.id,
                            })
                        "
                    >
                        <v-icon icon="mdi-eye"></v-icon> </v-btn
                ></template>
            </v-data-table-virtual>
        </v-container>
    </AdminLayout>
</template>
