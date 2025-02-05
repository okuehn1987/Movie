<script setup lang="ts">
import { Absence, AbsenceType, Canable, User, UserWorkingWeek, Weekday } from '@/types/types';
import { DateTime } from 'luxon';
import { computed } from 'vue';

type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'> &
    Canable & {
        user_working_weeks: Pick<UserWorkingWeek, 'id' | Weekday>[];
    };
const props = defineProps<{
    user: UserProp;
    date: DateTime;
    absences: (Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'absence_type_id' | 'user_id'> & {
        absence_type?: Pick<AbsenceType, 'id' | 'abbreviation'>;
    })[];
    absenceTypes: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
    holidays: Record<string, string> | null;
}>();

const absence = computed(() => props.absences.find(a => DateTime.fromSQL(a.start) <= props.date && props.date <= DateTime.fromSQL(a.end)));

function shouldUserWork(user: UserProp, day: DateTime) {
    return (
        user.user_working_weeks.find(e => e[day.setLocale('en-US').weekdayLong?.toLowerCase() as Weekday]) &&
        !props.holidays?.[props.date.toFormat('yyyy-MM-dd')]
    );
}
</script>
<template>
    <td
        class="pa-0"
        :style="{ backgroundColor: shouldUserWork(user, date) ? '' : 'lightgray' }"
        :class="{ 'editable-cell': can('absence', 'create', props.user) }"
        :role="can('absence', 'create', props.user) ? 'button' : 'cell'"
        :title="props.holidays?.[props.date.toFormat('yyyy-MM-dd')]"
    >
        <template v-if="absence && shouldUserWork(user, date)">
            <span v-if="absence.absence_type_id">{{ absence.absence_type?.abbreviation }}</span>
            <div v-else class="h-100 w-100" style="background-color: #f99"></div>
        </template>
    </td>
</template>

<style>
.editable-cell:not(:first-child):hover {
    background-color: darkgray !important;
}
</style>
