<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    user: unknown;
}>();
</script>
<template>
    <AdminLayout title="User Show">{{ user }}</AdminLayout>
</template>
