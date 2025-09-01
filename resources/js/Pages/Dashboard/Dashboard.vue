<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DateString, Relations, Seconds, Shift, ShiftEntries, User } from '@/types/types';
import { formatDuration } from '@/utils';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import Absences from './partial/Absences.vue';
import WorkingHours from './partial/WorkingHours.vue';

defineProps<{
    user: User &
        Pick<Relations<'user'>, 'latest_work_log'> & {
            current_shift: (Pick<Shift, 'id' | 'start' | 'end'> & { current_work_duration: Seconds }) | null;
        };
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    overtime: number;
    workingHours: { totalHours: number; homeOfficeHours: number };
    currentAbsences: { name: string; end: DateString; type: string }[];
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
                        <WorkingHours :user :overtime :workingHours />
                    </v-col>
                    <v-col cols="12">
                        <v-card title="Zeiten der Aktuellen Woche">
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
