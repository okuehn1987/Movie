<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, RelationPick, Seconds, ShiftEntries, User, WorkLog } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import Absences from './partial/Absences.vue';
import WorkingHours from './partial/WorkingHours.vue';
import { ref } from 'vue';
import { formatDuration } from '@/utils';

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'>;

defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    overtime: number;
    workingHours: { totalHours: number; homeOfficeHours: number };
    currentAbsences: (AbsenceProp &
        RelationPick<'absence', 'user', 'id' | 'first_name' | 'last_name'> &
        RelationPick<'absence', 'absence_type', 'id' | 'abbreviation'>)[];
    lastWeekEntries: (Pick<ShiftEntries, 'id' | 'start' | 'end'> & { duration: Seconds })[];
}>();

const currentPage = ref(1);
</script>

<template>
    <AdminLayout :title="'Dashboard von ' + $page.props.auth.user.first_name + ' ' + $page.props.auth.user.last_name">
        <v-row>
            <v-col cols="12" lg="6">
                <v-row>
                    <v-col cols="12">
                        <WorkingHours :lastWorkLog :overtime :workingHours />
                    </v-col>
                    <v-col cols="12">
                        <v-card title="Zeiten der Letzten 7 tage">
                            <template #append>
                                <v-btn
                                    icon="mdi-eye"
                                    variant="text"
                                    title="Alle Zeiten anzeigen"
                                    data-testid="workingHours"
                                    @click="
                                        router.get(
                                            route('user.workLog.index', {
                                                user: $page.props.auth.user.id,
                                            }),
                                        )
                                    "
                                />
                            </template>
                            <v-card-text>
                                <v-data-table
                                    v-model:page="currentPage"
                                    items-per-page="5"
                                    :items="
                                        lastWeekEntries.map(l => ({
                                            ...l,
                                            start: DateTime.fromSQL(l.start).toFormat('dd.MM.yyyy HH:mm'),
                                            end: l.end ? DateTime.fromSQL(l.end).toFormat('dd.MM.yyyy HH:mm') : 'Jetzt',
                                            duration: formatDuration(l.duration, 'minutes'),
                                        }))
                                    "
                                    :headers="[
                                        { title: 'Start', key: 'start', sortable: false },
                                        { title: 'Ende', key: 'end', sortable: false },
                                        { title: 'Dauer', key: 'duration', sortable: false },
                                    ]"
                                >
                                    <template #bottom>
                                        <v-pagination
                                            v-if="lastWeekEntries.length > 5"
                                            v-model="currentPage"
                                            :length="Math.ceil(lastWeekEntries.length / 5)"
                                        ></v-pagination>
                                    </template>
                                </v-data-table>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
            <v-col cols="12" lg="6">
                <v-row>
                    <v-col cols="12">
                        <Absences :absences="currentAbsences" />
                    </v-col>

                    <!-- <v-col cols="12" md="12" lg="6" xl="4" v-if="supervisor">
                    <v-card title="Vorgesetzter">
                        <v-card-text>
                            {{ supervisor.first_name }}
                            {{ supervisor.last_name }}
                        </v-card-text>
                    </v-card>
                </v-col> -->
                    <v-col cols="12">
                        <v-card>
                            <v-card-title>Informationen</v-card-title>
                            <v-card-item>TODO: to be implemented</v-card-item>
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </AdminLayout>
</template>
