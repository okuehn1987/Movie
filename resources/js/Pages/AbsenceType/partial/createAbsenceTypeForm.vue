<script setup lang="ts">
defineProps<{
    absence_type_defaults: string[];
}>();
const emit = defineEmits<{
    close: [];
}>();
const absenceTypeForm = useForm({
    name: '',
    abbreviation: '',
    type: '',
    requires_approval: false,
});
</script>
<template>
    <v-card title="Abwesenheitgrund erstellen">
        <template #append>
            <v-btn
                icon
                variant="text"
                @click="
                    emit('close');
                    absenceTypeForm.reset();
                "
            >
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </template>
        <v-divider></v-divider>
        <v-card-text>
            <v-form
                @submit.prevent="
                    absenceTypeForm.post(route('absenceType.store'), {
                        onSuccess: () => {
                            absenceTypeForm.reset();
                            emit('close');
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
<style lang="scss" scoped></style>
