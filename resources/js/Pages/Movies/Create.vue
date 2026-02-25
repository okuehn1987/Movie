<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateUpdateForm from './CreateUpdateForm.vue';
</script>

<template>
    <AuthenticatedLayout title="Add Movie">
        <CreateUpdateForm></CreateUpdateForm>
    </AuthenticatedLayout>
</template>
