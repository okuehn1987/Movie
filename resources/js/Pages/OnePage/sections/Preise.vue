<template>
    <section id="preise" class="mb-8 mb-md-16 pt-10 pb-md-16" aria-labelledby="preiseTitle">
        <h2 id="preiseTitle" class="headline text-h4 text-md-h3 text-center my-8 font-weight-semibold">Transparente Preise</h2>

        <v-row class="mb-4" style="font-size: 20px">
            <v-col cols="12" class="d-flex justify-center">
                <span class="sub-headline text-center text-grey-darken-2">Zahlen Sie nur für das, was Sie nutzen – ohne versteckte Kosten.</span>
            </v-col>
        </v-row>

        <v-row class="mb-8 cards-band" dense>
            <v-col cols="12" sm="6" lg="4" class="pa-2">
                <v-card class="pa-3 h-100 rounded-xl elevation border" aria-label="Tide Zeiterfassung">
                    <v-card-title>
                        <v-img width="120px" eager class="logo" src="/img/Tide.png"></v-img>
                        <h3 class="card-h3">Tide</h3>
                    </v-card-title>
                    <v-card-subtitle>Zeiterfassung</v-card-subtitle>
                    <v-card-text>
                        <div class="d-flex justify-start align-end mb-6 mt-0 price-row">
                            <h2 class="mb-0 mt-2 text-start font-weight-bold text-h4">4,75€</h2>
                            <v-card-subtitle class="text-body-1 ps-2">/ Mitarbeitende / Monat</v-card-subtitle>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Unbegrenzte Zeiteinträge</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Automatische Berichte</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Mobile App inklusive</p>
                        </div>
                        <div class="d-flex justify-center mt-6 text-start font-weight-bold text-body-1">
                            <v-hover v-slot="{ isHovering, props }">
                                <v-btn
                                    v-bind="props"
                                    :variant="isHovering ? 'flat' : 'outlined'"
                                    color="#214d63"
                                    class="rounded-lg w-100 btn-transition"
                                    @click.prevent="select(['tide'])"
                                >
                                    Auswählen
                                </v-btn>
                            </v-hover>
                        </div>
                        <div class="mt-2 text-center font-weight-regular text-body-2" style="color: grey">*Preise exkl. 19% Mehrwertsteuer</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" lg="4" class="pa-2">
                <v-card class="pa-3 h-100 rounded-xl elevation border" aria-label="Flow Auftragsmanagement">
                    <v-card-title>
                        <v-img width="120px" eager class="text-start" src="/img/Flow.png"></v-img>
                        <h3>Flow</h3>
                    </v-card-title>
                    <v-card-subtitle>Auftragsmanagement</v-card-subtitle>
                    <v-card-text>
                        <div class="d-flex justify-start align-end mb-6 mt-0 price-row">
                            <h2 class="mb-0 mt-2 text-start font-weight-bold text-h4">4,75€</h2>
                            <v-card-subtitle class="text-body-1 ps-2">/ Mitarbeitende / Monat</v-card-subtitle>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Unbegrenzte Tickets</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Custom Workflows</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Mobile App inklusive</p>
                        </div>
                        <div class="d-flex justify-center mt-6 text-start font-weight-bold text-body-1">
                            <v-hover v-slot="{ isHovering, props }">
                                <v-btn
                                    v-bind="props"
                                    :variant="isHovering ? 'flat' : 'outlined'"
                                    color="#214d63"
                                    class="rounded-lg w-100 btn-transition"
                                    @click.prevent="select(['flow'])"
                                >
                                    Auswählen
                                </v-btn>
                            </v-hover>
                        </div>
                        <div class="mt-2 text-center font-weight-regular text-body-2" style="color: grey">*Preise exkl. 19% Mehrwertsteuer</div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" lg="4" class="pa-2">
                <v-card color="#214d63" class="pa-3 h-100 rounded-xl elevation with-badge" aria-label="Tide und Flow Paket">
                    <span class="pill">Empfohlen</span>
                    <v-card-title class="d-flex flex-column align-start justify-center">
                        <div class="combo-logos d-flex justify-center align-center">
                            <v-img eager class="rounded-lg" width="60" src="/img/Tide.png" />
                            <span class="text-h4 font-weight-bold">+</span>
                            <v-img eager class="rounded-lg" width="60" src="/img/Flow.png" />
                        </div>
                        <h3>Tide + Flow</h3>
                    </v-card-title>
                    <v-card-subtitle>Komplettpaket</v-card-subtitle>
                    <v-card-text>
                        <div class="d-flex justify-start align-end mb-6 mt-0 price-row">
                            <h2 class="mb-0 mt-2 text-start font-weight-bold text-h4">8,00€</h2>
                            <v-card-subtitle class="text-body-1 ps-2">/ Mitarbeitende / Monat</v-card-subtitle>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Alle Tide-Funktionen</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">Alle Flow-Funktionen</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-body-1">1,50 € pro User sparen</p>
                        </div>
                        <div class="d-flex justify-center mt-6 text-start font-weight-bold text-body-1">
                            <v-hover v-slot="{ isHovering, props }">
                                <v-btn
                                    v-bind="props"
                                    :variant="isHovering ? 'flat' : 'outlined'"
                                    color="rgb(0, 189, 157)"
                                    class="rounded-lg w-100 btn-transition"
                                    @click.prevent="select(['tide', 'flow'])"
                                >
                                    Auswählen
                                </v-btn>
                            </v-hover>
                        </div>
                        <div class="mt-2 text-center font-weight-regular text-body-2" style="color: lightgrey">*Preise exkl. 19% Mehrwertsteuer</div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-row class="mb-8 mx-auto d-flex justify-center" dense>
            <v-col cols="12" md="5" class="pa-2">
                <v-card class="pa-3 h-100 rounded-xl elevation border" aria-label="Isa Suchassistent">
                    <v-card-title>
                        <v-img width="120px" eager class="text-start" src="/img/Isa.png"></v-img>
                        <h3>Isa</h3>
                    </v-card-title>
                    <v-card-subtitle>Interner Suchassistent</v-card-subtitle>
                    <v-card-text>
                        <div class="d-flex justify-start align-end mb-6 mt-0 price-row">
                            <h2 class="mb-0 mt-2 text-start font-weight-bold text-h4">1.500€</h2>
                            <v-card-subtitle class="text-body-1 ps-2">einmalig</v-card-subtitle>
                        </div>
                        <v-card-subtitle class="text-wrap">+ monatliche Chat-Kosten nach Verbrauch</v-card-subtitle>
                        <div class="d-flex justify-center mt-6 text-start font-weight-bold text-body-1">
                            <v-hover v-slot="{ isHovering, props }">
                                <v-btn
                                    v-bind="props"
                                    :variant="isHovering ? 'flat' : 'outlined'"
                                    color="#214d63"
                                    class="rounded-lg w-100 btn-transition"
                                    @click.prevent="select(['isa'])"
                                >
                                    Auswählen
                                </v-btn>
                            </v-hover>
                        </div>
                        <v-card-subtitle class="text-wrap mt-2 text-center font-weight-regular text-body-2">
                            *Preise exkl. 19% Mehrwertsteuer
                        </v-card-subtitle>
                        <v-card-subtitle class="text-wrap text-center font-weight-regular text-body-2">
                            *nur in Verbindung mit Tide oder Flow verwendbar
                        </v-card-subtitle>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="5" class="pa-2">
                <v-card class="pa-3 h-100 rounded-xl elevation bg-blue-grey-lighten-5" aria-label="Zusätzliche Services">
                    <v-card-title>
                        <h3 class="text-wrap">Zusätzliche Services</h3>
                    </v-card-title>
                    <v-card-text>
                        <v-row class="my-2 d-flex flex-row align-center">
                            <v-col cols="12" sm="8">
                                <h3 class="text-h6">Setup & Einrichtung</h3>
                                <v-card-subtitle class="ps-0 text-wrap">inkl. Mitarbeitenden- und Admin-Schulung</v-card-subtitle>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <h3 class="text-h6">ab 500€</h3>
                            </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row class="my-2 d-flex flex-row align-center">
                            <v-col cols="12" sm="8">
                                <h3 class="text-h6">Support-Paket</h3>
                                <v-card-subtitle class="ps-0 text-wrap">Wartung, Hosting und Support</v-card-subtitle>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <h3 class="text-h6">ab 100€/Monat</h3>
                            </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-row class="my-2 d-flex flex-row align-center">
                            <v-col cols="12" sm="8">
                                <h3 class="text-h6">Individuelle Anpassungen</h3>
                                <v-card-subtitle class="ps-0 text-wrap">Nach Anforderung</v-card-subtitle>
                            </v-col>
                            <v-col cols="12" sm="4">
                                <h3 class="text-h6">Auf Anfrage</h3>
                            </v-col>
                        </v-row>
                        <v-card-subtitle class="mt-12 text-wrap text-center font-weight-regular text-body-2">
                            *Preise exkl. 19% Mehrwertsteuer
                        </v-card-subtitle>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </section>
