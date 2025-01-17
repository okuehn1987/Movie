<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
});
</script>

<template>
    <v-card class="h-100" title="Profil-Informationen">
        <v-card-text>
            <form @submit.prevent="form.patch(route('profile.update'))">
                <v-row>
                    <v-col cols="12">
                        <p>Aktualisieren Sie die Profilinformationen und die E-Mail-Adresse Ihres Kontos.</p>
                    </v-col>

                    <v-col cols="12" md="6">
                        <v-text-field v-model="form.first_name" label="Vorname" required :error-messages="form.errors.first_name" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="form.last_name" label="Nachname" required :error-messages="form.errors.last_name" />
                    </v-col>
                    <v-col cols="12">
                        <v-text-field type="email" v-model="form.email" label="Email" required :error-messages="form.errors.email" />
                    </v-col>

                    <v-col cols="12">
                        <v-alert class="mb-3" v-if="mustVerifyEmail && user?.email_verified_at === null">
                            Deine E-Mail-Adresse wurde nicht verifiziert.
                            <span role="button" @click.stop="router.post(route('verification.send'))" style="text-decoration: underline">
                                Klicken Sie hier, um die Verifizierungs-E-Mail erneut zu senden.
                            </span>

                            <div v-show="status === 'verification-link-sent'">
                                Ein neuer Verifizierungslink wurde an Ihre E-Mail-Adresse gesendet.
                            </div>
                        </v-alert>
                    </v-col>

                    <v-col cols="12">
                        <div class="text-end">
                            <v-btn type="submit" color="primary" :loading="form.processing">Speichern</v-btn>
                        </div>
                    </v-col>
                </v-row>
            </form>
        </v-card-text>
    </v-card>
</template>
