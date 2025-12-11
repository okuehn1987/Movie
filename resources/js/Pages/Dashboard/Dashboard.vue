<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DateString, RelationPick, Relations, Seconds, Shift, ShiftEntries, User } from '@/types/types';
import { formatDuration } from '@/utils';
import { DateTime } from 'luxon';
import { useDisplay } from 'vuetify';
import Absences from './partial/Absences.vue';
import WorkingHours from './partial/WorkingHours.vue';
defineProps<{
    user: User &
        RelationPick<'user', 'current_trust_working_hours', 'id' | 'user_id'> &
        Pick<Relations<'user'>, 'latest_work_log'> & {
            current_shift: (Pick<Shift, 'id' | 'start' | 'end'> & { current_work_duration: Seconds }) | null;
        };
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    overtime?: number;
    workingHours?: { totalHours: number; homeOfficeHours: number };
    currentAbsences: { last_name: string; first_name: string; start: DateString; end: DateString; type: string }[];
    lastEntries?: (Pick<ShiftEntries, 'id' | 'start' | 'end'> & { duration: Seconds })[];
}>();
const display = useDisplay();
</script>

<template>
    <AdminLayout :noInlinePadding="display.smAndDown.value" title="Dashboard">
        <v-row>
            <v-col cols="12" lg="6" v-if="can('app', 'tide') && !user.current_trust_working_hours && overtime && workingHours">
                <WorkingHours :user :overtime :workingHours />
            </v-col>
            <v-col cols="12" lg="6">
                <Absences :absences="currentAbsences" />
            </v-col>
            <v-col cols="12" lg="6" v-if="can('app', 'tide') && lastEntries && lastEntries.length > 0">
                <v-card title="Letzte Zeiterfassungen">
                    <v-card-text>
                        <v-data-table-virtual
                            :items="
                                lastEntries.map(l => ({
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
                        ></v-data-table-virtual>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- <v-col cols="12" md="12" lg="6" xl="4" v-if="supervisor">
                    <v-card title="Vorgesetzter">
                        <v-card-text>
                            {{ supervisor.first_name }}
                            {{ supervisor.last_name }}
                        </v-card-text>
                    </v-card>
                </v-col> -->
            <!-- <v-col cols="12">
                        <v-card>
                            <v-card-title>Informationen</v-card-title>
                            <v-card-item>TODO: to be implemented</v-card-item>
                        </v-card>
                    </v-col> -->
        </v-row>
    </AdminLayout>
</template>
