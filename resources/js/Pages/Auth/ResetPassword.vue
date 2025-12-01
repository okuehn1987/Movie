<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';

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
        <v-form @submit.prevent="submit">
            <v-row>
                <v-col cols="12">
                    <v-text-field
                        v-model="form.email"
                        id="email"
                        :errorMessages="form.errors.email"
                        class="mt-1 mb-3"
                        required
                        type="email"
                        autocomplete="username"
                        label="Email"
                    />
                    <v-text-field
                        v-model="form.password"
                        :readonly="form.processing"
                        :errorMessages="form.errors.password"
                        required
                        type="password"
                        autocomplete="new-password"
                        label="Neues Passwort"
                    ></v-text-field>
                    <v-text-field
                        v-model="form.password_confirmation"
                        :readonly="form.processing"
                        :errorMessages="form.errors.password_confirmation"
                        required
                        type="password"
                        autocomplete="new-password"
                        label="Neues Passwort bestätigen"
                        class="mb-n3"
                    ></v-text-field>
                </v-col>

                <v-col cols="12">
                    <v-btn :loading="form.processing" block color="primary" type="submit" size="large">Passwort zurücksetzen</v-btn>
                </v-col>
            </v-row>
        </v-form>
    </GuestLayout>
</template>
