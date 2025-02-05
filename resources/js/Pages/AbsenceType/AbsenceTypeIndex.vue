<script setup lang="ts">
import { AbsenceType } from '@/types/types';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';

defineProps<{
    absenceTypes: AbsenceType[];
    types?: string[];
    absence_type_defaults: string[];
}>();
const absenceTypeForm = useForm({
    name: '',
    abbreviation: '',
    type: '',
    requires_approval: false,
});
</script>
<template>
    <v-card>
        <v-data-table-virtual
            hover
            :headers="[
                { title: 'Abwesenheitsgrund', key: 'name' },
                { title: 'Abkürzung', key: 'abbreviation' },
                { title: 'Muss genehmigt werden', key: 'requires_approval' },
                { title: '', key: 'action', align: 'end' },
            ]"
            :items="absenceTypes"
        >
            <template v-slot:header.action>
                <v-dialog max-width="1000" v-if="can('absenceType', 'create')">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn data-testid="absenceCreation" v-bind="activatorProps" color="primary">
                            <v-icon icon="mdi-plus"></v-icon>
                        </v-btn>
                    </template>

                    <template v-slot:default="{ isActive }">
                        <v-card title="Abwesenheitgrund erstellen">
                            <template #append>
                                <v-btn icon variant="text" @click="isActive.value = false">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </template>
                            <v-card-text>
                                <v-form
                                    @submit.prevent="
                                        absenceTypeForm.post(route('absenceType.store'), {
                                            onSuccess: () => {
                                                absenceTypeForm.reset();
                                                isActive.value = false;
                                            },
                                        })
                                    "
                                >
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Abwesenheitsgrund"
                                                required
                                                :error-messages="absenceTypeForm.errors.name"
                                                v-model="absenceTypeForm.name"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Abkürzung"
                                                required
                                                :error-messages="absenceTypeForm.errors.abbreviation"
                                                v-model="absenceTypeForm.abbreviation"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-select
                                                data-testid="typeOfAbsence"
                                                label="Typ"
                                                :items="absence_type_defaults"
                                                required
                                                :error-messages="absenceTypeForm.errors.type"
                                                v-model="absenceTypeForm.type"
                                            ></v-select>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-checkbox
                                                label="Muss genehmigt werden?"
                                                required
                                                :error-messages="absenceTypeForm.errors.requires_approval"
                                                v-model="absenceTypeForm.requires_approval"
                                            ></v-checkbox>
                                        </v-col>
                                        <v-col cols="12" class="text-end">
                                            <v-btn :loading="absenceTypeForm.processing" type="submit" color="primary">Erstellen</v-btn>
                                        </v-col>
                                    </v-row>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </template>
                </v-dialog>
                <ConfirmDelete
                    v-if="can('absenceType', 'delete')"
                    :content="`Bist du dir sicher das du den Abwesenheitsgrund '${item.name}' entfernen möchtest?`"
                    :route="
                        route('absenceType.destroy', {
                            absenceType: item.id,
                        })
                    "
                    title="Abwesenheitsgrund löschen"
                ></ConfirmDelete>
                <div style="width: 48px" v-else></div>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
