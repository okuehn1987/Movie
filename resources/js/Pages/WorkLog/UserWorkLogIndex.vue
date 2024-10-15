<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Paginator, User, WorkLog, WorkLogPatch } from '@/types/types';
import { useParams } from '@/utils';
import { router, useForm } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, watch } from 'vue';

type PatchProp = Omit<WorkLogPatch, 'deleted_at' | 'created_at' | 'user_id'>;

const props = defineProps<{
	user: User;
	workLogs: Paginator<
		(WorkLog & {
			work_log_patches: PatchProp[];
		})[]
	>;
}>();

const params = useParams();
const workLogId = params['workLog'];

onMounted(() => {
	if (workLogId) editWorkLog(Number(workLogId));
});

const currentPage = ref(props.workLogs.current_page);

const showDialog = ref(false);

const patchMode = ref<'edit' | 'fix' | 'show' | null>(null);
const patchLog = ref<WorkLog | null>(null);
const inputVariant = computed(() => (patchLog.value ? 'plain' : 'underlined'));

const editableWorkLogs = computed(() => props.workLogs.data.filter((_, i) => i < 5));

watch(currentPage, () => {
	router.visit(
		route('user.workLog.index', {
			user: props.user.id,
			page: currentPage.value,
		}),
		{
			only: ['paginator'],
		}
	);
});

const workLogForm = useForm({
	id: -1,
	start: new Date(),
	end: new Date(),
	start_time: '',
	end_time: '',
	is_home_office: false,
});

function submit() {
	workLogForm
		.transform(data => {
			const start_time = DateTime.fromFormat(data.start_time, 'HH:mm');
			const end_time = DateTime.fromFormat(data.end_time, 'HH:mm');
			return {
				...data,
				start: DateTime.fromISO(data.start.toISOString()).set({
					hour: start_time.hour,
					minute: start_time.minute,
				}),
				end: DateTime.fromISO(data.end.toISOString()).set({
					hour: end_time.hour,
					minute: end_time.minute,
				}),
				workLog: workLogForm.id,
			};
		})
		.post(route('workLogPatch.store'), {
			onSuccess: () => {
				workLogForm.reset();
				showDialog.value = false;
			},
		});
}

function editWorkLog(id: WorkLog['id']) {
	const workLog = props.workLogs.data.find(e => e.id === id);
	if (!workLog) return;
	const lastPatch = workLog.work_log_patches.at(-1);
	if (!workLog.end) {
		patchMode.value = 'fix';
		patchLog.value = workLog;
	} else if (!lastPatch) {
		patchLog.value = null;
		patchMode.value = 'edit';
	} else if (lastPatch && lastPatch.status === 'created') {
		patchLog.value = lastPatch;
		patchMode.value = 'show';
	}

	workLogForm.id = id;
	workLogForm.start = new Date(workLog.start);
	workLogForm.end = workLog.end ? new Date(workLog.end) : new Date();
	workLogForm.start_time = DateTime.fromSQL(workLog.start).toFormat('HH:mm');
	workLogForm.end_time = DateTime.fromSQL(workLog.end || DateTime.now().toSQL()).toFormat('HH:mm');
	workLogForm.is_home_office = workLog.is_home_office;
	showDialog.value = true;
}

