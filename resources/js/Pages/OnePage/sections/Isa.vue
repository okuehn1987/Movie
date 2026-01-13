<template>
    <section id="isa" aria-labelledby="isaTitle" class="pt-10 pb-10 pb-md-16">
        <div style="width: 100%">
            <v-img width="200px" eager class="mb-md-6 pt-4 mx-auto" src="/img/Isa-Transparent.png"></v-img>
        </div>

        <v-row class="mb-2 pt-2" style="font-size: 20px">
            <v-col cols="12" class="d-flex justify-center mb-md-2">
                <span class="sub-headline text-center text-grey-darken-2 px-4 px-md-0">
                    <b>Isa</b>
                    durchsucht KI-basiert alle ihr zur Verfügung gestellten Daten sowie alle freigegebenen Daten aus Flow.
                </span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center mb-md-8 pt-0">
                <span class="sub-headline text-center text-grey-darken-2">
                    Stellen Sie natürlichsprachige Fragen und erhalten Sie sofort relevante Antworten – in jeder Sprache, ohne stundenlanges Suchen.
                </span>
            </v-col>
        </v-row>
        <v-row class="cards-band px-3 px-sm-6 px-md-0" dense>
            <v-col cols="12" sm="4" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Sofortige Antworten">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center justify-center">
                            <v-icon
                                class="feature-icon rounded-lg"
                                style="width: 60px; height: 60px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-lightning-bolt-outline
                            </v-icon>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-center">
                            <p class="text-h6 text-wrap">Sofortige Antworten</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="4" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Intern & sicher">
                    <v-card-title class="pt-6">
                        <div class="d-flex justify-center">
                            <v-icon
                                class="rounded-lg"
                                style="width: 60px; height: 60px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-shield-outline
                            </v-icon>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-center">
                            <p class="text-h6 text-wrap">Intern & sicher</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="4" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Mehrsprachig">
                    <v-card-title class="pt-6">
                        <div class="d-flex justify-center">
                            <v-icon
                                class="rounded-lg"
                                style="width: 60px; height: 60px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-chat-outline
                            </v-icon>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-center">
                            <p class="text-h6 text-wrap">Mehrsprachig</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="mx-auto mt-8 pa-2">
                <v-carousel height="400" hide-delimiters :transition-duration="800" cycle show-arrows="hover">
                    <v-carousel-item
                        v-for="(src, i) in [
                            '/img/Isa-Screenshot.png',
                            '/img/Isa-Screenshot_2.png',
                            '/img/Isa-Screenshot_3.png',
                            '/img/Isa-Screenshot_4.png',
                        ]"
                        :key="i"
                        :src="src"
                    ></v-carousel-item>
                </v-carousel>
            </v-col>
            <v-col cols="12" class="d-flex justify-center mb-md-2">
                <span class="sub-headline text-center text-grey-darken-2 px-4 px-md-0">
                    <i>* &nbsp; Beispielchats - abhängig von freigegebenen Dateien</i>
                </span>
            </v-col>
        </v-row>
        <v-row class="cards-band pt-16 px-3 px-sm-6 px-md-16 d-flex align-center" dense>
            <v-col cols="12" md="6" class="py-6 d-flex align-md-start align-center flex-column">
                <p class="sub-headline text-grey-darken-2 mb-6 text-center text-md-start" style="font-size: 20px">
                    Sie haben Interesse an einem KI-basierten Chatbot für Ihre Kunden?
                    <br />
                    Dann schauen Sie sich auch gerne Britta AI an.
                </p>
                <v-hover v-slot="{ isHovering, props }">
                    <v-btn
                        href="https://britta-ai.de"
                        :size="display.mdAndUp.value ? 'large' : undefined"
                        target="_blank"
                        rel="noopener"
                        v-bind="props"
                        :variant="isHovering ? 'flat' : 'outlined'"
                        color="#214d63"
                        class="rounded-lg btn-transition w-50"
                    >
                        Mehr Erfahren
                    </v-btn>
                </v-hover>
            </v-col>
            <v-col cols="12" md="6" class="pa-2 d-flex align-center justify-center">
                <a href="https://britta-ai.de" target="_blank" rel="noopener">
                    <v-img width="250" class="mx-auto logo-float" contain src="img/BrittaAI_Logo.png" style="cursor: pointer"></v-img>
                </a>
            </v-col>
        </v-row>
    </section>
</template>

<script setup>
import { useDisplay } from 'vuetify';
const display = useDisplay();
</script>

<style scoped>
.cards-band {
    max-width: 1100px;
}

.elevation {
    box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.feature-icon {
    width: 50px;
    height: 50px;
    font-size: 28px;
    background-color: rgba(0, 189, 157, 0.1);
    color: rgb(0, 189, 157);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 960px) {
    .sub-headline {
        font-size: 18px !important;
    }
}

.btn-transition {
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.logo-float {
    animation: float 4s ease-in-out infinite;
}

@keyframes float {
    0% {
        transform: scale(1);
    }
    60% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}
</style>
