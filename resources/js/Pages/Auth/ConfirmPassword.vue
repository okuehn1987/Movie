<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout title="Confirm Password">
        <v-form @submit.prevent="submit">
            <v-row>
                <v-col cols="12"> This is a secure area of the application. Please confirm your password before continuing.</v-col>

                <v-col cols="12">
                    <v-text-field
                        v-model="form.password"
                        :readonly="form.processing"
                        :errorMessages="form.errors.password"
                        variant="solo"
                        required
                        type="password"
                        autocomplete="current-password"
                        label="Password"
                        class="mb-n3"
                    ></v-text-field>
                </v-col>

                <v-col cols="12">
                    <v-btn :loading="form.processing" block color="primary" type="submit" size="large">Confirm</v-btn>
                </v-col>
            </v-row>
        </v-form>
    </GuestLayout>
</template>
