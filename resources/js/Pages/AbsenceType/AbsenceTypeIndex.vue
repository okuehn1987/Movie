<script setup lang="ts">
import { AbsenceType } from '@/types/types';
import CreateAbsenceTypeForm from './partial/createAbsenceTypeForm.vue';
import EditAbsenceTypeForm from './partial/editAbsenceTypeForm.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';

defineProps<{
    absenceTypes: AbsenceType[];
    absence_type_defaults: string[];
}>();
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
                        <CreateAbsenceTypeForm :absence_type_defaults @close="isActive.value = false" />
                    </template>
                </v-dialog>
            </template>
            <template v-slot:item.requires_approval="{ item }">
                <v-icon :icon="item.requires_approval ? 'mdi-check' : 'mdi-close'"></v-icon>
            </template>
            <template v-slot:item.action="{ item }">
                <v-dialog max-width="1000" v-if="can('absenceType', 'update')">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn icon variant="text" v-bind="activatorProps" color="primary">
                            <v-icon icon="mdi-pencil"></v-icon>
                        </v-btn>
                    </template>

                    <template v-slot:default="{ isActive }">
                        <EditAbsenceTypeForm :item :absence_type_defaults @close="isActive.value = false" />
                    </template>
                </v-dialog>
                <ConfirmDelete
                    v-if="can('absenceType', 'delete')"
                    :content="`Bist du dir sicher das du den Abwesenheitsgrund '${item.name}' löschen möchtest?`"
                    :route="
                        route('absenceType.destroy', {
                            absenceType: item.id,
                        })
                    "
                    :title="`Abwesenheitsgrund ${item.name} löschen`"
                ></ConfirmDelete>
                <div style="width: 48px" v-else></div>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
