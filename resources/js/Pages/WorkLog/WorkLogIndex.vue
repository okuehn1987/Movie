<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { WorkLog } from "@/types/types";

defineProps<{
    workLogs: WorkLog[];
}>();
</script>
<template>
    <AdminLayout title="Arbeitszeiten">{{ workLogs }}</AdminLayout>
</template>
