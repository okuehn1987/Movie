<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout title="Passwort Zurücksetzen">
        <div class="d-flex h-100 w-100 justify-center flex-column">
            <v-form @submit.prevent="submit" class="w-100">
                <div class="d-flex justify-space-between mb-3 w-100">
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
                </div>
                <div class="d-flex justify-space-between mb-3 w-100">
                    <v-text-field
                        style="width: 100%"
                        v-model="form.password"
                        :readonly="form.processing"
                        :errorMessages="form.errors.password"
                        variant="solo"
                        required
                        type="password"
                        autocomplete="new-password"
                        label="Neues Passwort"
                    ></v-text-field>
                </div>
                <div class="d-flex justify-space-between mb-12 w-100">
                    <v-text-field
                        style="width: 100%"
                        v-model="form.password_confirmation"
                        :readonly="form.processing"
                        :errorMessages="form.errors.password_confirmation"
                        variant="solo"
                        required
                        type="password"
                        autocomplete="new-password"
                        label="Neues Passwort bestätigen"
                    ></v-text-field>
                </div>
                <div class="d-flex justify-center">
                    <v-btn type="submit" :loading="form.processing" variant="elevated" color="primary">Passwort zurücksetzen</v-btn>
                </div>
            </v-form>
        </div>
    </GuestLayout>
</template>
