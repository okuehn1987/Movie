<script setup lang="ts">
import { TimeAccount, TimeAccountSetting, User, UserWorkingHours, UserWorkingWeek } from '@/types/types';
import { accountType, getTruncationCycleDisplayName, roundTo } from '@/utils';

defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        currentWorkingWeek: UserWorkingWeek;
    };
    item: Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> & {
        time_account_setting: TimeAccountSetting;
    };
    time_account_settings: TimeAccountSetting[];
}>();

const timeAccountSettingsForm = useForm({
    name: '',
    time_account_setting_id: null as null | TimeAccountSetting['id'],
    balance_limit: 0,
});
</script>
<template>
    <v-dialog @after-leave="timeAccountSettingsForm.reset()" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn
                v-bind="activatorProps"
                @click="
                    () => {
                        timeAccountSettingsForm.name = item.name;
                        timeAccountSettingsForm.balance_limit = roundTo(item.balance_limit / 3600, 2);
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
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
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
                                    :disabled="item.time_account_setting.type === null"
                                    label="Typ"
                                    :items="
                                        time_account_settings
                                            .filter(s => item.time_account_setting.type === null || s.type != null)
                                            .map(s => ({
                                                title: `${accountType(s.type)} (${getTruncationCycleDisplayName(
                                                    s.truncation_cycle_length_in_months,
                                                )})`,
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
                                    type="number"
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
