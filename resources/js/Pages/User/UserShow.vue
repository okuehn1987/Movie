<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, User, UserPermission, UserWorkingWeek, UserWorkingHours, TimeAccount, TimeAccountSetting } from '@/types/types';
import UserForm from './UserForm.vue';
import { ref } from 'vue';
import { fillNullishValues, getMaxScrollHeight } from '@/utils';

defineProps<{
    user: User & {
        lastWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
    };
    time_accounts: (TimeAccount & { time_account_setting: TimeAccountSetting })[];
    time_account_settings: TimeAccountSetting[];
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();

const tab = ref<'generalInformation' | 'timeAccounts'>('generalInformation');

function accountType(type: string) {
    return type === 'default' ? 'Standard' : type;
}
</script>
<template>
    <AdminLayout title="Nutzer bearbeiten" :backurl="route('user.index')">
        <v-tabs v-model="tab">
            <v-tab value="generalInformation">Allgemeine Informationen</v-tab>
            <v-tab value="timeAccounts">Arbeitszeitkonten</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item style="overflow-y: auto" :style="{ maxHeight: getMaxScrollHeight(48) }" value="generalInformation">
                <UserForm :user :groups :operating_sites :permissions mode="edit"></UserForm>
            </v-tabs-window-item>
            <v-tabs-window-item style="overflow-y: auto" value="timeAccounts">
                <v-card class="mb-4">
                    <v-data-table
                        :items="
                            time_accounts.map(account =>
                                fillNullishValues({
                                    ...account,
                                    type: accountType(account.time_account_setting.type),
                                    truncation_cycle_length: { 0: 'nie', 1: 'monatlich', 3: 'quartalsweise', 6: 'halbjährlich', 12: 'jährlich' }[
                                        account.time_account_setting.truncation_cycle_length
                                    ],
                                }),
                            )
                        "
                        :headers="[
                            {
                                title: 'Überstunden',
                                key: 'balance',
                            },
                            {
                                title: 'Limit',
                                key: 'balance_limit',
                            },
                            {
                                title: 'Typ',
                                key: 'type',
                            },
                            {
                                title: 'Berechnungszeitraum',
                                key: 'truncation_cycle_length',
                            },
                        ]"
                    ></v-data-table>
                </v-card>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
