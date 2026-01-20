<template>
    <section id="flow" aria-labelledby="flowTitle" class="pt-10 pb-10 pb-md-16">
        <div style="width: 100%">
            <v-img width="200px" eager class="mb-md-6 pt-4 mx-auto" src="/img/Flow-Transparent.png" alt="Flow-Logo"></v-img>
        </div>

        <v-row class="mb-2 pt-2" style="font-size: 20px">
            <v-col cols="12" class="d-flex justify-center mb-md-2">
                <span class="sub-headline text-center text-grey-darken-2 px-4 px-md-0">
                    Planen, bearbeiten und dokumentieren Sie Aufträge strukturiert, transparent und ohne Informationsverlust – vom ersten Ticket bis
                    zum Leistungsnachweis.
                </span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center mb-md-2">
                <span class="sub-headline text-center text-grey-darken-2">
                    <b>Flow</b>
                    bündelt Auftragsplanung, Ticketbearbeitung und Dokumentation in einer zentralen Lösung.
                </span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center mb-md-8">
                <span class="sub-headline text-center text-grey-darken-2">
                    Von der einfachen Ticketerstellung über die Zusammenarbeit im Team bis hin zur revisionssicheren Berichterstellung sorgt
                    <b>Flow</b>
                    für klare Prozesse, Transparenz und Effizienz im Arbeitsalltag.
                </span>
            </v-col>
        </v-row>

        <v-row class="cards-band px-3 px-sm-6 px-md-0" dense>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Informationen zu strukturierter Ticketerstellung">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-file-document-outline
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Strukturierte Ticketerstellung</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Einfache Erstellung von Aufträgen mit Kundenauswahl und Terminplanung
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Zuweisung an Mitarbeitende inklusive Priorisierung</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Nachträgliche Auftragserfassung und Leistungsnachweise
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Optionale Kunden-Logins zur Ticketanlage</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Informationen zu effizienter Zusammenarbeit">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-account-multiple-outline
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Effiziente Zusammenarbeit</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Aufträge annehmen, weiterreichen oder abschließen</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Benachrichtigungen bei Zuweisungen und Statusänderungen
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Abwesenheiten im Team sichtbar</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Kundenbezogene Tickets klar filterbar und auffindbar
                            </p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Mehr Informationen zu lückenloser Dokumentation">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-shield-outline
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Lückenlose Dokumentation</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Berichte direkt am Auftrag hinterlegen</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">
                                Einsatzort erfassen: Betriebsstätte, Homeoffice oder Kundenstandort
                            </p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Zeiten und Materialien vollständig dokumentieren</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Dateien direkt an Tickets oder Kunden anhängen</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" class="pa-2">
                <v-card class="pa-3 h-100 rounded-lg elevation border" tabindex="0" aria-label="Mehr Informationen zu Kundenmanagement & Wissen">
                    <v-card-title class="pt-6">
                        <div class="d-flex align-center flex-wrap">
                            <v-icon
                                class="feature-icon rounded-lg me-4"
                                style="width: 50px; height: 50px; font-size: 28px; background-color: rgba(0, 189, 157, 0.1); color: rgb(0, 189, 157)"
                            >
                                mdi-chart-line
                            </v-icon>
                            <h2 class="text-h6 text-wrap">Kundenmanagement & Wissen</h2>
                        </div>
                    </v-card-title>
                    <v-card-text class="mt-4">
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Kunden, Standorte und Kontakte zentral pflegen</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Direkte Verknüpfung mit Telefon, E-Mail und Maps</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Eigene Ordnerstrukturen und übergreifende Suche</p>
                        </div>
                        <div class="my-2 d-flex flex-row align-top justify-start">
                            <v-icon color="rgb(0, 189, 157)" class="me-3" style="margin-top: 2px">mdi-checkbox-marked-circle-outline</v-icon>
                            <p class="font-weight-regular text-sm-body-1 text-body-2 flex-wrap">Wiedervorlagefunktion und internes Wiki</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </section>
</template>

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
</style>
