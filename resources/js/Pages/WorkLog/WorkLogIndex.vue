<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    workLogs: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Arbeitszeiten">{{ workLogs }}</AdminLayout>
</template>
