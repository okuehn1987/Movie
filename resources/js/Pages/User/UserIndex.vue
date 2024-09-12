<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { User } from "@/types/types";

defineProps<{
    users: User[];
}>();
</script>
<template>
    <AdminLayout title="Mitarbeiter">{{ users }}</AdminLayout>
</template>
