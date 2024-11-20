<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const confirmingUserDeletion = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <v-card class="h-100">
        <v-card-title>Account löschen</v-card-title>
        <v-card-text>
            Wenn Ihr Konto gelöscht wird, werden alle Ressourcen und Daten dauerhaft gelöscht. Bevor Sie Ihr Konto löschen, laden Sie bitte alle Daten
            oder Informationen herunter, die Sie behalten möchten.
        </v-card-text>
        <div class="text-end ma-3">
            <v-btn :loading="form.processing" type="submit" color="error" @click="confirmUserDeletion">Account löschen</v-btn>
        </div>

        <v-dialog max-width="1000" v-model="confirmingUserDeletion">
            <v-card title="Account löschen">
                <v-form @submit.prevent="deleteUser">
                    <v-card-text>
                        <v-row>
                            <v-col cols="12">
                                Sobald Ihr Konto gelöscht ist, werden alle Ihre Ressourcen und Daten dauerhaft gelöscht. Bitte geben Sie Ihr Passwort
                                erneut ein, um zu bestätigen, dass Sie Ihr Konto dauerhaft löschen möchten.
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    :error-messages="form.errors.password"
                                    v-model="form.password"
                                    label="Passwort"
                                    type="password"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <div class="d-flex justify-space-between">
                                    <v-btn :loading="form.processing" color="primary" @click.stop="closeModal">Abbrechen</v-btn>
                                    <v-btn :loading="form.processing" color="error" type="submit">Account löschen</v-btn>
                                </div>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-form>
            </v-card>
        </v-dialog>
    </v-card>
</template>
