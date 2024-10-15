<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';
defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout title="Forgot Password">
        <div class="d-flex h-100 justify-center align-center flex-column">
            <div class="mb-5" v-if="!status">
                <v-alert color="darkPrimary" variant="plain">
                    Passwort vergessen? Kein Problem. Geben Sie einfach Ihre E-Mail-Adresse ein und wir senden Ihnen einen Link zum Zurücksetzen des
                    Passworts.
                </v-alert>
            </div>
            <div class="mb-5" v-if="status">
                <v-alert color="success" variant="elevated">
                    {{ status }}
                </v-alert>
            </div>
            <v-form @submit.prevent="submit" class="w-100 text-center">
                <v-text-field
                    id="email"
                    label="Email"
                    :errorMessages="form.errors.email"
                    type="email"
                    class="mt-1 mb-3"
                    v-model="form.email"
                    variant="solo"
                    required
                    autocomplete="username"
                    style="width: 100%"
                />
                <v-btn color="primary" :loading="form.processing" :disabled="form.processing" type="submit" variant="elevated">
                    Passwort Zurücksetzen
                </v-btn>
                <v-card-text class="pb-0 px-0 text-center mt-4">
                    <v-btn :href="route('login')" class="text-primary btn btn-link text-black mt-2 text-decoration-none" variant="outlined">
                        Zurück zum Login
                    </v-btn>
                </v-card-text>
            </v-form>
        </div>
    </GuestLayout>
</template>
