<script setup lang="ts">
import { useForm, usePage, router } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.first_name,
    email: user.email,
});
</script>

<template>
    <v-card class="h-100 d-flex flex-column pa-3">
        <v-card-title>
            <h5>Profil-Informationen</h5>
        </v-card-title>
        <v-card-text style="flex-grow: 0">
            <p>Aktualisieren Sie die Profilinformationen und die E-Mail-Adresse Ihres Kontos.</p>
        </v-card-text>

        <form @submit.prevent="form.patch(route('profile.update'))" class="d-flex flex-column flex-grow-1">
            <div class="flex-grow-1">
                <v-text-field
                    id="name"
                    type="text"
                    v-model="form.name"
                    label="Name"
                    required
                    autocomplete="name"
                    :error-messages="form.errors.name"
                />
                <v-text-field
                    id="email"
                    type="email"
                    v-model="form.email"
                    label="Email"
                    required
                    autocomplete="username"
                    :error-messages="form.errors.email"
                />
            </div>

            <v-alert class="mb-3" v-if="mustVerifyEmail && user?.email_verified_at === null">
                Deine E-Mail-Adresse wurde nicht verifiziert.
                <span role="button" @click.stop="router.post(route('verification.send'))" style="text-decoration: underline">
                    Klicken Sie hier, um die Verifizierungs-E-Mail erneut zu senden.
                </span>

                <div v-show="status === 'verification-link-sent'">Ein neuer Verifizierungslink wurde an Ihre E-Mail-Adresse gesendet.</div>
            </v-alert>

            <div class="text-end">
                <v-btn type="submit" color="primary" :loading="form.processing">Speichern</v-btn>
                <v-snackbar v-model="form.recentlySuccessful">Gespeichert</v-snackbar>
            </div>
        </form>
    </v-card>
</template>
