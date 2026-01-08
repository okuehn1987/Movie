<script setup lang="ts">
import { ref } from 'vue';
import OnePageLayout from '@/Layouts/OnePageLayout.vue';
import Tools from './sections/Tools.vue';
import Tide from './sections/Tide.vue';
import Flow from './sections/Flow.vue';
import Isa from './sections/Isa.vue';
import Preise from './sections/Preise.vue';
import Kontakt from './sections/Kontakt.vue';
import HeaderComponent from './sections/HeaderComponent.vue';

const selectedModules = ref<string[]>([]);

function onSelectModules(modules: string[]) {
    selectedModules.value = Array.from(new Set(modules));
    document.getElementById('kontakt')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <OnePageLayout title="Hrta - HR Tools and Assistants">
        <div class="customFont">
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <HeaderComponent></HeaderComponent>
                </v-container>
            </div>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <Tools></Tools>
                </v-container>
            </div>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <Tide></Tide>
                </v-container>
            </div>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <Flow></Flow>
                </v-container>
            </div>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <Isa></Isa>
                </v-container>
            </div>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <Preise @selected-modules="onSelectModules"></Preise>
                </v-container>
            </div>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))">
                <v-container fluid class="pt-0 px-md-8 px-0">
                    <Kontakt :preselected-modules="selectedModules"></Kontakt>
                </v-container>
            </div>
        </div>
    </OnePageLayout>
</template>

<style scoped>
.customFont * {
    font-family: ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji' !important;
}
</style>
