<script setup lang="ts">
import { TimeAccountSetting, TRUNCATION_CYCLES } from '@/types/types';
import { accountType, getTruncationCycleDisplayName } from '@/utils';

defineProps<{ timeAccountSettings: TimeAccountSetting[] }>();

const timeAccountSettingForm = useForm({
    type: '',
    truncation_cycle_length_in_months: null,
});
</script>
<template>
    <v-card>
        <v-data-table-virtual
            :items="
                timeAccountSettings.map(s => ({
                    ...s,
                    type: accountType(s.type),
                    truncation_cycle_length_in_months: getTruncationCycleDisplayName(s.truncation_cycle_length_in_months),
                }))
            "
            :headers="[
                { title: 'Art', key: 'type' },
                { title: 'Berechnungszeitraum', key: 'truncation_cycle_length_in_months' },
                { title: '', key: 'actions', align: 'end' },
            ]"
        >
            <template v-slot:header.actions>
                <v-dialog max-width="1000" v-if="can('timeAccountSetting', 'create')">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="primary">
                            <v-icon icon="mdi-plus"></v-icon>
                        </v-btn>
                    </template>
                    <template v-slot:default="{ isActive }">
                        <v-card title="Neue Variante Erstellen">
                            <template #append>
                                <v-btn icon variant="text" @click.stop="isActive.value = false">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </template>
                            <v-card-text>
                                <v-form
                                    @submit.prevent="
                                        timeAccountSettingForm.post(route('timeAccountSetting.store'), {
                                            onSuccess: () => (isActive.value = false),
                                        })
                                    "
                                >
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field
                                                label="Bezeichnung"
                                                v-model="timeAccountSettingForm.type"
                                                :error-messages="timeAccountSettingForm.errors.type"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-select
                                                :items="TRUNCATION_CYCLES.map(e => ({ title: getTruncationCycleDisplayName(e), value: e }))"
                                                label="Berechnungszeitraum (optional)"
                                                v-model="timeAccountSettingForm.truncation_cycle_length_in_months"
                                                :error-messages="timeAccountSettingForm.errors.truncation_cycle_length_in_months"
                                            ></v-select>
                                        </v-col>
                                        <v-col cols="12" class="text-end">
                                            <v-btn type="submit" color="primary" :loading="timeAccountSettingForm.processing">Speichern</v-btn>
                                        </v-col>
                                    </v-row>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </template>
                </v-dialog>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
