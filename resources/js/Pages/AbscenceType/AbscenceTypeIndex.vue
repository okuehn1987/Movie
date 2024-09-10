<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    abscenceTypes: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Abwesenheitsgründe">{{ abscenceTypes }}</AdminLayout>
</template>
