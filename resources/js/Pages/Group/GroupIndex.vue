<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    groups: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Abteilungen">{{ groups }}</AdminLayout>
</template>
