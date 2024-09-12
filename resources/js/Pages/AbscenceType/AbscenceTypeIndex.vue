<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { AbscenceType } from "@/types/types";

defineProps<{
    abscenceTypes: AbscenceType[];
    types: string[];
}>();
</script>
<template>
    <AdminLayout title="Abwesenheitsgründe"
        >{{ abscenceTypes }} - {{ types }}</AdminLayout
    >
</template>
