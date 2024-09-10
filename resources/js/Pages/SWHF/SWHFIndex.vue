<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    factors: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Sonderarbeitzeitfaktor">{{ factors }}</AdminLayout>
</template>