</template>

<script setup lang="ts">
const emit = defineEmits<{
    (e: 'selected-modules', modules: string[]): void;
}>();

function select(modules: string[]) {
    emit('selected-modules', modules);
}
</script>

<style scoped>
:deep(.v-card),
:deep(.v-card-title),
:deep(.v-card-text) {
    min-width: 0;
}

.elevation {
    box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap; /* ✅ wichtig für 320px */
    min-width: 0;
}

.logo {
    width: 120px; /* default wie vorher */
    max-width: 100%;
    flex: 0 0 auto;
}

.card-h3 {
    margin: 0;
    min-width: 0;
    word-break: break-word;
}

/* ✅ Preiszeile darf umbrechen */
.price-row {
    flex-wrap: wrap;
    gap: 6px 8px;
}

.hover-shadow {
    position: relative;
    box-shadow: rgb(226, 232, 240) 0px 2px 1px -1px, rgb(226, 232, 240) 0px 1px 1px 0px, rgb(226, 232, 240) 0px 1px 3px 0px;
    transition: box-shadow 0.18s ease;
    &:hover {
        box-shadow: rgb(226, 232, 240) 0px 2px 6px -1px, rgb(226, 232, 240) 0px 6px 7px 0px, rgb(226, 232, 240) 0px 1px 12px 0px;
    }
}

