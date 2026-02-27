<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateUpdateForm from './CreateUpdateForm.vue';
import { Actor } from '@/types/types';

defineProps<{
    actors: Actor[];
}>();
</script>

<template>
    <AuthenticatedLayout title="Add Movie">
        <CreateUpdateForm :actors></CreateUpdateForm>
    </AuthenticatedLayout>
</template>
