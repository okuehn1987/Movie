<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps<{
    operatingSites: unknown[];
}>();
</script>
<template>
    <AdminLayout title="Betriebsorte">{{ operatingSites }}</AdminLayout>
</template>
