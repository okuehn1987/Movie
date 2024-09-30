<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { User, WorkLog } from "@/types/types";
import { useNow } from "@/utils";
import { router, usePage } from "@inertiajs/vue3";
import { DateTime } from "luxon";

const props = defineProps<{
    lastWorkLog: Pick<WorkLog, "id" | "start" | "end" | "is_home_office">;
    supervisor: Pick<User, "id" | "first_name" | "last_name">;
}>();
const page = usePage();
const now = useNow();
// momentanen überstunden ohne aktuellen Log
// bei abwesenheit buttons disablen

// Kommen", "Gehen"-Button dürfen bei vergessener "Gehen"-Buchung am Vortag, am Folgetag nicht angezeigt werden
//    Stattdessen Text "Es fehlt eine Meldung. Bitte Zeitkorrektur"
//       Zeitkorrektur = Link zur Zeitkorrektur

// anzeige aktueller Vorgesetzter
// anzeige wichtiger Informationen (falls vorhanden)

function changeWorkStatus(home_office = false) {
    router.post(route("workLog.store"), {
        is_home_office: home_office,
        id: props.lastWorkLog.end ? null : props.lastWorkLog.id,
    });
}
</script>
<template>
    <AdminLayout title="Dashboard">
        <v-container>
            <v-row>
                <v-col cols="12" md="4">
                    <v-card>
                        <v-toolbar
                            color="primary"
                            title="Arbeitszeit"
                        ></v-toolbar>
                        <v-card-text>
                            <div>
                                <div>
                                    Aktuelle Stunden:
                                    {{
                                        now
                                            .diff(
                                                DateTime.fromSQL(
                                                    lastWorkLog.start
                                                )
                                            )
                                            .toFormat("hh:mm")
                                    }}
                                </div>
                                <div>
                                    {{
                                        DateTime.fromSQL(
                                            lastWorkLog.start
                                        ).toFormat("dd.MM HH:mm")
                                    }}
                                </div>
                            </div>
                            <div>
                                <template
                                    v-if="
                                        (page.props.auth.user.home_office &&
                                            lastWorkLog.end) ||
                                        (lastWorkLog.is_home_office &&
                                            !lastWorkLog.end)
                                    "
                                >
                                    <v-btn
                                        @click.stop="changeWorkStatus(true)"
                                        color="primary"
                                        class="me-2"
                                    >
                                        {{
                                            lastWorkLog.end
                                                ? "Kommen Homeoffice"
                                                : "Gehen Homeoffice"
                                        }}</v-btn
                                    >
                                </template>
                                <v-btn
                                    @click.stop="changeWorkStatus()"
                                    color="primary"
                                    v-if="
                                        lastWorkLog.end ||
                                        !lastWorkLog.is_home_office
                                    "
                                >
                                    {{ lastWorkLog.end ? "Kommen" : "Gehen" }}
                                </v-btn>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4">
                    <v-card>
                        <v-toolbar
                            color="primary"
                            title="Vorgesetzter"
                        ></v-toolbar>
                        <v-card-text>
                            {{ supervisor.first_name }}
                            {{ supervisor.last_name }}
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4">
                    <v-card>
                        <v-toolbar
                            color="primary"
                            title="Informationen"
                        ></v-toolbar>
                        <v-card-text> text</v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AdminLayout>
</template>
