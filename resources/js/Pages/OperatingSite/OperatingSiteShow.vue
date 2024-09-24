<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { OperatingSite, OperatingTime } from "@/types/types";
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
    operatingSite: OperatingSite;
    operatingTimes: OperatingTime[];
}>();

const WEEKDAYS = [
    { key: "monday", value: "Montag" },
    { key: "tuesday", value: "Dienstag" },
    { key: "wednesday", value: "Mittwoch" },
    { key: "thursday", value: "Donnerstag" },
    { key: "friday", value: "Freitag" },
    { key: "saturday", value: "Samstag" },
    { key: "sunday", value: "Sonntag" },
];

const siteForm = useForm({
    email: props.operatingSite.email,
    fax: props.operatingSite.fax,
    phone_number: props.operatingSite.phone_number,
    street: props.operatingSite.street,
    country: props.operatingSite.country,
    city: props.operatingSite.city,
    address_suffix: props.operatingSite.address_suffix,
    house_number: props.operatingSite.house_number,
    federal_state: props.operatingSite.federal_state,
    zip: props.operatingSite.zip,
    name: props.operatingSite.name,
    is_head_quarter: props.operatingSite.is_head_quarter,
});

const operatingTimeForm = useForm({
    start: "",
    end: "",
    type: "monday",
    operating_site_id: props.operatingSite.id,
});

function submit() {
    console.log();
    siteForm.patch(
        route("operatingSite.update", {
            operatingSite: props.operatingSite.id,
        }),
        {
            onSuccess: () => siteForm.reset(),
        }
    );
}

function submitOperatingTime() {
    operatingTimeForm.post(route("operatingTime.store"), {
        onSuccess: () => operatingTimeForm.reset(),
    });
}

const tab = ref<"Betriebsort" | "Betriebszeiten">("Betriebsort");
</script>
<template>
    <AdminLayout title="Standort">
        <v-container>
            <v-alert
                class="mb-4"
                v-if="siteForm.wasSuccessful"
                closable
                color="success"
                >Betriebsstätte wurde erfolgreich aktualisiert.</v-alert
            >
            <v-card>
                <v-toolbar
                    color="primary"
                    :title="operatingSite.name ?? 'Betriebsort'"
                ></v-toolbar>

                <div class="d-flex flex-row">
                    <v-tabs v-model="tab" color="primary" direction="vertical">
                        <v-tab
                            prepend-icon="mdi-account"
                            text="Betriebsort"
                            value="Betriebsort"
                        ></v-tab>
                        <v-tab
                            prepend-icon="mdi-clock-outline"
                            text="Betriebszeiten"
                            value="Betriebszeiten"
                        ></v-tab>
                    </v-tabs>
                    <v-tabs-window v-model="tab" class="w-100">
                        <v-tabs-window-item value="Betriebsort">
                            <v-form @submit.prevent="submit">
                                <v-card-text>
                                    <h3>Kontaktinformationen</h3>
                                    <v-divider class="mb-8"></v-divider>
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Name"
                                                v-model="siteForm.name"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Email"
                                                v-model="siteForm.email"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Telefonnummer"
                                                v-model="siteForm.phone_number"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Fax"
                                                v-model="siteForm.fax"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>
                                    <h3 class="mt-4">Adresse</h3>
                                    <v-divider class="mb-8"></v-divider>
                                    <v-row>
                                        <v-col cols="12" md="4">
                                            <v-text-field
                                                label="Straße"
                                                v-model="siteForm.street"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field
                                                label="Hausnummer"
                                                v-model="siteForm.house_number"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field
                                                label="Adresszusatz"
                                                v-model="
                                                    siteForm.address_suffix
                                                "
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Postleitzahl"
                                                v-model="siteForm.zip"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Ort"
                                                v-model="siteForm.city"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Bundesland"
                                                v-model="siteForm.federal_state"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-checkbox
                                                label="Hauptsitz?"
                                                v-model="
                                                    siteForm.is_head_quarter
                                                "
                                                variant="underlined"
                                            ></v-checkbox>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                                <v-card-actions>
                                    <div class="d-flex justify-end w-100">
                                        <v-btn
                                            type="submit"
                                            color="primary"
                                            variant="elevated"
                                        >
                                            Aktualisieren
                                        </v-btn>
                                    </div>
                                </v-card-actions>
                            </v-form>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="Betriebszeiten">
                            <v-alert
                                class="mb-4"
                                v-if="operatingTimeForm.wasSuccessful"
                                closable
                                color="success"
                                >Arbeitszeit wurde erfolgreich
                                hinzugefügt.</v-alert
                            >
                            <v-form @submit.prevent="submitOperatingTime">
                                <v-card-text>
                                    <h3>Aktuelle Arbeitszeiten</h3>
                                    <v-divider class="mb-8"></v-divider>
                                    <v-list>
                                        <div
                                            class="d-flex align-center"
                                            v-for="operatingTime in operatingTimes"
                                            :key="operatingTime.id"
                                        >
                                            <v-list-item
                                                :title="
                                                    WEEKDAYS.find(
                                                        (e) =>
                                                            e.key ===
                                                            operatingTime.type
                                                    )?.value
                                                "
                                                :subtitle="
                                                    operatingTime.start +
                                                    ' Uhr' +
                                                    ' - ' +
                                                    operatingTime.end +
                                                    ' Uhr'
                                                "
                                            >
                                            </v-list-item>
                                            <Link
                                                method="delete"
                                                :href="
                                                    route(
                                                        'operatingTime.destroy',
                                                        {
                                                            operatingTime:
                                                                operatingTime.id,
                                                        }
                                                    )
                                                "
                                            >
                                                <v-btn
                                                    color="error"
                                                    size="small"
                                                    ><v-icon
                                                        icon="mdi-delete"
                                                    ></v-icon
                                                ></v-btn>
                                            </Link>
                                        </div>
                                    </v-list>
                                    <div class="my-8">
                                        <h3>Arbeitszeiten hinzufügen</h3>
                                        <v-divider></v-divider>
                                    </div>
                                    <v-row>
                                        <v-col cols="12" md="4">
                                            <v-select
                                                v-model="operatingTimeForm.type"
                                                label="Wochentag"
                                                :items="WEEKDAYS"
                                                item-title="value"
                                                item-value="key"
                                                variant="underlined"
                                            ></v-select>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field
                                                type="time"
                                                v-model="
                                                    operatingTimeForm.start
                                                "
                                                label="Beginn Arbeitstages"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field
                                                type="time"
                                                v-model="operatingTimeForm.end"
                                                label="Beginn Arbeitstages"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                                <v-card-actions>
                                    <div class="d-flex justify-end w-100">
                                        <v-btn
                                            type="submit"
                                            color="primary"
                                            variant="elevated"
                                        >
                                            Hinzufügen
                                        </v-btn>
                                    </div>
                                </v-card-actions>
                            </v-form>
                        </v-tabs-window-item>
                    </v-tabs-window>
                </div>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
