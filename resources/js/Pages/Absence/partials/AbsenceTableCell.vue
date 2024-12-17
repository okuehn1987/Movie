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
    absences: Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'absence_type_id' | 'user_id'>[];
    absenceTypes: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
    holidays: Record<string, string> | null;
}>();

const absence = computed(() => props.absences.find(a => DateTime.fromSQL(a.start) <= props.date && props.date <= DateTime.fromSQL(a.end)));

function shouldUserWork(user: UserProp, day: DateTime) {
    return user.user_working_weeks.find(e => e[day.setLocale('en-US').weekdayLong?.toLowerCase() as Weekday]) && !props.holidays?.[props.date.day];
}
</script>
<template>
    <td
        :style="{ backgroundColor: shouldUserWork(user, date) ? '' : 'lightgray' }"
        :class="{ 'editable-cell': can('absence', 'create', props.user) }"
        :role="can('absence', 'create', props.user) ? 'button' : 'cell'"
        :title="props.holidays?.[props.date.day]"
    >
        {{ absence && shouldUserWork(user, date) ? absenceTypes.find(a => a.id === absence?.absence_type_id)?.abbreviation ?? '❌' : '' }}
    </td>
</template>

<style>
.editable-cell:not(:first-child):hover {
    background-color: darkgray !important;
}
</style>
