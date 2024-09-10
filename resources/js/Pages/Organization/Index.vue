<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    organizations: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Organisationen">{{ organizations }}</AdminLayout>
</template>
