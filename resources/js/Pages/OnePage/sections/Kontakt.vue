<template>
    <section id="kontakt" aria-labelledby="kontaktTitle" class="mb-8 mb-md-16 pt-10 pb-md-16">
        <h2 id="kontaktTitle" class="headline text-h4 text-md-h3 text-center my-8 font-weight-semibold">Kontaktanfrage</h2>

        <v-row class="mb-4">
            <v-col cols="12" class="d-flex justify-center pb-0">
                <span class="sub-headline text-center text-grey-darken-2">Haben Sie Fragen oder möchten Sie mehr über H(e)rta erfahren?</span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center pt-0">
                <span class="sub-headline text-center text-grey-darken-2">Schreiben Sie uns!</span>
            </v-col>
        </v-row>

        <v-card class="kontakt-card elevation border rounded-lg" aria-label="Kontaktformular">
            <v-card-text>
                <v-form @submit.prevent="submitForm">
                    <v-row>
                        <div v-if="honeypot.enabled" :name="`${honeypot.nameFieldName}_wrap`" style="display: none">
                            <input type="text" v-model="honeypot.nameFieldName" :name="honeypot.nameFieldName" :id="honeypot.nameFieldName" />
                            <input type="text" v-model="honeypot.validFromFieldName" :name="honeypot.validFromFieldName" />
                        </div>
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="form.name"
                                label="Name *"
                                required
                                :errorMessages="form.errors['name']"
                                variant="solo-filled"
                                placeholder="Ihr Name"
                            />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field
                                v-model="form.company"
                                label="Unternehmen"
                                :errorMessages="form.errors['company']"
                                variant="solo-filled"
                                placeholder="Ihr Unternehmen"
                            />
                        </v-col>

                        <v-col cols="12">
                            <v-text-field
                                v-model="form.email"
                                label="E-Mail *"
                                :errorMessages="form.errors['email']"
                                type="email"
                                required
                                variant="solo-filled"
                                placeholder="Ihre E-Mail-Adresse"
                            />
                        </v-col>
                        <v-col cols="12" class="pb-2">
                            <v-card-subtitle class="ps-2">Interesse an</v-card-subtitle>
                            <div class="modules-row">
                                <v-checkbox hide-details color="rgb(0, 189, 157)" v-model="form.modules" value="tide" label="Tide"></v-checkbox>
                                <v-checkbox hide-details color="rgb(0, 189, 157)" v-model="form.modules" value="flow" label="Flow"></v-checkbox>
                                <v-checkbox hide-details color="rgb(0, 189, 157)" v-model="form.modules" value="isa" label="Isa"></v-checkbox>
                            </div>
                        </v-col>
                        <v-col cols="12" class="pt-2 pb-6">
                            <v-textarea
                                v-model="form.message"
                                placeholder="Wie können wir Ihnen helfen?"
                                label="Nachricht *"
                                variant="solo-filled"
                                rows="3"
                                required
                                :errorMessages="form.errors['message']"
                            />
                        </v-col>
                    </v-row>
                    <v-btn class="w-100 rounded-lg" style="height: 50px" color="rgb(0, 189, 157)" type="submit">Anfrage senden</v-btn>
                </v-form>
            </v-card-text>
        </v-card>
    </section>
</template>

<script setup lang="ts">
import { showSnackbarMessage } from '@/snackbarService';
import { useForm, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const honeypot = usePage().props['honeypot'] as {
    enabled: boolean;
    nameFieldName: string;
    validFromFieldName: string;
    encryptedValidFrom: string;
};

const props = defineProps<{
    preselectedModules: string[];
}>();

const form = useForm({
    [honeypot.nameFieldName]: '',
    [honeypot.validFromFieldName]: honeypot.encryptedValidFrom,
    name: '',
    company: '',
    email: '',
    modules: [] as string[],
    message: '',
});

watch(
    () => props.preselectedModules,
    modules => {
        form.modules = Array.from(new Set([...form.modules, ...modules]));
    },
    { immediate: true },
);

function submitForm() {
    form.post(route('contact.store'), {
        onSuccess: () => {
            form.reset();
            showSnackbarMessage('Vielen Dank für Ihre Kontaktanfrage!\nWir werden uns schnellstmöglich mit Ihnen in Verbindung setzen.');
        },
        preserveScroll: true,
    });
}
</script>

<style scoped>
.elevation {
    box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.step-stack {
    gap: 16px;
}
.step-row {
    gap: 12px;
}
.step-badge {
    width: 36px;
    height: 36px;
    margin-top: 2px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 960px) {
    .step-stack {
        gap: 20px;
    }
    .step-row {
        gap: 16px;
    }
    .step-badge {
        width: 40px;
        height: 40px;
    }
}

.auto-height-carousel {
    width: 100%;
    height: auto !important;
}

:deep(.auto-height-carousel .v-window),
:deep(.auto-height-carousel .v-window__container),
:deep(.auto-height-carousel .v-window-item),
:deep(.auto-height-carousel .v-carousel-item) {
    height: auto !important;
}

:deep(.auto-height-carousel .v-responsive),
:deep(.auto-height-carousel .v-img) {
    width: 100%;
    height: auto !important;
}

:deep(.auto-height-carousel img) {
    display: block;
    width: 100%;
    height: auto;
}

.kontakt-card {
    width: 100%;
    max-width: 640px;
    margin-inline: auto;
    padding: 24px;
}

.sub-headline {
    font-size: 20px;
    margin-inline: 27%;
    display: block;
}

.modules-row {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 12px;
}

:deep(.v-card),
:deep(.v-card-text),
:deep(.v-input) {
    min-width: 0;
}

@media (max-width: 960px) {
    .sub-headline {
        font-size: 18px !important;
        margin-inline: 10% !important;
    }
    .headline {
        font-size: 1.5rem !important;
    }
    .kontakt-card {
        max-width: 90%;
        padding: 20px;
    }
}

@media (max-width: 560px) {
    .headline {
        font-size: 20px !important;
        margin-inline: 10% !important;
    }
    .sub-headline {
        font-size: 16px !important;
        margin-inline: 8% !important;
    }
    .kontakt-card {
        max-width: 100%;
        padding: 16px;
    }

    .modules-row {
        flex-direction: column;
        gap: 0;
    }
}

@media (max-width: 360px) {
    .kontakt-card {
        padding: 12px;
    }

    .sub-headline {
        margin-inline: 6% !important;
    }

    :deep(.v-field__input) {
        padding-inline: 10px;
    }
}
</style>
