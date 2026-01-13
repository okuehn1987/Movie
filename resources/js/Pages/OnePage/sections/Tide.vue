<template>
    <section id="tide" aria-labelledby="tideTitle" class="pt-10 pb-10 pb-md-16">
        <div style="width: 100%">
            <v-img width="200px" eager class="mb-md-6 pt-4 mx-auto" src="/img/Tide-Transparent.png" alt="Tide-Logo"></v-img>
        </div>

        <v-row class="mb-2 pt-2" style="font-size: 20px">
            <v-col cols="12" class="d-flex justify-center mb-md-2">
                <span class="sub-headline text-center text-grey-darken-2 px-4 px-md-0">
                    Arbeitszeiten erfassen, Abwesenheiten steuern und Teams transparent managen – einfach, flexibel und datenschutzkonform.
                </span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center mb-md-2">
                <span class="sub-headline text-center text-grey-darken-2">
                    <b>Tide</b>
                    vereint Zeiterfassung, Abwesenheitsmanagement und Teamübersicht in einer zentralen, intuitiven Lösung. Ob Büro, Homeoffice oder
                    hybrid:
                </span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center mb-md-8">
                <span class="sub-headline text-center text-grey-darken-2">
                    <b>Tide</b>
                    passt sich euren Arbeitsmodellen an – nicht umgekehrt.
                </span>
            </v-col>
        </v-row>

        <v-row class="px-3 px-sm-6 px-md-0" dense>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Informationen zu Arbeitszeiten & Zeitkonten">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-clock-time-four-outline
                            </v-icon>
                            <h2 class="text-h6 mb-0 text-wrap">Arbeitszeiten & Zeitkonten</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="my-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Zeiterfassung über „Kommen" und „Gehen"</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Unterstützung verschiedener Arbeitszeitmodelle</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Gleitzeitkonto standardmäßig aktiv</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Individuelle Zeitkonten: Jahres- oder Lebensarbeitszeitkonto
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Zeitkorrekturen als Antrag mit Genehmigungsworkflow
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Gleitzeitampel</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Informationen zu Abwesenheiten & Kalender">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-calendar-blank-outline
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Abwesenheiten & Kalender</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Abwesenheiten direkt im Kalender bei der Führungskraft beantragen
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Status-Benachrichtigungen bei Genehmigung oder Ablehnung
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">datenschutzkonformer Kalender</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Abwesenheitsgründe individuell hinzufügen, entfernen oder anpassen
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">optional: Geburtstage im Teamkalender</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Mehr Informationen zu Homeoffice & Transparenz">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-home-outline
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Homeoffice & Transparenz</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Homeoffice flexibel definierbar: feste Tage, allgemeine Nutzung oder prozentuale Wochenanteile
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Anwesenheiten auf einen Blick</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Live-Übersicht für Führungskräfte</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Mehr Informationen zu Auswertungen & Organisation">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-chart-line
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Auswertungen & Organisation</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Einfache Erstellung von Zeitnachweisen zur Abrechnung oder für das Lohnbüro
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Frei definierbare und speicherbare Filter</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Vertretungsregelungen für Führungskräfte</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Automatische Erstellung eines Organigramms</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </section>
</template>

<style scoped>
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
</style>
