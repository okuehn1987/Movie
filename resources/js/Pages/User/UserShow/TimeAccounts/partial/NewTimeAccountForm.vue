<script setup lang="ts">
import { Relations, TimeAccountSetting, User } from '@/types/types';
import { accountType, getTruncationCycleDisplayName, roundTo } from '@/utils';

const props = defineProps<{
    user: User & Pick<Relations<'user'>, 'current_working_hours'>;
    time_account_settings: TimeAccountSetting[];
}>();

const newTimeAccountForm = useForm({
    balance_limit: (props.user.current_working_hours?.weekly_working_hours ?? 40) * 2,
    balance: 0,
    time_account_setting_id: props.time_account_settings.filter(t => t.type != null)[0]?.id ?? null,
    name: '',
});
</script>
<template>
    <v-dialog @after-leave="newTimeAccountForm.reset()" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="primary">
                <v-icon icon="mdi-plus"></v-icon>
            </v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card title="Neues Arbeitszeitkonto erstellen">
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click.stop="
                            isActive.value = false;
                            newTimeAccountForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            newTimeAccountForm.post(route('user.timeAccount.store', { user: user.id }), {
                                onSuccess: () => {
                                    isActive.value = false;
                                    newTimeAccountForm.reset();
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Name"
                                    v-model="newTimeAccountForm.name"
                                    :error-messages="newTimeAccountForm.errors.name"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    :items="
                                        time_account_settings
                                            .filter(t => t.type != null)
                                            .map(s => ({
                                                title: `${accountType(s.type)} (${getTruncationCycleDisplayName(
                                                    s.truncation_cycle_length_in_months,
                                                )})`,
                                                value: s.id,
                                            }))
                                    "
                                    label="Typ"
                                    v-model="newTimeAccountForm.time_account_setting_id"
                                    :error-messages="newTimeAccountForm.errors.time_account_setting_id"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Limit in Stunden"
                                    :hint="
                                        'entspricht ' +
                                        roundTo(newTimeAccountForm.balance_limit / (user.current_working_hours?.weekly_working_hours ?? 40), 2) +
                                        'x wöchentliche Arbeitszeit'
                                    "
                                    v-model="newTimeAccountForm.balance_limit"
                                    :error-messages="newTimeAccountForm.errors.balance_limit"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Startbetrag in Stunden"
                                    v-model="newTimeAccountForm.balance"
                                    :error-messages="newTimeAccountForm.errors.balance"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn type="submit" color="primary" :loading="newTimeAccountForm.processing">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
