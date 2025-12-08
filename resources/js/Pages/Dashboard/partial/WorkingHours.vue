<script setup lang="ts">
import { Relations, Seconds, Shift, User } from '@/types/types';
import { formatDuration, useNow } from '@/utils';
import { router, usePage } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { computed } from 'vue';

const props = defineProps<{
    overtime: number;
    workingHours: { totalHours: number; homeOfficeHours: number };
    user: User &
        Pick<Relations<'user'>, 'latest_work_log'> & {
            current_shift: (Pick<Shift, 'id' | 'start' | 'end'> & { current_work_duration: Seconds }) | null;
        };
}>();

const page = usePage();
const now = useNow();

function changeWorkStatus(is_home_office = false) {
    router.post(route('workLog.store'), {
        is_home_office,
    });
}

const currentWorkingHours = computed(() => {
    if (!props.user.latest_work_log) return 0;
    return props.user.latest_work_log.end
        ? props.user.current_shift?.current_work_duration ?? 0
        : now.value.diff(DateTime.fromSQL(props.user.latest_work_log?.start || '')).as('seconds') +
              (props.user.current_shift?.current_work_duration ?? 0);
});

const lastActionText = computed(() => {
    if (!props.user.latest_work_log) return;
    const endTime = DateTime.fromSQL(props.user.latest_work_log.end ?? props.user.latest_work_log.start);
    const diff = now.value.diff(endTime);
    if (diff.as('minutes') < 1) {
        return 'Jetzt';
    }
    if (diff.as('hours') < 1) {
        return 'vor ' + Math.floor(diff.as('minutes')) + ' minuten';
    }
    if (endTime.day !== now.value.day) {
        return endTime.toFormat('dd.MM. - HH:mm') + ' Uhr';
    }
    return endTime.toFormat('HH:mm') + ' Uhr';
});
</script>
<template>
    <v-card title="Arbeitszeit">
        <v-card-text>
            <v-row>
                <v-col cols="12" sm="6">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="blue-grey" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-clock-check" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Aktuelle Schicht
                            <div class="text-h6">
                                {{ formatDuration(Math.max(0, currentWorkingHours), 'minutes') }}
                            </div>
                        </div>
                    </div>
                </v-col>

                <v-col cols="12" sm="6">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="orange" rounded size="40" class="elevation-2 text-white">
                            <v-icon size="24" icon="mdi-clock-plus" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Überstunden
                            <div class="text-h6">
                                {{ formatDuration(overtime, 'minutes') }}
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" v-if="user.latest_work_log">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="blue" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-timer" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            {{ user.latest_work_log.end ? 'Gehen' : 'Kommen' }}
                            <div class="text-h6">{{ lastActionText }}</div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" v-if="$page.props.auth.user.home_office || workingHours.homeOfficeHours">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="green" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-clock-check" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Woche Homeoffice
                            <div class="text-h6">
                                <!-- TODO: should probably get live updates like currentWorkingHours -->
                                {{ formatDuration(workingHours.homeOfficeHours, 'minutes') }}
                                <span v-if="workingHours.totalHours">
                                    ({{ Math.round((workingHours.homeOfficeHours / workingHours.totalHours) * 100) }}%)
                                </span>
                            </div>
                        </div>
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col
                    cols="12"
                    md="6"
                    v-if="
                        (page.props.auth.user.home_office && (!user.latest_work_log || user.latest_work_log.end)) ||
                        (user.latest_work_log && user.latest_work_log.is_home_office && !user.latest_work_log.end)
                    "
                >
                    <!-- <v-alert
                        color="error"
                        v-if="currentOperatingTime && now.diff(DateTime.fromFormat(currentOperatingTime.end, 'HH:mm:ss')).as('minutes') < 0"
                    >
                        Es fehlt eine Meldung. Bitte Zeitkorrektur.
                        <v-btn color="error" :href="`/user/${page.props.auth.user.id}/workLogs?workLog=${user.latest_work_log?.id}`"> Zeitkorrektur </v-btn>
                    </v-alert> 
                    TODO: determine how to handle working outside of operating hours
                    -->
                    <v-btn block size="large" @click.stop="changeWorkStatus(true)" color="primary" class="me-2">
                        {{ !user.latest_work_log || user.latest_work_log.end ? 'Kommen Homeoffice' : 'Gehen Homeoffice' }}
                    </v-btn>
                </v-col>

                <v-col cols="12" md="6">
                    <v-btn
                        block
                        size="large"
                        @click.stop="changeWorkStatus()"
                        color="primary"
                        v-if="user.latest_work_log?.end || !user.latest_work_log?.is_home_office"
                    >
                        {{ !user.latest_work_log || user.latest_work_log.end ? 'Kommen' : 'Gehen' }}
                    </v-btn>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<style lang="scss" scoped></style>