function retreatPatch() {
	const workLog = props.workLogs.data.find(e => e.id === workLogForm.id);
	if (!workLog) return;

	const lastPatch = workLog.work_log_patches.at(-1);
	if (!lastPatch) return;

	router.delete(
		route('workLogPatch.destroy', {
			workLogPatch: lastPatch.id,
		}),
		{
			onSuccess: () => {
				showDialog.value = false;
			},
		}
	);
}
</script>
<template>
	<AdminLayout :title="user.first_name + ' ' + user.last_name">
		<v-container>
			<v-alert class="mb-4" color="primary" v-if="workLogForm.wasSuccessful" closable>
				Korrigierung der Arbeitszeit erfolgreich beantragt.
			</v-alert>
			<v-data-table
				v-model:page="workLogs.current_page"
				:headers="[
					{ title: 'Start', key: 'start' },
					{ title: 'Ende', key: 'end' },
					{ title: 'Homeoffice', key: 'is_home_office' },
					{ title: 'Korrektur', key: 'status' },
					{
						title: '',
						key: 'action',
						width: '96px',
						sortable: false,
					},
				]"
				:items-per-page="workLogs.per_page"
				:items="
					workLogs.data
						.map(workLog => {
							const lastAcceptedPatch = workLog.work_log_patches
								.filter(e => e.status === 'accepted')
								.toSorted((a, b) => (a.updated_at < b.updated_at ? 1 : -1))[0];
							return {
								...(lastAcceptedPatch ?? workLog),
								id: workLog.id,
							};
						})
						.toSorted((a, b) => (a.start < b.start ? 1 : -1))
						.map(workLog => ({
							start: DateTime.fromSQL(workLog.start).toFormat('dd.MM.yyyy HH:mm'),
							end: workLog.end ? DateTime.fromSQL(workLog.end).toFormat('dd.MM.yyyy HH:mm') : 'Noch nicht beendet',
							is_home_office: workLog.is_home_office ? 'Ja' : 'Nein',
							id: workLog.id,
							status: {
								created: 'Beantragt',
								declined: 'Abgelehnt',
								accepted: 'Akzeptiert',
								none: 'Nicht vorhanden',
							}[workLogs.data.find(e => e.id === workLog.id)?.work_log_patches.at(-1)?.status || 'none'],
						}))
				"
			>
				<template v-slot:item.action="{ item }">
					<v-btn color="primary" v-if="editableWorkLogs.find(e => e.id === item.id)" @click.stop="editWorkLog(item.id)">
						<v-icon
							:icon="
								workLogs.data.find(log => log.id === item.id)?.work_log_patches.at(-1)?.status === 'created'
									? 'mdi-eye'
									: 'mdi-pencil'
							"
						></v-icon>
					</v-btn>
				</template>
				<template v-slot:bottom>
					<v-pagination v-if="workLogs.last_page > 1" v-model="currentPage" :length="workLogs.last_page"> </v-pagination>
				</template>
			</v-data-table>

			<v-dialog max-width="1000" v-model="showDialog">
				<v-card title="Zeitkorrektur">
					<v-form @submit.prevent="submit">
						<v-row no-gutters>
							<v-col cols="12" md="3">
								<v-date-input
									:disabled="patchMode == 'fix'"
									class="px-8"
									label="Start"
									required
									:error-messages="workLogForm.errors.start"
									v-model="workLogForm.start"
									:variant="inputVariant"
									style="height: 73px"
								></v-date-input
							></v-col>
							<v-col cols="12" md="3">
								<v-text-field
									:disabled="patchMode == 'fix'"
									type="time"
									class="px-8"
									label="Start"
									required
									:error-messages="workLogForm.errors.start_time"
									v-model="workLogForm.start_time"
									:variant="inputVariant"
								></v-text-field>
							</v-col>
							<v-col cols="12" md="3">
								<v-date-input
									:disabled="patchMode != 'fix'"
									class="px-8"
									label="Ende"
									required
									:error-messages="workLogForm.errors.end"
									v-model="workLogForm.end"
									:variant="inputVariant"
									style="height: 73px"
								></v-date-input>
							</v-col>
							<v-col cols="12" md="3">
								<v-text-field
									:disabled="!!patchLog"
									type="time"
									class="px-8"
									label="Ende"
									required
									:error-messages="workLogForm.errors.end_time"
									v-model="workLogForm.end_time"
									:variant="inputVariant"
								></v-text-field>
							</v-col>

							<v-col cols="12" md="3">
								<v-checkbox
									:disabled="!!patchLog"
									class="px-8 ms-n2"
									label="Homeoffice"
									required
									:error-messages="workLogForm.errors.is_home_office"
									v-model="workLogForm.is_home_office"
									:variant="inputVariant"
								></v-checkbox>
							</v-col>
						</v-row>

						<v-card-actions>
							<div class="d-flex justify-end w-100">
								<v-btn color="error" class="me-2" variant="elevated" @click="showDialog = false"> Abbrechen </v-btn>
								<v-btn v-if="patchLog" @click.stop="retreatPatch" color="primary" variant="elevated"> Antrag zurückziehen </v-btn>
								<v-btn v-else type="submit" color="primary" variant="elevated"> Korrektur beantragen </v-btn>
							</div>
						</v-card-actions>
					</v-form>
				</v-card>
			</v-dialog>
		</v-container>
	</AdminLayout>
</template>
