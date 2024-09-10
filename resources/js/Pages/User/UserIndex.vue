<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    users: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Mitarbeiter">{{ users }}</AdminLayout>
</template>
