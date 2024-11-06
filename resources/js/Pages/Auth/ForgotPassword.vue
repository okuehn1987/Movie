<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
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
        <v-form @submit.prevent="submit">
            <v-row>
                <v-col cols="12">
                    Passwort vergessen? Kein Problem. Geben Sie einfach Ihre E-Mail-Adresse ein und wir senden Ihnen einen Link zum Zurücksetzen des
                    Passworts.
                </v-col>

                <v-col cols="12">
                    <v-text-field
                        label="Email"
                        :errorMessages="form.errors.email"
                        type="email"
                        v-model="form.email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="solo"
                        required
                        autocomplete="username"
                        class="mb-n3"
                    />
                </v-col>

                <v-col cols="12">
                    <v-btn :loading="form.processing" block color="primary" type="submit" size="large"> Passwort Zurücksetzen </v-btn>
                </v-col>

                <v-col cols="12">
                    <Link :href="route('login')" class="text-primary text-decoration-none">
                        <v-icon icon="mdi-chevron-left" style="margin-bottom: 2px"></v-icon>
                        Zurück zum Login
                    </Link>
                </v-col>
            </v-row>
        </v-form>
    </GuestLayout>
</template>
