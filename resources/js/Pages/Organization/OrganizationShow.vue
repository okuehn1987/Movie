<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Organization } from "@/types/types";
defineProps<{
    organization: Organization;
}>();
</script>
<template>
    <AdminLayout title="Organisation">{{ organization }}</AdminLayout>
</template>
