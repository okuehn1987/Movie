<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    first_name: '',
    last_name: '',
});
</script>

<template>
    <AuthenticatedLayout title="Add Actor">
        <form @submit.prevent="form.post(route('actor.store'), { onSuccess: () => form.reset() })">
            <v-text-field v-model="form.first_name" placeholder="First Name" :error-messages="form.errors.first_name"></v-text-field>
            <v-text-field v-model="form.last_name" placeholder="Last Name" :error-messages="form.errors.last_name"></v-text-field>
            <v-btn type="submit">Actor Upload</v-btn>
        </form>
    </AuthenticatedLayout>
</template>
