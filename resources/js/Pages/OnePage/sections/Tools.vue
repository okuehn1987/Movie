<template>
    <section id="tools" aria-labelledby="toolsTitle" class="mb-8 mb-md-16 pt-10 pb-md-16">
        <h2 id="toolsTitle" class="headline text-center mt-8 font-weight-semibold text-h4 text-md-h3" style="font-size: 2.5rem">
            Das komplette HR-Toolkit
        </h2>

        <v-row class="mb-2 pt-2">
            <v-col cols="12" class="d-flex justify-center">
                <span class="sub-headline text-center text-grey-darken-2 mb-0 px-4 px-md-0">
                    Wählen Sie die Tools, die zu Ihrem Unternehmen passen – einzeln oder als Paket.
                </span>
            </v-col>
        </v-row>
        <v-row class="px-3 pt-4 d-flex justify-center align-stretch" dense>
            <v-col v-for="(card, index) in cards" :key="index" cols="12" sm="6" lg="4" class="d-flex pa-2">
                <v-card class="pa-3 w-100 rounded-lg no-hover-dim border" :href="card.link" tabindex="0" :aria-label="card.aria" :ripple="false">
                    <v-card-text class="pt-2 card-content">
                        <v-img eager class="tool-logo" :src="'/img/' + card.img" :alt="card.title"></v-img>
                        <h3 class="mb-0 mt-2 text-start text-h6 card-title">{{ card.title }}</h3>
                        <p class="mb-0 mt-4 text-start font-weight-regular text-body-2 text-sm-body-1">{{ card.text }}</p>
                        <v-hover v-slot="{ isHovering, props }">
                            <p
                                class="mt-4 text-start font-weight-bold text-body-1 d-inline-flex align-center ga-1 more-link"
                                v-bind="props"
                                :style="isHovering ? 'color: rgba(0, 189, 157, 0.7)' : 'color: rgb(0, 189, 157)'"
                            >
                                Mehr erfahren
                                <v-icon>mdi-chevron-right</v-icon>
                            </p>
                        </v-hover>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </section>
</template>

<script setup lang="ts">
const cards = [
    {
        img: 'Tide.png',
        title: 'Zeiterfassung',
        text: 'Arbeitszeiten erfassen, Abwesenheiten steuern und Teams transparent managen.',
        link: '#tide',
        aria: 'Mehr Informationen zur Zeiterfassung',
    },
    {
        img: 'Flow.png',
        title: 'Auftragsmanagement',
        text: 'Aufträge zentral erfassen, steuern und dokumentieren.',
        link: '#flow',
        aria: 'Mehr Informationen zum Auftragsmanagement',
    },
    {
        img: 'Isa.png',
        title: 'Interner Suchassistent',
        text: 'Durchsucht alle ihr zur Verfügung gestellten Dateien oder freigegebenen Daten aus Flow.',
        link: '#isa',
        aria: 'Mehr Informationen zum internen Suchassistenten',
    },
];
</script>

<style scoped>
.cards-band {
    padding-top: 30px;
    max-width: 1100px;
    margin-inline: auto;
    padding-inline: 12px;
}

.card-content {
    display: grid;
    grid-template-rows: 150px auto 1fr auto;
    height: 100%;
}

.card-title {
    min-height: 28px;
}

.tool-logo {
    width: 150px;
    max-width: 100%;
    height: 150px;
}

.tool-logo :deep(img) {
    object-fit: contain;
    height: 100%;
}

.more-link {
    align-self: end;
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
        max-width: none;
        padding-inline: 12px;
    }
}
@media (max-width: 560px) {
    .headline {
        font-size: 28px !important;
        margin-inline: 10% !important;
    }
    .cards-band {
        max-width: none;
        padding-inline: 12px;
    }
}

@media (max-width: 360px) {
    .cards-band {
        padding-inline: 10px;
    }
    .tool-logo {
        width: 120px;
    }
    .sub-headline {
        font-size: 16px;
    }
}

.no-hover-dim {
    box-shadow: 4px 8px 24px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.2s ease, box-shadow 0.2s ease;
}
.no-hover-dim:hover {
    box-shadow: 8px 8px 32px rgba(0, 0, 0, 0.12) !important;
    transform: translateY(-1px);
}

.no-hover-dim :deep(.v-card__overlay),
.no-hover-dim :deep(.v-card__underlay) {
    opacity: 0 !important;
}
</style>
