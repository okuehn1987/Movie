<template>
    <section id="headerComponent" aria-labelledby="headerComponentTitle" class="mb-8 mb-md-16 pt-10 pb-md-16">
        <h1 id="headerComponentTitle" class="headline text-center text-h2 pt-8 pt-md-16 mt-md-16 font-weight-black">
            HR Tools & Assistants
            <div style="color: rgb(0, 189, 157); margin-top: 5px">für moderne Teams</div>
        </h1>

        <v-row class="d-flex justify-center align-center mt-8 cards-band" style="direction: rtl">
            <v-col cols="12" md="4">
                <v-img
                    class="mx-auto bg-white rounded-pill"
                    :width="display.smAndUp.value ? 150 : 100"
                    src="/img/Björn.webp"
                    alt="Björn-Braker-Foto"
                />
            </v-col>
            <v-col cols="12" md="8">
                <div :style="`${display.smAndDown.value ? 'width:max-content;margin-inline:auto;max-width:85vw' : ''}`">
                    <div style="direction: ltr" class="sub-headline font-italic text-center text-md-end text-h5">
                        „H(e)rta vereint intelligente Zeiterfassung, effizientes Auftragsmanagement und einen KI-gestützten Suchassistenten in einem
                        bezahlbaren modularen Toolkit."
                    </div>
                    <div style="direction: ltr" class="mt-2 text-end text-h7">Björn Braker - Geschäftsführer</div>
                </div>
            </v-col>
        </v-row>
    </section>
</template>

<script setup lang="ts">
import { useDisplay } from 'vuetify';

const display = useDisplay();
</script>

<style scoped>
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

.hover-swap {
    position: relative;
    box-shadow: rgba(0, 0, 0, 0.2) 0px 2px 1px -1px, rgba(0, 0, 0, 0.14) 0px 1px 1px 0px, rgba(0, 0, 0, 0.12) 0px 1px 3px 0px;
    transition: box-shadow 0.18s ease;
    &:hover {
        box-shadow: rgba(0, 0, 0, 0.2) 0px 2px 6px -1px, rgba(0, 0, 0, 0.14) 0px 6px 7px 0px, rgba(0, 0, 0, 0.12) 0px 1px 12px 0px;
    }

    .face {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-align: center;
        transition: opacity 0.18s ease;
    }

    .face--hover {
        position: absolute;
        inset: 0;
        padding: inherit;
        opacity: 0;
    }
}

.hover-swap:hover .face--hover,
.hover-swap:focus-visible .face--hover {
    opacity: 1;
}
.hover-swap:hover .face--default,
.hover-swap:focus-visible .face--default {
    opacity: 0;
}

.hover-swap.is-active .face--hover {
    opacity: 1;
}
.hover-swap.is-active .face--default {
    opacity: 0;
}
</style>
