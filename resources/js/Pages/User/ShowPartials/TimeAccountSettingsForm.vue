<script setup lang="ts">
import { TimeAccount, TimeAccountSetting, User, UserWorkingHours, UserWorkingWeek } from '@/types/types';
import { accountType, getTruncationCylceDisplayName, roundTo } from '@/utils';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
    };
    item: TimeAccount & { time_account_setting: TimeAccountSetting };
    time_account_settings: TimeAccountSetting[];
}>();

const timeAccountSettingsForm = useForm({
    name: '',
    time_account_setting_id: null as null | TimeAccountSetting['id'],
    balance_limit: 0,
});

watch(
    () => timeAccountSettingsForm.balance_limit,
    () => {
        timeAccountSettingsForm.balance_limit = +timeAccountSettingsForm.balance_limit
            .toString()
            .replace(/[^\d,.]/g, '')
            .replace(/,/, '.');
    },
);
</script>
<template>
    <v-dialog @after-leave="timeAccountSettingsForm.reset()" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn
                v-bind="activatorProps"
                @click="
                    () => {
                        timeAccountSettingsForm.name = item.name;
                        timeAccountSettingsForm.balance_limit = item.balance_limit;
                        timeAccountSettingsForm.time_account_setting_id = item.time_account_setting_id;
                    }
                "
                color="primary"
                icon
                variant="text"
            >
                <v-icon icon="mdi-cog"></v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="`Einstellungen für Konto ${item.name} bearbeiten`">
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            timeAccountSettingsForm.patch(route('timeAccount.update', { timeAccount: item.id }), {
                                onSuccess: () => {
                                    isActive.value = false;
                                    timeAccountSettingsForm.reset();
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="timeAccountSettingsForm.name"
                                    label="Name"
                                    :error-messages="timeAccountSettingsForm.errors.name"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    label="Typ"
                                    :items="
                                        time_account_settings.map(s => ({
                                            title: `${accountType(s.type)} (${getTruncationCylceDisplayName(s.truncation_cycle_length_in_months)})`,
                                            value: s.id,
                                        }))
                                    "
                                    v-model="timeAccountSettingsForm.time_account_setting_id"
                                    :error-messages="timeAccountSettingsForm.errors.time_account_setting_id"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="timeAccountSettingsForm.balance_limit"
                                    label="Limit in Stunden"
                                    :hint="
                                        'entspricht ' +
                                        roundTo(timeAccountSettingsForm.balance_limit / user.currentWorkingHours.weekly_working_hours, 2) +
                                        'x wöchentliche Arbeitszeit'
                                    "
                                    :error-messages="timeAccountSettingsForm.errors.balance_limit"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn type="submit" color="primary" :loading="timeAccountSettingsForm.processing"> Speichern </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
