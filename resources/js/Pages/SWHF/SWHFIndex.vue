<script setup lang="ts">
import { SpecialWorkingHoursFactor, Weekday } from '@/types/types';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    special_working_hours_factors: SpecialWorkingHoursFactor[];
}>();

const WEEKDAYS = [
    { key: 'monday', value: 'Montag' },
    { key: 'tuesday', value: 'Dienstag' },
    { key: 'wednesday', value: 'Mittwoch' },
    { key: 'thursday', value: 'Donnerstag' },
    { key: 'friday', value: 'Freitag' },
    { key: 'saturday', value: 'Samstag' },
    { key: 'sunday', value: 'Sonntag' },
]; //FIXME: fehlende Feiertage oder nachtarbeit , etc. adden

const showDialog = ref(false);

const swhfForm = useForm({
    id: null as number | null,
    type: '' as Weekday | 'holiday' | 'nightshift',
    extra_charge: 0,
});

function edit(item: SpecialWorkingHoursFactor) {
    swhfForm.id = item.id;
    swhfForm.type = item.type;
    swhfForm.extra_charge = item.extra_charge;

    showDialog.value = true;
}

function create() {
    if (swhfForm.id != null) swhfForm.reset();

    showDialog.value = true;
}

function submit() {
    swhfForm.post(route('specialWorkingHoursFactor.store'), {
        onSuccess: () => {
            swhfForm.reset();
            showDialog.value = false;
        },
    });
}
</script>
<template>
    <v-dialog max-width="1000" v-model="showDialog">
        <template v-slot:default="{ isActive }">
            <v-card title="Besondere Arbeitszeitzuschläge">
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form @submit.prevent="submit">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="swhfForm.type"
                                    label="Wochentag"
                                    :error-messages="swhfForm.errors.type"
                                    :items="WEEKDAYS.filter(e => !special_working_hours_factors.find(a => e.key == a.type))"
                                    item-title="value"
                                    item-value="key"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Faktor Zuschlag"
                                    required
                                    :error-messages="swhfForm.errors.extra_charge"
                                    v-model="swhfForm.extra_charge"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn :loading="swhfForm.processing" type="submit" color="primary">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
    <v-card>
        <v-data-table-virtual
            hover
            :items="
                special_working_hours_factors.map(e => ({
                    ...e,
                    type: WEEKDAYS.find(a => a.key === e.type)?.value as Weekday,
                }))
            "
            :headers="[
                { title: 'Tag', key: 'type' },
                { title: 'Faktor', key: 'extra_charge' },
                { title: '', key: 'actions', align: 'end' },
            ]"
        >
            <template v-slot:header.actions>
                <v-btn @click.stop="create()" color="primary">
                    <v-icon icon="mdi-plus"></v-icon>
                </v-btn>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-btn @click.stop="edit(item)" color="primary" size="large" variant="text" icon="mdi-pencil" />

                <v-dialog max-width="1000">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="error" size="large" variant="text" icon="mdi-delete" />
                    </template>

                    <template v-slot:default="{ isActive }">
                        <v-card title="Betriebsstätte löschen">
                            <template #append>
                                <v-btn icon variant="text" @click="isActive.value = false">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </template>
                            <v-card-text>
                                <v-row>
                                    <v-col cols="12"> Bist du dir sicher, dass du diesen Arbeitszeitzuschlag entfernen möchtest? </v-col>
                                    <v-col cols="12" class="text-end">
                                        <v-btn
                                            @click.stop="
                                                router.delete(
                                                    route('specialWorkingHoursFactor.destroy', {
                                                        specialWorkingHoursFactor: item.id,
                                                    }),
                                                    { onSuccess: () => (isActive.value = false) },
                                                )
                                            "
                                            color="error"
                                        >
                                            Löschen
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </template>
                </v-dialog>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
