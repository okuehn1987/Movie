<script setup lang="ts">
import { OperatingTime, TimeAccount, WorkLog } from '@/types/types';
import { useNow } from '@/utils';
import { router, usePage } from '@inertiajs/vue3';
import { DateTime, Info } from 'luxon';
import { computed } from 'vue';

const props = defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    operating_times: OperatingTime[];
    defaultTimeAccount: TimeAccount;
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
    <v-card>
        <v-toolbar color="primary">
            <v-toolbar-title>Arbeitszeit</v-toolbar-title>
            <v-btn
                :href="
                    route('user.workLog.index', {
                        user: page.props.auth.user.id,
                    })
                "
            >
                <v-icon icon="mdi-eye"></v-icon>
            </v-btn>
        </v-toolbar>

        <v-card-text>
            <div>Woche Soll: {{ workingHours.should }}</div>
            <div>Woche Ist: {{ Math.floor(currentWorkingHours) }}:{{ Math.floor((currentWorkingHours % 1) * 60) }}</div>
            <div>Momentane Überstunden: {{ defaultTimeAccount.balance }}</div>
            <div v-if="lastWorkLog.end == null">
                <div>
                    Hier seit:
                    {{ DateTime.fromSQL(lastWorkLog.start).toFormat('HH:mm') }} Uhr
                </div>
            </div>
            <v-alert
                color="danger"
                class="mt-2"
                v-if="currentOperatingTime && now.diff(DateTime.fromFormat(currentOperatingTime.end, 'HH:mm')).as('minutes') > 0"
            >
                Es fehlt eine Meldung. Bitte Zeitkorrektur.
                <v-btn color="primary" class="ms-2" :href="`/user/${page.props.auth.user.id}/workLogs?workLog=${lastWorkLog.id}`">
                    Zeitkorrektur
                </v-btn>
            </v-alert>
            <div v-else>
                <template v-if="(page.props.auth.user.home_office && lastWorkLog.end) || (lastWorkLog.is_home_office && !lastWorkLog.end)">
                    <v-btn @click.stop="changeWorkStatus(true)" color="primary" class="me-2">
                        {{ lastWorkLog.end ? 'Kommen Homeoffice' : 'Gehen Homeoffice' }}
                    </v-btn>
                </template>
                <v-btn @click.stop="changeWorkStatus()" color="primary" v-if="lastWorkLog.end || !lastWorkLog.is_home_office">
                    {{ lastWorkLog.end ? 'Kommen' : 'Gehen' }}
                </v-btn>
            </div>
        </v-card-text>
    </v-card>
</template>
<style lang="scss" scoped></style>
