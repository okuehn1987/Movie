<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    Group,
    OperatingSite,
    Paginator,
    TimeAccount,
    TimeAccountSetting,
    User,
    UserPermission,
    UserWorkingHours,
    UserWorkingWeek,
} from '@/types/types';
import { accountType, fillNullishValues, getMaxScrollHeight, getTruncationCylceDisplayName, usePagination } from '@/utils';
import { ref, toRefs } from 'vue';
import NewTimeAccountForm from './ShowPartials/NewTimeAccountForm.vue';
import TimeAccountSettingsForm from './ShowPartials/TimeAccountSettingsForm.vue';
import TimeAccountTransactionForm from './ShowPartials/TimeAccountTransactionForm.vue';
import TimeAccountTransferForm from './ShowPartials/TimeAccountTransferForm.vue';
import UserForm from './UserForm.vue';

const props = defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
    };
    time_accounts: (TimeAccount & { time_account_setting: TimeAccountSetting })[];
    time_account_settings: TimeAccountSetting[];
    time_account_transactions: Paginator<Record<string, unknown>>;
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();

const tab = ref<'generalInformation' | 'timeAccounts'>('generalInformation');

const { currentPage, data, lastPage } = usePagination(toRefs(props), 'time_account_transactions');
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name + ' bearbeiten'" :backurl="route('user.index')">
        {{ time_account_transactions }}
        <v-tabs v-model="tab">
            <v-tab value="generalInformation">Allgemeine Informationen</v-tab>
            <v-tab value="timeAccounts">Arbeitszeitkonten</v-tab>
            <v-tab value="timeAccountTransactions">Transaktionen</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item style="overflow-y: auto" :style="{ maxHeight: getMaxScrollHeight(48) }" value="generalInformation">
                <UserForm :user :groups :operating_sites :permissions mode="edit"></UserForm>
            </v-tabs-window-item>

            <v-tabs-window-item style="overflow-y: auto" value="timeAccounts">
                <v-card class="mb-4">
                    <v-data-table-virtual
                        :items="
                            time_accounts.map(account =>
                                fillNullishValues({
                                    ...account,
                                    type: accountType(account.time_account_setting.type),
                                    truncation_cycle_length_in_months: getTruncationCylceDisplayName(
                                        account.time_account_setting.truncation_cycle_length_in_months,
                                    ),
                                }),
                            )
                        "
                        :headers="[
                            { title: 'Name', key: 'name' },
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
                                key: 'truncation_cycle_length_in_months',
                            },
                            {
                                title: '',
                                key: 'actions',
                                align: 'end',
                            },
                        ]"
                    >
                        <template v-slot:header.actions>
                            <TimeAccountTransferForm :time_accounts />
                            <NewTimeAccountForm :user :time_account_settings />
                        </template>
                        <template v-slot:item.actions="{ item }">
                            <TimeAccountTransactionForm :item />
                            <TimeAccountSettingsForm :item :time_account_settings :user />
                        </template>
                    </v-data-table-virtual>
                </v-card>
            </v-tabs-window-item>
            <v-tabs-window-item style="overflow-y: auto" value="timeAccountTransactions">
                <v-data-table-virtual :items="data || []">
                    <template v-slot:bottom> <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination> </template
                ></v-data-table-virtual>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
