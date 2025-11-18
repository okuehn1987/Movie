<script setup lang="ts">
import { HomeOfficeDay, HomeOfficeDayGenerator, User } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type HomeOfficeDayProp = Pick<HomeOfficeDay, 'id' | 'user_id' | 'date'> & {
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
    homeOfficeDayGenerator: Pick<HomeOfficeDayGenerator, 'id' | 'start' | 'end' | 'created_as_request'>;
};

const props = defineProps<{
    homeOfficeDayRequests: HomeOfficeDayProp[];
}>();

const homeOfficeDayDialog = ref<HomeOfficeDayProp | null>(
    props.homeOfficeDayRequests?.find(homeOfficeDay => homeOfficeDay.homeOfficeDayGenerator.id == Number(route().params['openHomeOffice'])) ?? null,
);
const showHomeOfficeDayDialog = ref(!!homeOfficeDayDialog.value);
const submitAbsenceSuccess = ref(false);

const currentPage = ref(1);

function openAbsence(_: unknown, row: { item: { id: HomeOfficeDayProp['id'] } }) {
    const homeOfficeDay = props.homeOfficeDayRequests?.find(p => p.id === row.item.id);
    if (!homeOfficeDay) return;
    homeOfficeDayDialog.value = homeOfficeDay;
    showHomeOfficeDayDialog.value = true;
}

function changeAbsenceStatus(accepted: boolean) {
    if (!homeOfficeDayDialog.value) return;
    router.patch(
        route('homeOfficeDay.update', { homeOfficeDay: homeOfficeDayDialog.value.id }),
        { accepted },
        {
            onSuccess: () => {
                showHomeOfficeDayDialog.value = false;
                submitAbsenceSuccess.value = true;
            },
        },
    );
}
</script>
<template>
    <v-data-table
        hover
        items-per-page="5"
        v-model:page="currentPage"
        no-data-text="keine Abwesenheitsanträge vorhanden."
        @click:row="openAbsence"
        :items="
            homeOfficeDayRequests.map(homeOfficeDay => ({
                id: homeOfficeDay.id,
                user: homeOfficeDay.user.first_name + ' ' + homeOfficeDay.user.last_name,
                start: DateTime.fromSQL(homeOfficeDay.homeOfficeDayGenerator.start).toFormat('dd.MM.yyyy'),
                end: DateTime.fromSQL(homeOfficeDay.homeOfficeDayGenerator.end).toFormat('dd.MM.yyyy'),
            }))
        "
        :headers="[
            { title: 'Mitarbeiter', key: 'user' },
            { title: 'Von', key: 'start' },
            { title: 'Bis', key: 'end' },
        ]"
    >
        <template v-slot:bottom>
            <v-pagination
                v-if="homeOfficeDayRequests.length > 5"
                v-model="currentPage"
                :length="Math.ceil(homeOfficeDayRequests.length / 5)"
            ></v-pagination>
        </template>
    </v-data-table>

    <v-dialog v-if="homeOfficeDayDialog" v-model="showHomeOfficeDayDialog" max-width="1000">
        <v-card :title="'Abwesenheit von ' + homeOfficeDayDialog.user.first_name + ' ' + homeOfficeDayDialog.user.last_name">
            <template #append>
                <v-btn icon variant="text" @click="showHomeOfficeDayDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-divider></v-divider>
            <v-card-text>
                <v-row>
                    <!-- <v-col cols="12">
                        <v-text-field label="Abwesenheitsgrund" v-model="homeOfficeDayDialog.absence_type.name" readonly>Homeoffice</v-text-field>
                    </v-col> -->
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Von:"
                            :model-value="DateTime.fromSQL(homeOfficeDayDialog.homeOfficeDayGenerator.start).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Bis:"
                            :model-value="DateTime.fromSQL(homeOfficeDayDialog.homeOfficeDayGenerator.end).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-col cols="12" class="d-flex justify-end ga-3">
                <v-btn color="error" @click.stop="changeAbsenceStatus(false)">Ablehnen</v-btn>
                <v-btn color="success" @click.stop="changeAbsenceStatus(true)">Akzeptieren</v-btn>
            </v-col>
        </v-card>
    </v-dialog>
</template>
