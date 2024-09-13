<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { AbsenceType } from "@/types/types";

defineProps<{
    absenceTypes: AbsenceType[];
    types: string[];
}>();
</script>
<template>
    <AdminLayout title="Abwesenheitsgründe"
        >{{ absenceTypes }} - {{ types }}</AdminLayout
    >
</template>