.with-badge {
    position: relative;
    overflow: visible !important;
    padding-top: 14px;
}
.pill {
    position: absolute;
    top: 0;
    left: 50%;
    z-index: 10;
    transform: translate(-50%, -50%);
    background: rgb(0, 189, 157);
    color: #fff;
    border-radius: 999px;
    padding: 2px 10px;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    pointer-events: none;
}

.combo-logos {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 120px;
}

.btn-transition {
    transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

@media (max-width: 960px) {
    .sub-headline {
        font-size: 18px !important;
        margin-inline: 10% !important;
    }
    .headline {
        font-size: 1.5rem !important;
    }
    .cards-band {
        max-width: 90%;
    }
}

@media (max-width: 560px) {
    .headline {
        font-size: 20px !important;
        margin-inline: 10% !important;
    }
    .sub-headline {
        margin-inline: 8% !important;
        font-size: 16px !important;
    }
}

/* ✅ Speziell für 360–320px */
@media (max-width: 360px) {
    .cards-band {
        max-width: 100%;
        padding-inline: 10px;
    }

    .logo {
        width: 96px; /* ✅ weniger Platzverbrauch */
    }

    .pill {
        font-size: 12px;
        padding: 2px 8px;
    }

    /* Optional: etwas weniger Innenabstand, damit nix gequetscht wird */
    :deep(.v-card) {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
}
</style>
