<script setup lang="ts">
import { AbsenceType, Weekday } from '@/types/types';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { AbsencePatchProp, AbsenceProp, getEntryState, HomeOfficeDayProp, UserProp } from '../utils';

const props = defineProps<{
    user: UserProp;
    date: DateTime;
    entries: (AbsenceProp | AbsencePatchProp)[];
    absenceTypes: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
    holidays: Record<string, string> | null;
    homeOfficeDays: HomeOfficeDayProp[];
}>();

const currentEntry = computed(() => props.entries.find(a => DateTime.fromSQL(a.start) <= props.date && props.date <= DateTime.fromSQL(a.end)));

function shouldUserWork(day: DateTime) {
    const currentWorkingWeek = props.user.user_working_weeks
        .toSorted((a, b) => b.active_since.localeCompare(a.active_since))
        .find(w => w.active_since <= day.toFormat('yyyy-MM-dd'));
    return (
        currentWorkingWeek &&
        currentWorkingWeek[day.setLocale('en-US').weekdayLong?.toLowerCase() as Weekday] &&
        !props.holidays?.[props.date.toFormat('yyyy-MM-dd')]
    );
}

function getColor() {
    if (currentEntry.value && shouldUserWork(props.date)) {
        return { accepted: '#f99', created: '#99f', declined: 'grey', hasOpenPatch: '#99f' }[getEntryState(currentEntry.value)];
    }
    if (shouldUserWork(props.date) && !!currentHomeOfficeEntry.value) {
        return { accepted: '#ff9', created: '#99f', declined: 'grey' }[currentHomeOfficeEntry.value.status];
    }
    if (!shouldUserWork(props.date)) return 'lightgray';
    return '';
}

const currentHomeOfficeEntry = computed(() => props.homeOfficeDays.find(d => d.date === props.date.toFormat('yyyy-MM-dd')));
</script>
<template>
    <td
        class="pa-0"
        :class="{ 'editable-cell': can('absence', 'create', props.user) }"
        :role="can('absence', 'create', props.user) ? 'button' : 'cell'"
        :title="props.holidays?.[props.date.toFormat('yyyy-MM-dd')] ?? absenceTypes.find(t => t.id == currentEntry?.absence_type?.id)?.name"
    >
        <div class="w-100 h-100 d-flex justify-center align-center flex-column" :style="{ backgroundColor: getColor() }">
            <div v-if="currentEntry && shouldUserWork(date) && currentEntry.absence_type_id" class="h-100 w-100 d-flex justify-center align-center">
                <span>{{ currentEntry.absence_type?.abbreviation }}</span>
            </div>
            <div
                v-if="user.date_of_birth_marker && date.toFormat('MM-dd') === DateTime.fromISO(user.date_of_birth_marker).toFormat('MM-dd')"
                title="Geburtstag"
            >
                <v-icon>mdi-crown</v-icon>
            </div>
        </div>
    </td>
</template>

<style>
.editable-cell:not(:first-child):hover > div {
    filter: brightness(0.8);
}
.editable-cell:not(:first-child):hover > div.empty {
    background-color: #ccc;
}
</style>
