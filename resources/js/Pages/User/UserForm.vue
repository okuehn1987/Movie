<script setup lang="ts">
import { Group, OperatingSite, User, UserPermission } from '@/types/types';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    user?: User;
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
    groups: Group[];
    mode: 'create' | 'edit';
}>();

const userForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    date_of_birth: null as null | string,
    city: '',
    zip: '',
    street: '',
    house_number: '',
    address_suffix: '',
    country: '',
    federal_state: '',
    phone_number: '',
    staff_number: 0,
    password: '',
    group_id: null as null | number,
    operating_site_id: null as null | number,
    permissions: [] as UserPermission['name'][],
});

if (props.user) {
    userForm.first_name = props.user.first_name;
    userForm.last_name = props.user.last_name;
    userForm.email = props.user.email;
    userForm.date_of_birth = props.user.date_of_birth;
    userForm.city = props.user.city ?? '';
    userForm.zip = props.user.zip ?? '';
    userForm.street = props.user.street ?? '';
    userForm.house_number = props.user.house_number ?? '';
    userForm.address_suffix = props.user.address_suffix ?? '';
    userForm.country = props.user.country ?? '';
    userForm.federal_state = props.user.federal_state ?? '';
    userForm.phone_number = props.user.phone_number ?? '';
    userForm.staff_number = props.user.staff_number;
    userForm.password = props.user.password;
    userForm.group_id = props.user.group_id;
    userForm.operating_site_id = props.user.operating_site_id;
    for (const permission of props.permissions) {
        userForm.permissions.push(permission.name);
    }
}

function submit() {
    const form = userForm.transform(data => ({
        ...data,
        date_of_birth: data.date_of_birth ? new Date(data.date_of_birth).toISOString() : null,
    }));
    if (props.mode == 'edit' && props.user)
        form.patch(route('user.update', { user: props.user.id }), {
            onSuccess: () => userForm.reset(),
        });
    else {
        form.post(route('user.store'), {
            onSuccess: () => userForm.reset(),
            onError: e => console.log(e),
        });
    }
}
</script>
<template>
    <v-form @submit.prevent="submit">
        <v-toolbar
            color="primary"
            class="mb-4"
            :title="mode === 'create' ? 'Mitarbeiter hinzufügen' : userForm.first_name + ' ' + userForm.last_name"
        ></v-toolbar>
        <v-alert class="mb-4" v-if="userForm.wasSuccessful" closable color="success">Mitarbeiter wurde erfolgreich aktualisiert.</v-alert>
        <v-card-text>
            <h3>Allgemeine Informationen</h3>
            <v-divider></v-divider>
        </v-card-text>
        <v-row>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.first_name" label="Vorname" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.last_name" label="Nachname" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.email" label="Email" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6" v-if="mode == 'create'">
                <v-text-field v-model="userForm.password" label="Passwort" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-date-input
                    v-model="userForm.date_of_birth"
                    prepend-icon=""
                    label="Geburtsdatum"
                    class="px-8"
                    variant="underlined"
                    required
                ></v-date-input>
            </v-col>
        </v-row>
        <v-card-text>
            <h3>Adresse</h3>
            <v-divider></v-divider>
        </v-card-text>
        <v-row>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.street" label="Straße" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.house_number" label="Hausnummer" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.city" label="Ort" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.zip" label="Postleitzahl" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.country" label="Land" class="px-8" variant="underlined"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field v-model="userForm.federal_state" label="Bundesland" class="px-8" variant="underlined"></v-text-field>
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
            <v-col cols="12" md="6" v-for="permission in permissions" :key="permission.name">
                <v-checkbox class="px-8" v-model="userForm.permissions" :value="permission.name" :label="permission.label"></v-checkbox>
            </v-col>
        </v-row>
        <v-card-actions>
            <div class="d-flex justify-end w-100">
                <v-btn type="submit" color="primary" variant="elevated">Speichern</v-btn>
            </div>
        </v-card-actions>
    </v-form>
</template>
