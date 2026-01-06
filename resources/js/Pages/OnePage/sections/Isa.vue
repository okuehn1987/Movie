<template>
    <section id="isa" aria-labelledby="isaTitle" class="mb-8 mb-md-16 pt-10 pb-md-16">
        <div style="width: 100%">
            <v-img width="200px" eager class="justify-content-center align-items-center mx-auto" src="/img/Isa-Transparent.png"></v-img>
        </div>

        <v-row class="mb-2 pt-2" style="font-size: 20px">
            <v-col cols="12" class="d-flex justify-center mb-md-8">
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

        <v-row class="mb-8 cards-band px-3 px-sm-6 px-md-0" dense>
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
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Mehr Informationen zu Kundenmanagement & Wissen">
                    <v-card-text>
                        <v-img eager src="/img/Isa-Screenshot.png"></v-img>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
        <v-row style="height: 20%" class="cards-band mb-8 d-flex align-center">
            <v-col cols="12" md="6" class="py-6">
                <p class="sub-headline text-center text-sm-start text-grey-darken-2 mb-1">
                    Sie haben Interesse an einem KI-basierten Chatbot für Ihre Kunden?
                </p>
                <p class="sub-headline text-center text-sm-start text-grey-darken-2 mb-6">Dann schauen Sie sich gerne Britta AI an.</p>
                <v-hover v-slot="{ isHovering, props }">
                    <v-btn
                        href="https://britta-ai.de"
                        style="height: 40px"
                        :style="display.smAndUp.value ? '' : 'margin-left: 25%'"
                        target="_blank"
                        rel="noopener"
                        v-bind="props"
                        :variant="isHovering ? 'flat' : 'outlined'"
                        color="#214d63"
                        class="rounded-lg btn-transition w-50 text-body-2 text-sm-body-1"
                    >
                        Mehr Erfahren
                    </v-btn>
                </v-hover>
            </v-col>
            <v-col cols="12" md="6">
                <v-card style="height: 200px" class="pa-3 rounded-lg elevation border d-flex justify-center align-center">
                    <v-card-text class="text-center">Placeholder</v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </section>
</template>

<script setup lang="ts">
import { useDisplay } from 'vuetify';
const display = useDisplay();
</script>

<style scoped>
.cards-band {
    max-width: 1100px;
    margin-inline: auto;
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
    margin-bottom: 20px;
}

@media (max-width: 960px) {
    .sub-headline {
        font-size: 18px !important;
        margin-inline: 10% !important;
    }
}

.btn-transition {
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}
</style>
