<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Group, OperatingSite, User } from "@/types/types";
import { useForm } from "@inertiajs/vue3";
import { DateTime } from "luxon";
import { VDateInput } from "vuetify/labs/components";

const props = defineProps<{
    user: User;
    groups: Group[];
    operating_sites: OperatingSite[];
}>();

const userForm = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    date_of_birth: props.user.date_of_birth,
    city: props.user.city,
    zip: props.user.zip,
    street: props.user.street,
    house_number: props.user.house_number,
    address_suffix: props.user.address_suffix,
    country: props.user.country,
    federal_state: props.user.federal_state,
    phone_number: props.user.phone_number,
    staff_number: props.user.staff_number,
    group_id: props.user.group_id,
    operating_site_id: props.user.operating_site_id,
});

function submit() {
    userForm
        .transform((data) => ({
            ...data,
            date_of_birth: DateTime.fromISO(
                new Date(data.date_of_birth + "").toISOString()
            ),
        }))
        .patch(route("user.update", { user: props.user.id }), {
            onSuccess: () => userForm.reset(),
        });
}
</script>
<template>
    <AdminLayout title="User Show">
        <v-container>
            <v-card>
                <v-toolbar
                    color="primary"
                    :title="user.first_name + ' ' + user.last_name"
                ></v-toolbar>
                <v-alert
                    class="my-4"
                    v-if="userForm.wasSuccessful"
                    closable
                    color="success"
                    >Mitarbeiter wurde erfolgreich aktualisiert.</v-alert
                >
                <v-form @submit.prevent="submit">
                    <v-card-text>
                        <h3>Allgemeine Informationen</h3>
                        <v-divider class="mb-8"></v-divider>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Vorname"
                                    v-model="userForm.first_name"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Nachname"
                                    v-model="userForm.last_name"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Email"
                                    v-model="userForm.email"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Telefonnummer"
                                    v-model="userForm.phone_number"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-date-input
                                    label="Geburtsdatum"
                                    v-model="userForm.date_of_birth"
                                    variant="underlined"
                                ></v-date-input>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Personalnummer"
                                    v-model="userForm.staff_number"
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
                                    v-model="userForm.street"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Hausnummer"
                                    v-model="userForm.house_number"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    label="Adresszusatz"
                                    v-model="userForm.address_suffix"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Postleitzahl"
                                    v-model="userForm.zip"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Ort"
                                    v-model="userForm.city"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Bundesland"
                                    v-model="userForm.federal_state"
                                    variant="underlined"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <h3 class="mt-4">Abteilung</h3>
                        <v-divider class="mb-8"></v-divider>
                        <v-row>
                            <v-col cols="12">
                                <v-select
                                    v-model="userForm.group_id"
                                    class="px-8"
                                    :items="groups"
                                    item-title="name"
                                    item-value="id"
                                    label="Wähle eine Abteilung aus, zu die der Mitarbeiter gehören soll."
                                    variant="underlined"
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-select
                                    v-model="userForm.operating_site_id"
                                    class="px-8"
                                    :items="operating_sites"
                                    item-title="name"
                                    item-value="id"
                                    label="Wähle den Betriebsort des Mitarbeiters aus."
                                    variant="underlined"
                                ></v-select>
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
