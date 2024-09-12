<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { SpecialWorkingHoursFactor } from "@/types/types";

defineProps<{
    factors: SpecialWorkingHoursFactor[];
}>();
</script>
<template>
    <AdminLayout title="Sonderarbeitzeitfaktor">{{ factors }}</AdminLayout>
</template>
