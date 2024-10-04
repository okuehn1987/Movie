<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, User, WorkLog } from '@/types/types';
import { DateTime } from 'luxon';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

type CalendarEvent = {
	start: Date;
	end: Date;
	title: string;
	id: number;
};

type UserProp = User & { work_logs: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id'>[] };

const props = defineProps<{
	users: UserProp[];
	date: string;
	groups: (Pick<Group, 'id' | 'name'> & { users: { id: User['id']; group_id: Group['id'] } })[];
}>();

const selectedGroups = ref<Group['id'][]>([]);

const expanded = ref<Set<number>>(new Set([]));

const calendarMonth = ref([new Date(props.date)]);
watch(
	calendarMonth,
	() =>
		router.get(route('workLog.index'), {
			year: calendarMonth.value[0].getFullYear(),
			month: (calendarMonth.value[0].getMonth() + 1).toString().padStart(2, '0'),
		}),
	{ deep: true }
);

const events = computed(() => {
	const userDates = props.users
		.filter(user => selectedGroups.value.length == 0 || selectedGroups.value.includes(user.group_id))
		.flatMap(user => {
			const dates: string[] = [];
			for (const log of user.work_logs) {
				const day = DateTime.fromSQL(log.start).startOf('day').toISO();
				if (day && !dates.includes(day)) dates.push(day);
			}
			return dates.flatMap(date => ({
				start: new Date(date),
				end: new Date(date),
				title: user.first_name + ' ' + user.last_name,
				id: user.id,
			}));
		});
	const days = DateTime.fromJSDate(calendarMonth.value[0]).daysInMonth ?? DateTime.now().daysInMonth;

	const filteredDates = [];
	for (let i = 1; i <= days; i++) {
		const datesInDay = userDates.filter(date => DateTime.fromJSDate(date.start).day === i);
		if (datesInDay.length > 5) {
			filteredDates.push({ start: datesInDay[0].start, end: datesInDay[0].end, title: '', id: -1 });
		}
		let count = 0;
		for (const date of datesInDay) {
			if (count < 5 || expanded.value.has(i)) {
				filteredDates.push(date);
				count++;
			} else {
				break;
			}
		}
	}
	return filteredDates satisfies CalendarEvent[];
});

function expandDay(event: CalendarEvent) {
	const day = DateTime.fromJSDate(event.start).day;
	if (expanded.value.has(day)) {
		expanded.value.delete(day);
	} else expanded.value.add(day);
}

const showEventDialog = ref(false);
const dialogEvent = ref<(CalendarEvent & UserProp) | null>(null);
function openEvent(event: CalendarEvent) {
	const user = props.users.find(u => u.id === event.id);
	if (!user) return;

	dialogEvent.value = {
		...event,
		...user,
		work_logs: user.work_logs.filter(workLog => DateTime.fromSQL(workLog.start).day === DateTime.fromJSDate(event.start).day),
	};
	showEventDialog.value = true;
}
// TODO: filter z.b. nach abteilungen, fehltage, arbeit
// TODO: absences anzeigen
</script>
<template>
	<AdminLayout title="Arbeitszeiten">
		<v-container>
			<v-calendar :events v-model="calendarMonth">
				<template v-slot:header>
					<v-toolbar color="primary" :title="calendarMonth[0].toLocaleDateString('de-DE', { year: 'numeric', month: 'long' })">
						<template v-slot:prepend>
							<v-btn @click="() => (calendarMonth[0] = DateTime.fromJSDate(calendarMonth[0]).minus({ month: 1 }).toJSDate())">
								<v-icon size="large" icon="mdi-chevron-left"></v-icon>
							</v-btn>
							<v-btn @click="() => (calendarMonth[0] = DateTime.fromJSDate(calendarMonth[0]).plus({ month: 1 }).toJSDate())">
								<v-icon size="large" icon="mdi-chevron-right"></v-icon>
							</v-btn>
						</template>
						<v-dialog max-width="1000">
							<template v-slot:activator="{ props }">
								<v-btn v-bind="props"><v-icon icon="mdi-filter"></v-icon> </v-btn>
							</template>
							<v-card>
								<v-toolbar color="primary" title="Filteroptionen"> </v-toolbar>
								<v-card-text
									><v-select
										v-model="selectedGroups"
										multiple
										variant="underlined"
										:items="groups"
										item-value="id"
										item-title="name"
									></v-select
								></v-card-text>
							</v-card>
						</v-dialog>
					</v-toolbar>
				</template>
				<template v-slot:event="{ event }">
					<v-chip
						v-if="event['id'] == -1"
						class="w-100 mb-2"
						:prepend-icon="expanded.has(DateTime.fromJSDate(event['start'] as Date).day) ? 'mdi-chevron-up' : 'mdi-chevron-down'"
						@click.stop="expandDay(event as CalendarEvent)"
					>
						{{ expanded.has(DateTime.fromJSDate(event['start'] as Date).day) ? 'Weniger Anzeigen' : 'Alle Anzeigen' }}
					</v-chip>
					<v-chip v-else class="mb-1 me-1" prepend-icon="mdi-circle-medium" @click.stop="openEvent(event as CalendarEvent)">
						{{ event['title'] }}
					</v-chip>
				</template>
			</v-calendar>

			<v-dialog v-if="dialogEvent" max-width="1000" v-model="showEventDialog">
				<v-toolbar color="primary" :title="dialogEvent.title + ' ' + dialogEvent.start.toLocaleDateString()"></v-toolbar>
				<v-card>
					<v-card-text>
						<v-data-table-virtual
							:headers="[
								{ title: 'Start', key: 'start', sortable: false },
								{ title: 'Ende', key: 'end', sortable: false },
								{ title: 'Homeoffice', key: 'is_home_office', sortable: false },
							]"
							:items="
								dialogEvent.work_logs.map(w => ({
									start: DateTime.fromSQL(w.start).toFormat('dd.MM.yyyy HH:mm'),
									end: w.end ? DateTime.fromSQL(w.end).toFormat('dd.MM.yyyy HH:mm') : 'Noch nicht beendet',
									is_home_office: w.is_home_office ? 'Ja' : 'Nein',
								}))
							"
						></v-data-table-virtual>
					</v-card-text>
				</v-card>
			</v-dialog>
		</v-container>
	</AdminLayout>
</template>
