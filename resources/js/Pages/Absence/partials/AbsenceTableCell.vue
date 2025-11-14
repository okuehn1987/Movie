<script setup lang="ts">
import { AbsenceType, HomeOfficeDay, Weekday } from '@/types/types';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { AbsencePatchProp, AbsenceProp, getEntryState, UserProp } from '../utils';

const props = defineProps<{
    user: UserProp;
    date: DateTime;
    entries: (AbsenceProp | AbsencePatchProp)[];
    absenceTypes: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
    holidays: Record<string, string> | null;
    homeOfficeDays: Pick<HomeOfficeDay, 'id' | 'user_id' | 'date' | 'status'>[];
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

function hasHomeOfficeDay() {
    return !!props.homeOfficeDays.find(d => d.date === props.date.toFormat('yyyy-MM-dd'));
}
</script>
<template>
    <td
        class="pa-0"
        :class="{ 'editable-cell': can('absence', 'create', props.user) }"
        :role="can('absence', 'create', props.user) ? 'button' : 'cell'"
        :title="props.holidays?.[props.date.toFormat('yyyy-MM-dd')] ?? absenceTypes.find(t => t.id == currentEntry?.absence_type?.id)?.name"
    >
        <template v-if="currentEntry && shouldUserWork(date)">
            <div
                class="h-100 w-100 d-flex justify-center align-center"
                :style="{
                    backgroundColor: { accepted: '#f99', created: '#99f', declined: 'grey', hasOpenPatch: '#ff9' }[getEntryState(currentEntry)],
                }"
            >
                <span v-if="currentEntry.absence_type_id">{{ currentEntry.absence_type?.abbreviation }}</span>
            </div>
        </template>
        <div
            v-else
            :style="{ backgroundColor: shouldUserWork(date) ? (hasHomeOfficeDay() ? '#a7e8f1' : '') : 'lightgray' }"
            class="h-100 w-100 empty"
        ></div>
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
