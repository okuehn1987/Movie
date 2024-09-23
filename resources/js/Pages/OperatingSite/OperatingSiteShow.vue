<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { OperatingSite } from "@/types/types";
import { useForm } from "@inertiajs/vue3";
const props = defineProps<{
    operatingSite: OperatingSite;
    success?: string;
}>();

const siteForm = useForm({
    email: props.operatingSite.email,
    fax: props.operatingSite.fax,
    phone_number: props.operatingSite.phone_number,
    street: props.operatingSite.street,
    country: props.operatingSite.country,
    city: props.operatingSite.city,
    address_suffix: props.operatingSite.address_suffix,
    house_number: props.operatingSite.house_number,
    federal_state: props.operatingSite.federal_state,
    zip: props.operatingSite.zip,
    name: props.operatingSite.name,
    is_head_quarter: props.operatingSite.is_head_quarter,
});

function submit() {
    siteForm.patch(
        route("operatingSite.update", {
            operatingSite: props.operatingSite.id,
        }),
        {
            onSuccess: () => siteForm.reset(),
        }
    );
}
</script>
<template>
    <AdminLayout title="Standort">
        <v-container>
            <v-alert
                class="mb-4"
                v-if="siteForm.wasSuccessful"
                closable
                color="success"
                >Betriebsstätte wurde erfolgreich aktualisiert.</v-alert
            >
            <v-card>
                <v-toolbar
                    color="primary"
                    :title="operatingSite.name ?? ''"
                ></v-toolbar>
                <v-form @submit.prevent="submit">
                    <v-card-text>
                        <h3>Kontaktinformationen</h3>
                        <v-divider class="mb-8"></v-divider>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Name"
                                    v-model="siteForm.name"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Email"
                                    v-model="siteForm.email"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Telefonnummer"
                                    v-model="siteForm.phone_number"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Fax"
                                    v-model="siteForm.fax"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <h3 class="mt-4">Adresse</h3>
                        <v-divider class="mb-8"></v-divider>
                        <v-row>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Straße"
                                    v-model="siteForm.street"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Hausnummer"
                                    v-model="siteForm.house_number"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Adresszusatz"
                                    v-model="siteForm.address_suffix"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Postleitzahl"
                                    v-model="siteForm.zip"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Ort"
                                    v-model="siteForm.city"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Bundesland"
                                    v-model="siteForm.federal_state"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-checkbox
                                    label="Hauptsitz?"
                                    v-model="siteForm.is_head_quarter"
                                    variant="underlined"
                                ></v-checkbox>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <div class="d-flex justify-end w-100">
                            <v-btn
                                type="submit"
                                color="primary"
                                variant="elevated"
                            >
                                Aktualisieren
                            </v-btn>
                        </div>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
