<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const adminForm = useForm({
    email: '',
    password: '',
});

const adminLogin = () => {
    adminForm.post(route('login'), {
        onSuccess: () => {
            router.reload();
        },
        onFinish: () => {
            adminForm.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout title="Login">
        <v-form @submit.prevent="adminLogin">
            <v-row>
                <v-col cols="12">
                    <v-text-field
                        v-model="adminForm.email"
                        :readonly="adminForm.processing"
                        :error-messages="adminForm.errors.email"
                        prepend-inner-icon="mdi-email-outline"
                        label="Email"
                    ></v-text-field>

                    <v-text-field
                        v-model="adminForm.password"
                        :readonly="adminForm.processing"
                        :error-messages="adminForm.errors.password"
                        type="password"
                        prepend-inner-icon="mdi-lock-outline"
                        label="Passwort"
                        class="mt-2 mb-n3"
                    ></v-text-field>
                </v-col>

                <v-col cols="12">
                    <v-btn :loading="adminForm.processing" block color="primary" type="submit" size="large"> Login </v-btn>
                </v-col>

                <v-col cols="12">
                    <Link :href="route('password.request')" class="text-primary text-decoration-none">
                        <v-icon icon="mdi-lock"></v-icon>
                        Passwort vergessen?
                    </Link>
                </v-col>
            </v-row>
        </v-form>
    </GuestLayout>
</template>
<style scoped></style>
