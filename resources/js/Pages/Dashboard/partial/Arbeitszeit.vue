<script setup lang="ts">
import { OperatingTime, WorkLog } from '@/types/types';
import { useNow } from '@/utils';
import { router, usePage } from '@inertiajs/vue3';
import { DateTime, Info } from 'luxon';
import { computed } from 'vue';

const props = defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    operating_times: OperatingTime[];
    overtime: number;
    workingHours: { should: number; current: number };
}>();

const page = usePage();
const now = useNow();

function changeWorkStatus(is_home_office = false) {
    router.post(route('workLog.store'), {
        is_home_office,
        id: props.lastWorkLog.end ? null : props.lastWorkLog.id,
    });
}

const currentOperatingTime = props.operating_times.find(
    t => t.type == Info.weekdays('long', { locale: 'en' })[DateTime.now().weekday - 1].toLowerCase(),
);

const currentWorkingHours = computed(() =>
    props.lastWorkLog.end
        ? props.workingHours.current
        : now.value.diff(DateTime.fromSQL(props.lastWorkLog.start)).as('hours') + props.workingHours.current,
);
</script>
<template>
    <v-card title="Arbeitszeit">
        <template #append>
            <v-btn
                icon="mdi-eye"
                variant="text"
                @click="
                    router.get(
                        route('user.workLog.index', {
                            user: page.props.auth.user.id,
                        }),
                    )
                "
            />
        </template>
        <v-card-text>
            <v-row>
                <v-col cols="12" sm="6">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="blue-grey" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-clock" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Woche Soll
                            <div class="text-h6">
                                {{ workingHours.should }}
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="green" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-clock-check" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Woche Ist
                            <div class="text-h6">{{ Math.floor(currentWorkingHours) }}:{{ Math.floor((currentWorkingHours % 1) * 60) }}</div>
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
                                {{ overtime }}
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" v-if="lastWorkLog.end == null">
                    <div class="d-flex align-center ga-3">
                        <v-avatar color="blue" rounded size="40" class="elevation-2">
                            <v-icon size="24" icon="mdi-timer" />
                        </v-avatar>

                        <div class="d-flex flex-column">
                            Hier seit
                            <div class="text-h6">{{ DateTime.fromSQL(lastWorkLog.start).toFormat('HH:mm') }} Uhr</div>
                        </div>
                    </div>
                </v-col>

                <v-col cols="12">
                    <v-alert
                        color="error"
                        v-if="currentOperatingTime && now.diff(DateTime.fromFormat(currentOperatingTime.end, 'HH:mm')).as('minutes') > 0"
                    >
                        Es fehlt eine Meldung. Bitte Zeitkorrektur.
                        <v-btn color="error" :href="`/user/${page.props.auth.user.id}/workLogs?workLog=${lastWorkLog.id}`"> Zeitkorrektur </v-btn>
                    </v-alert>
                    <div v-else-if="(page.props.auth.user.home_office && lastWorkLog.end) || (lastWorkLog.is_home_office && !lastWorkLog.end)">
                        <v-btn block size="large" @click.stop="changeWorkStatus(true)" color="primary" class="me-2">
                            {{ lastWorkLog.end ? 'Kommen Homeoffice' : 'Gehen Homeoffice' }}
                        </v-btn>
                    </div>
                </v-col>

                <v-col cols="12">
                    <v-btn block size="large" @click.stop="changeWorkStatus()" color="primary" v-if="lastWorkLog.end || !lastWorkLog.is_home_office">
                        {{ lastWorkLog.end ? 'Kommen' : 'Gehen' }}
                    </v-btn>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<style lang="scss" scoped></style>
