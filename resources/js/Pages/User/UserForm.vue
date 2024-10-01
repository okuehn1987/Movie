<script setup lang="ts">
import { Group, OperatingSite, User, UserPermission } from "@/types/types";
import { InertiaForm } from "@inertiajs/vue3";

defineProps<{
    userForm: InertiaForm<
        Partial<
            Omit<User, UserPermission["name"]> & {
                permissions: UserPermission["name"][];
            }
        >
    >;
    submit: () => void;
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
    groups: Group[];
    mode: "create" | "edit";
}>();
</script>
<template>
    <v-form @submit.prevent="submit">
        <v-toolbar
            color="primary"
            class="mb-4"
            :title="
                mode === 'create'
                    ? 'Mitarbeiter hinzufügen'
                    : userForm.first_name + ' ' + userForm.last_name
            "
        ></v-toolbar>
        <v-alert
            class="mb-4"
            v-if="userForm.wasSuccessful"
            closable
            color="success"
            >Mitarbeiter wurde erfolgreich aktualisiert.</v-alert
        >
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
        <v-card-text>
            <h3>Berechtigungen</h3>
            <v-divider></v-divider>
        </v-card-text>
        <v-row>
            <v-col cols="12" md="6" v-for="permission in permissions">
                <v-checkbox
                    class="px-8"
                    v-model="userForm.permissions"
                    :value="permission.name"
                    :label="permission.label"
                ></v-checkbox>
            </v-col>
        </v-row>
        <v-card-actions>
            <div class="d-flex justify-end w-100">
                <v-btn type="submit" color="primary" variant="elevated">
                    Speichern
                </v-btn>
            </div>
        </v-card-actions>
    </v-form>
</template>
