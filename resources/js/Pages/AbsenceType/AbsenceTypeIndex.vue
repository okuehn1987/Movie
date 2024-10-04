<script setup lang="ts">
import { AbsenceType } from '@/types/types';
import { useForm } from '@inertiajs/vue3';

defineProps<{
	absenceTypes: AbsenceType[];
	types?: string[];
}>();

const DEFAULTS = [
	{ name: 'Unbezahlter Urlaub', abbreviation: 'UB' },
	{ name: 'Ausbildung/ Berufsschule', abbreviation: 'BS' },
	{ name: 'Fort- und Weiterbildung', abbreviation: 'FW' },
	{ name: 'AZV-Tag', abbreviation: 'AZ' },
	{ name: 'Bildungsurlaub', abbreviation: 'BU' },
	{ name: 'Sonderurlaub', abbreviation: 'SU' },
	{ name: 'Elternzeit', abbreviation: 'EZ' },
	{ name: 'Urlaub', abbreviation: 'EU' },
	{ name: 'Andere', abbreviation: 'AN' },
];

const absenceTypeForm = useForm({
	name: '',
	abbreviation: '',
	type: '',
	requires_approval: false,
});

function submit() {
	absenceTypeForm.post(route('absenceType.store'), {
		onSuccess: () => absenceTypeForm.reset(),
	});
}
</script>
<template>
	<v-data-table-virtual
		hover
		:headers="[
			{ title: '#', key: 'id' },
			{ title: 'Abwesenheitsgrund', key: 'name' },
			{ title: 'Abkürzung', key: 'abbreviation' },
			{ title: 'Muss genehmigt werden', key: 'requires_approval' },
			{ title: '', key: 'action', align: 'end' },
		]"
		:items="absenceTypes"
	>
		<template v-slot:header.action>
			<v-dialog max-width="1000">
				<template v-slot:activator="{ props: activatorProps }">
					<v-btn v-bind="activatorProps" color="primary">
						<v-icon icon="mdi-plus"></v-icon>
					</v-btn>
				</template>

				<template v-slot:default="{ isActive }">
					<v-card title="Abwesenheitgrund erstellen">
						<v-form @submit.prevent="submit">
							<v-row>
								<v-col cols="12" md="6">
									<v-text-field
										class="px-8"
										label="Abwesenheitsgrund"
										required
										:error-messages="absenceTypeForm.errors.name"
										v-model="absenceTypeForm.name"
										variant="underlined"
									></v-text-field>
								</v-col>
								<v-col cols="12" md="6">
									<v-text-field
										class="px-8"
										label="Abkürzung"
										required
										:error-messages="absenceTypeForm.errors.abbreviation"
										v-model="absenceTypeForm.abbreviation"
										variant="underlined"
									></v-text-field>
								</v-col>
								<v-col cols="12" md="6">
									<v-select
										class="px-8"
										label="Typ"
										:items="DEFAULTS"
										item-value="name"
										item-title="name"
										required
										:error-messages="absenceTypeForm.errors.type"
										v-model="absenceTypeForm.type"
										variant="underlined"
									></v-select>
								</v-col>
								<v-col cols="12" md="6">
									<v-checkbox
										class="px-8"
										label="Muss genehmigt werden?"
										required
										:error-messages="absenceTypeForm.errors.requires_approval"
										v-model="absenceTypeForm.requires_approval"
										variant="underlined"
									></v-checkbox>
								</v-col>
							</v-row>

							<v-card-actions>
								<div class="d-flex justify-end w-100">
									<v-btn color="error" class="me-2" variant="elevated" @click="isActive.value = false"> Abbrechen </v-btn>
									<v-btn type="submit" color="primary" variant="elevated">Erstellen </v-btn>
								</div>
							</v-card-actions>
						</v-form>
					</v-card>
				</template>
			</v-dialog>
		</template>
	</v-data-table-virtual>
</template>
