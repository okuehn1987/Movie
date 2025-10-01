<script setup lang="ts">
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <v-card class="h-100" title="Passwort ändern">
        <v-divider></v-divider>
        <v-card-text>
            <form @submit.prevent="updatePassword">
                <v-row>
                    <v-col cols="12">Stellen Sie sicher, dass Ihr Konto ein langes, zufälliges Passwort verwendet, um sicher zu bleiben.</v-col>
                    <v-col cols="12">
                        <v-text-field
                            type="password"
                            label="Aktuelles Passwort"
                            :errorMessages="form.errors.current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            autocomplete="current-password"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            type="password"
                            ref="passwordInput"
                            label="Neues Passwort"
                            v-model="form.password"
                            :errorMessages="form.errors.password"
                            autocomplete="new-password"
                        />
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            type="password"
                            label="Passwort bestätigen"
                            v-model="form.password_confirmation"
                            :errorMessages="form.errors.password_confirmation"
                            autocomplete="new-password"
                        />
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
