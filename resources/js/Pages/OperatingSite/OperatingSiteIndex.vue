<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { OperatingSite } from "@/types/types";

defineProps<{
    operatingSites: OperatingSite[];
}>();
</script>
<template>
    <AdminLayout title="Betriebsorte">{{ operatingSites }}</AdminLayout>
</template>
