<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
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
    <v-card>
        <v-card-title>Account löschen</v-card-title>
        <v-card-text>
            Wenn Ihr Konto gelöscht wird, werden alle Ressourcen und Daten dauerhaft gelöscht. Bevor Sie Ihr Konto löschen, laden Sie bitte alle Daten
            oder Informationen herunter, die Sie behalten möchten.
        </v-card-text>
        <div class="text-end ma-3">
            <v-btn :loading="form.processing" type="submit" color="error" @click="confirmUserDeletion">Account löschen</v-btn>
        </div>

        <Modal v-model="confirmingUserDeletion" title="Account löschen">
            <v-form @submit.prevent="deleteUser">
                <v-card-text>
                    Sobald Ihr Konto gelöscht ist, werden alle Ihre Ressourcen und Daten dauerhaft gelöscht. Bitte geben Sie Ihr Passwort erneut ein,
                    um zu bestätigen, dass Sie Ihr Konto dauerhaft löschen möchten.
                </v-card-text>
                <v-text-field :errorMessage="form.errors.password" v-model="form.password" label="Passwort" type="password"></v-text-field>
                <div class="d-flex justify-space-between">
                    <v-btn :loading="form.processing" color="secondary" variant="elevated" @click.stop="closeModal">Abbrechen</v-btn>
                    <v-btn :loading="form.processing" color="error" variant="elevated" type="submit">Account löschen</v-btn>
                </div>
            </v-form>
        </Modal>
    </v-card>
</template>
