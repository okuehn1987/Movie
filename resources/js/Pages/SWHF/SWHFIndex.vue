<script setup lang="ts">
import { SpecialWorkingHoursFactor, Weekday } from "@/types/types";
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps<{
    special_working_hours_factors: SpecialWorkingHoursFactor[];
}>();

const WEEKDAYS = [
    { key: "monday", value: "Montag" },
    { key: "tuesday", value: "Dienstag" },
    { key: "wednesday", value: "Mittwoch" },
    { key: "thursday", value: "Donnerstag" },
    { key: "friday", value: "Freitag" },
    { key: "saturday", value: "Samstag" },
    { key: "sunday", value: "Sonntag" },
]; //FIXME: fehlende Feiertage oder nachtarbeit , etc. adden

const showDialog = ref(false);

const swhfForm = useForm({
    id: null as number | null,
    type: "" as Weekday | "holiday" | "nightshift",
    extra_charge: 0,
});

function edit(item: SpecialWorkingHoursFactor) {
    swhfForm.id = item.id;
    swhfForm.type = item.type;
    swhfForm.extra_charge = item.extra_charge;

    showDialog.value = true;
}

function create() {
    if (swhfForm.id != null) swhfForm.reset();

    showDialog.value = true;
}

function submit() {
    swhfForm.post(route("specialWorkingHoursFactor.store"), {
        onSuccess: () => {
            swhfForm.reset();
            showDialog.value = false;
        },
    });
}
</script>
<template>
    <v-dialog max-width="1000" v-model="showDialog">
        <v-card>
            <v-toolbar
                color="primary"
                title="Besondere Arbeitszeitzuschläge erstellen"
                class="mb-4"
            ></v-toolbar>

            <v-form @submit.prevent="submit">
                <v-row>
                    <v-col cols="12" md="6">
                        <v-select
                            class="px-8"
                            v-model="swhfForm.type"
                            label="Wochentag"
                            :error-messages="swhfForm.errors.type"
                            :items="
                                WEEKDAYS.filter(
                                    (e) =>
                                        !special_working_hours_factors.find(
                                            (a) => e.key == a.type
                                        )
                                )
                            "
                            item-title="value"
                            item-value="key"
                            variant="underlined"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            class="px-8"
                            label="Faktor Zuschlag"
                            required
                            :error-messages="swhfForm.errors.extra_charge"
                            v-model="swhfForm.extra_charge"
                            variant="underlined"
                        ></v-text-field>
                    </v-col>
                </v-row>

                <v-card-actions>
                    <div class="d-flex justify-end w-100">
                        <v-btn
                            color="error"
                            class="me-2"
                            variant="elevated"
                            @click="
                                () => {
                                    showDialog = false;
                                    swhfForm.reset();
                                }
                            "
                        >
                            Abbrechen
                        </v-btn>
                        <v-btn type="submit" color="primary" variant="elevated">
                            Speichern
                        </v-btn>
                    </div>
                </v-card-actions>
            </v-form>
        </v-card>
    </v-dialog>

    <v-card flat>
        <v-alert
            color="success"
            closable
            class="my-2 mx-4"
            v-if="swhfForm.wasSuccessful"
            >Besonderer Arbeitszeitzuschlag erfolgreich gespeichert.</v-alert
        >
        <v-data-table-virtual
            hover
            :items="
                special_working_hours_factors.map((e) => ({
                    ...e,
                    type: WEEKDAYS.find((a) => a.key === e.type)?.value as Weekday,
                }))
            "
            :headers="[
                { title: 'Tag', key: 'type' },
                { title: 'Faktor', key: 'extra_charge' },
                { title: '', key: 'actions', align: 'end' },
            ]"
        >
            <template v-slot:header.actions>
                <v-btn @click.stop="create()" color="primary">
                    <v-icon icon="mdi-plus"></v-icon>
                </v-btn>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-btn color="primary" class="me-2" @click.stop="edit(item)"
                    ><v-icon icon="mdi-pencil"></v-icon
                ></v-btn>
                <v-dialog max-width="1000">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="error">
                            <v-icon size="large" icon="mdi-delete"> </v-icon>
                        </v-btn>
                    </template>

                    <template v-slot:default="{ isActive }">
                        <v-card
                            ><v-toolbar
                                color="primary"
                                class="mb-4"
                                title="Mitarbeiter löschen"
                            ></v-toolbar>
                            <v-card-text
                                >Bist du dir sicher, dass du diesen
                                Arbeitszeitzuschlag entfernen möchtest?
                            </v-card-text>
                            <v-card-actions>
                                <div class="d-flex justify-end w-100">
                                    <v-btn
                                        color="error"
                                        variant="elevated"
                                        class="me-2"
                                        @click="isActive.value = false"
                                    >
                                        Abbrechen
                                    </v-btn>
                                    <Link
                                        :href="
                                            route(
                                                'specialWorkingHoursFactor.destroy',
                                                {
                                                    specialWorkingHoursFactor:
                                                        item.id,
                                                }
                                            )
                                        "
                                        method="delete"
                                    >
                                        <v-btn
                                            type="submit"
                                            color="primary"
                                            variant="elevated"
                                            >Löschen
                                        </v-btn>
                                    </Link>
                                </div>
                            </v-card-actions>
                        </v-card>
                    </template>
                </v-dialog>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
