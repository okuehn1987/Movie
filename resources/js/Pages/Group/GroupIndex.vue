<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Group } from "@/types/types";

defineProps<{
    groups: Group[];
}>();
</script>
<template>
    <AdminLayout title="Abteilungen">{{ groups }}</AdminLayout>
</template>
