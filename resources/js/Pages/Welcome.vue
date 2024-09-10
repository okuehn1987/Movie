<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    users: unknown[];
}>();
</script>

<template>
    <AdminLayout title="Dashboard">
        {{ users }}
    </AdminLayout>
</template>
