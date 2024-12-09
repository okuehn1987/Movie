<script setup lang="ts">
import { OperatingSite, OperatingTime } from '@/types/types';
import { Link, useForm } from '@inertiajs/vue3';
import { DateTime } from 'luxon';

const props = defineProps<{
    operatingSite: OperatingSite & { operating_times: OperatingTime[] };
}>();

const WEEKDAYS = [
    { key: 'monday', value: 'Montag' },
    { key: 'tuesday', value: 'Dienstag' },
    { key: 'wednesday', value: 'Mittwoch' },
    { key: 'thursday', value: 'Donnerstag' },
    { key: 'friday', value: 'Freitag' },
    { key: 'saturday', value: 'Samstag' },
    { key: 'sunday', value: 'Sonntag' },
];

const operatingTimeForm = useForm({
    start: '',
    end: '',
    type: 'monday',
    operating_site_id: props.operatingSite.id,
});

function submitOperatingTime() {
    operatingTimeForm.post(route('operatingTime.store'), {
        onSuccess: () => operatingTimeForm.reset(),
    });
}
</script>
<template>
    <v-card>
        <v-card-text>
            <v-row>
                <v-col cols="12"><h3>Aktuelle Arbeitszeiten</h3></v-col>

                <v-list item-props hover>
                    <v-list-item
                        v-for="operatingTime in operatingSite.operating_times.toSorted(
                            (a, b) => WEEKDAYS.findIndex(w => a.type === w.key) - WEEKDAYS.findIndex(w => b.type === w.key),
                        )"
                        :key="operatingTime.id"
                        :title="WEEKDAYS.find(e => e.key === operatingTime.type)?.value"
                        :subtitle="
                            DateTime.fromFormat(operatingTime.start, 'HH:mm:ss').toFormat('HH:mm') +
                            ' Uhr' +
                            ' - ' +
                            DateTime.fromFormat(operatingTime.end, 'HH:mm:ss').toFormat('HH:mm') +
                            ' Uhr'
                        "
                    >
                        <template #append v-if="can('operatingTime', 'delete')">
                            <Link
                                method="delete"
                                :href="
                                    route('operatingTime.destroy', {
                                        operatingTime: operatingTime.id,
                                    })
                                "
                            >
                                <v-btn color="error" class="ms-2 mt-1" size="small" variant="text" icon="mdi-delete"></v-btn>
                            </Link>
                        </template>
                    </v-list-item>
                </v-list>
            </v-row>
        </v-card-text>
        <v-card-text>
            <v-form @submit.prevent="submitOperatingTime" v-if="can('operatingTime', 'create')">
                <v-row>
                    <v-col cols="12"><h3>Arbeitszeiten hinzufügen</h3></v-col>

                    <v-col cols="12" md="4">
                        <v-select v-model="operatingTimeForm.type" label="Wochentag" :items="WEEKDAYS" item-title="value" item-value="key"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            type="time"
                            v-model="operatingTimeForm.start"
                            :error-messages="operatingTimeForm.errors.start"
                            label="Beginn des Arbeitstages"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field
                            type="time"
                            v-model="operatingTimeForm.end"
                            :error-messages="operatingTimeForm.errors.end"
                            label="Ende des Arbeitstages"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" class="text-end">
                        <v-btn type="submit" :loading="operatingTimeForm.processing" color="primary">Hinzufügen</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
