<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Group, OperatingSite, User } from "@/types/types";
import { Link, useForm } from "@inertiajs/vue3";
import { DateTime } from "luxon";
import { VDateInput } from "vuetify/labs/components";

defineProps<{
    users: (User & { group: Pick<Group, "id" | "name"> })[];
    groups: Group[];
    operating_sites: OperatingSite[];
}>();

const userForm = useForm({
    first_name: "",
    last_name: "",
    email: "",
    date_of_birth: null,
    city: "",
    zip: "",
    street: "",
    house_number: "",
    address_suffix: "",
    country: "",
    federal_state: "",
    phone_number: "",
    staff_number: 0,
    password: "",
    group_id: null,
    operating_site_id: null,
});

function submit() {
    userForm
        .transform((data) => ({
            ...data,
            date_of_birth: DateTime.fromISO(
                new Date(data.date_of_birth + "").toISOString()
            ),
        }))
        .post(route("user.store"), {
            onSuccess: () => userForm.reset(),
        });
}
</script>
<template>
    <AdminLayout title="Mitarbeiter">
        <v-container>
            <v-alert
                class="mb-4"
                v-if="userForm.wasSuccessful"
                closable
                color="success"
                >Mitarbeiter wurde erfolgreich angelegt.</v-alert
            >
            <v-data-table-virtual
                :headers="[
                    { title: '#', key: 'id' },
                    { title: 'Vorname', key: 'first_name' },
                    { title: 'Nachname', key: 'last_name' },
                    { title: 'Email', key: 'email' },
                    { title: 'Abteilung', key: 'group.name' },
                    { title: 'Personalnummer', key: 'staff_number' },
                    { title: 'Geburtsdatum', key: 'date_of_birth' },
                    { title: '', key: 'actions', align: 'end' },
                ]"
                :items="users"
                hover
                ><template v-slot:header.actions>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card>
                                <v-form @submit.prevent="submit">
                                    <v-toolbar
                                        color="primary"
                                        class="mb-4"
                                        title="Mitarbeiter hinzufügen"
                                    ></v-toolbar>
                                    <v-card-text>
                                        <h3>Allgemeine Informationen</h3>
                                        <v-divider></v-divider>
                                    </v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.first_name"
                                                label="Vorname"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.last_name"
                                                label="Nachname"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.email"
                                                label="Email"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.password"
                                                label="Passwort"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-date-input
                                                v-model="userForm.date_of_birth"
                                                label="Geburtsdatum"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-date-input>
                                        </v-col>
                                    </v-row>
                                    <v-card-text>
                                        <h3>Adresse</h3>
                                        <v-divider></v-divider>
                                    </v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.street"
                                                label="Straße"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.house_number"
                                                label="Hausnummer"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.city"
                                                label="Ort"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.zip"
                                                label="Postleitzahl"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.country"
                                                label="Land"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                v-model="userForm.federal_state"
                                                label="Bundesland"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>
                                    <v-card-text>
                                        <h3>Abteilung</h3>
                                        <v-divider></v-divider>
                                    </v-card-text>
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
                                                v-model="
                                                    userForm.operating_site_id
                                                "
                                                class="px-8"
                                                :items="operating_sites"
                                                item-title="name"
                                                item-value="id"
                                                label="Wähle den Betriebsort des Mitarbeiters aus."
                                                variant="underlined"
                                            ></v-select>
                                        </v-col>
                                    </v-row>
                                    <v-card-actions>
                                        <div class="d-flex justify-end w-100">
                                            <v-btn
                                                color="error"
                                                variant="elevated"
                                                class="me-2"
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
                <template v-slot:item.actions="{ item }">
                    <v-btn
                        :href="
                            route('user.show', {
                                user: item.id,
                            })
                        "
                        color="primary"
                        class="me-2"
                    >
                        <v-icon size="large" icon="mdi-pencil"></v-icon>
                    </v-btn>
                    <v-dialog>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="error">
                                <v-icon size="large" icon="mdi-delete">
                                </v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card
                                ><v-toolbar
                                    color="primary"
                                    class="mb-4"
                                    title="Mitarbeiter löschen"
                                ></v-toolbar>
                                <v-card-text
                                    >Bist du dir sicher, dass du
                                    {{ item.first_name }}
                                    {{ item.last_name }} entfernen möchtest?
                                </v-card-text>
                                <v-card-actions>
                                    <div class="d-flex justify-end w-100">
                                        <v-btn
                                            color="error"
                                            variant="elevated"
                                            class="me-2"
                                            @click="isActive.value = false"
                                        >
                                            Abbrechen
                                        </v-btn>
                                        <Link
                                            :href="
                                                route('user.destroy', {
                                                    user: item.id,
                                                })
                                            "
                                            method="delete"
                                        >
                                            <v-btn
                                                type="submit"
                                                color="primary"
                                                variant="elevated"
                                                >Löschen
                                            </v-btn>
                                        </Link>
                                    </div>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
            </v-data-table-virtual>
        </v-container>
    </AdminLayout>
</template>
