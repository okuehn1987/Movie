<script setup lang="ts">
import { SpecialWorkingHoursFactor } from "@/types/types";

defineProps<{
    special_working_hours_factors: SpecialWorkingHoursFactor[];
}>();
</script>
<template>
    <v-card flat>
        <v-card-text>{{ special_working_hours_factors }}</v-card-text>
    </v-card>
</template>
