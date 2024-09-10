<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <v-card>
        <v-card-title><h5>Passwort ändern</h5></v-card-title>
        <v-card-text
            >Stellen Sie sicher, dass Ihr Konto ein langes, zufälliges Passwort
            verwendet, um sicher zu bleiben.</v-card-text
        >

        <form @submit.prevent="updatePassword" class="pa-3">
            <v-text-field
                type="password"
                label="Aktuelles Passwort"
                :errorMessages="form.errors.current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                autocomplete="current-password"
            />

            <v-text-field
                type="password"
                ref="passwordInput"
                label="Neues Passwort"
                v-model="form.password"
                :errorMessages="form.errors.password"
                autocomplete="new-password"
            />

            <v-text-field
                type="password"
                label="Passwort bestätigen"
                v-model="form.password_confirmation"
                :errorMessages="form.errors.password_confirmation"
                autocomplete="new-password"
            />

            <div class="text-end">
                <v-btn type="submit" color="primary" :loading="form.processing"
                    >Speichern</v-btn
                >
                <v-snackbar v-model="form.recentlySuccessful"
                    >Gespeichert</v-snackbar
                >
            </div>
        </form>
    </v-card>
</template>
