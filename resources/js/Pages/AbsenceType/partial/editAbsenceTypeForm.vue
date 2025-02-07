<script setup lang="ts">
import { AbsenceType } from '@/types/types';

const props = defineProps<{
    absence_type_defaults: string[];
    item: AbsenceType;
}>();
const emit = defineEmits<{
    close: [];
}>();
const absenceTypeForm = useForm({
    name: props.item.name,
    abbreviation: props.item.abbreviation,
    type: props.item.type,
    requires_approval: props.item.requires_approval,
});
</script>
<template>
    <v-card :title="`Abwesenheitgrund ${item.name} bearbeiten`">
        <template #append>
            <v-btn icon variant="text" @click="emit('close')">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </template>
        <v-card-text>
            <v-form
                @submit.prevent="
                    absenceTypeForm.patch(route('absenceType.update', { absenceType: item.id }), {
                        onSuccess: () => emit('close'),
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
                        <v-btn :loading="absenceTypeForm.processing" type="submit" color="primary">Speichern</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
<style lang="scss" scoped></style>
