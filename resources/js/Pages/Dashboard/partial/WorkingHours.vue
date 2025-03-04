<script setup lang="ts">
import { OperatingTime, WorkLog } from '@/types/types';
import { formatDuration, useNow } from '@/utils';
import { router, usePage } from '@inertiajs/vue3';
import { DateTime, Duration } from 'luxon';
import { computed } from 'vue';

const props = defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'> | null;
    operating_times: OperatingTime[];
    overtime: number;
    workingHours: { should: number; current: number; currentHomeOffice: number };
}>();

const page = usePage();
const now = useNow();

function changeWorkStatus(is_home_office = false) {
    router.post(route('workLog.store'), {
        is_home_office,
    });
}

const currentWorkingHours = computed(() => {
    if (!props.lastWorkLog) return 0;
    return props.lastWorkLog.end
        ? props.workingHours.current
        : now.value.diff(DateTime.fromSQL(props.lastWorkLog?.start || '')).as('hours') + props.workingHours.current;
});

const lastActionText = computed(() => {
    if (!props.lastWorkLog) return;
    const endTime = DateTime.fromSQL(props.lastWorkLog.end ?? props.lastWorkLog.start);
    const diff = now.value.diff(endTime);
    if (diff.as('minutes') < 1) {
        return 'Jetzt';
    }
    if (diff.as('hours') < 1) {
        return 'vor ' + Math.floor(diff.as('minutes')) + ' minuten';
    }
    if (endTime.day !== now.value.day) {
        return endTime.toFormat('dd.MM - HH:mm') + ' Uhr';
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
                            Woche gesamt
                            <div class="text-h6">
                                {{ Duration.fromObject({ hours: currentWorkingHours }).toFormat('h:mm') }}
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" v-if="$page.props.auth.user.home_office || workingHours.currentHomeOffice">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="green" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-clock-check" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Woche Homeoffice
                            <div class="text-h6">
                                {{ Duration.fromObject({ hours: workingHours.currentHomeOffice }).toFormat('h:mm') }}
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
                <v-col cols="12" sm="6" v-if="lastWorkLog">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="blue" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-timer" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            {{ lastWorkLog.end ? 'Gehen' : 'Kommen' }}
                            <div class="text-h6">{{ lastActionText }}</div>
                        </div>
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col
                    cols="12"
                    md="6"
                    v-if="
                        (page.props.auth.user.home_office && (!lastWorkLog || lastWorkLog.end)) ||
                        (lastWorkLog && lastWorkLog.is_home_office && !lastWorkLog.end)
                    "
                >
                    <!-- <v-alert
                        color="error"
                        v-if="currentOperatingTime && now.diff(DateTime.fromFormat(currentOperatingTime.end, 'HH:mm:ss')).as('minutes') < 0"
                    >
                        Es fehlt eine Meldung. Bitte Zeitkorrektur.
                        <v-btn color="error" :href="`/user/${page.props.auth.user.id}/workLogs?workLog=${lastWorkLog?.id}`"> Zeitkorrektur </v-btn>
                    </v-alert> 
                    TODO: determine how to handle working outside of operating hours
                    -->
                    <v-btn block size="large" @click.stop="changeWorkStatus(true)" color="primary" class="me-2">
                        {{ !lastWorkLog || lastWorkLog.end ? 'Kommen Homeoffice' : 'Gehen Homeoffice' }}
                    </v-btn>
                </v-col>

                <v-col cols="12" md="6">
                    <v-btn
                        block
                        size="large"
                        @click.stop="changeWorkStatus()"
                        color="primary"
                        v-if="lastWorkLog?.end || !lastWorkLog?.is_home_office"
                    >
                        {{ !lastWorkLog || lastWorkLog.end ? 'Kommen' : 'Gehen' }}
                    </v-btn>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<style lang="scss" scoped></style>
