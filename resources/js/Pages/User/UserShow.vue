<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    Group,
    OperatingSite,
    Paginator,
    TimeAccount,
    TimeAccountSetting,
    TimeAccountTransaction,
    User,
    UserPermission,
    UserWorkingHours,
    UserWorkingWeek,
} from '@/types/types';
import {
    accountType,
    DEFAULT_ACCOUNTYPE_NAME,
    fillNullishValues,
    getMaxScrollHeight,
    getTruncationCylceDisplayName,
    roundTo,
    usePagination,
} from '@/utils';
import { ref, toRefs } from 'vue';
import NewTimeAccountForm from './ShowPartials/NewTimeAccountForm.vue';
import TimeAccountSettingsForm from './ShowPartials/TimeAccountSettingsForm.vue';
import TimeAccountTransactionForm from './ShowPartials/TimeAccountTransactionForm.vue';
import TimeAccountTransferForm from './ShowPartials/TimeAccountTransferForm.vue';
import UserForm from './UserForm.vue';
import { DateTime } from 'luxon';

const props = defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
    };
    time_accounts: (TimeAccount & { time_account_setting: TimeAccountSetting })[];
    time_account_settings: TimeAccountSetting[];
    time_account_transactions: Paginator<TimeAccountTransaction & { user: Pick<User, 'id' | 'first_name' | 'last_name'> }>;
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();

const tab = ref<'generalInformation' | 'timeAccounts'>('generalInformation');

const { currentPage, data: timeAccountTransactions, lastPage } = usePagination(toRefs(props), 'time_account_transactions');

function getTransactionType(t: TimeAccountTransaction): 'positive' | 'negative' | 'transfer' {
    if (t.from_id && t.to_id) return 'transfer';
    if (t.to_id) return 'positive';
    return 'negative';
}
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name + ' bearbeiten'" :backurl="route('user.index')">
        <v-tabs v-model="tab">
            <v-tab value="generalInformation">Allgemeine Informationen</v-tab>
            <v-tab value="timeAccounts">Arbeitszeitkonten</v-tab>
            <v-tab value="timeAccountTransactions">Transaktionen</v-tab>
        </v-tabs>
        <v-card>
            <v-tabs-window v-model="tab">
                <v-tabs-window-item style="overflow-y: auto" :style="{ maxHeight: getMaxScrollHeight(48) }" value="generalInformation">
                    <UserForm :user :groups :operating_sites :permissions mode="edit"></UserForm>
                </v-tabs-window-item>

                <v-tabs-window-item style="overflow-y: auto" value="timeAccounts">
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
                        <template v-slot:item.balance="{ item }">
                            <v-chip color="success" v-if="item.balance > 0">{{ item.balance }}</v-chip>
                            <v-chip color="error" v-if="item.balance < 0">{{ item.balance }}</v-chip>
                            <v-chip color="black" v-if="item.balance == 0">{{ item.balance }}</v-chip>
                        </template>
                        <template v-slot:item.type="{ item }">
                            <v-chip color="purple-darken-1" v-if="item.type == DEFAULT_ACCOUNTYPE_NAME">{{ item.type }}</v-chip>
                            <span v-else>{{ item.type }}</span>
                        </template>
                    </v-data-table-virtual>
                </v-tabs-window-item>
                <v-tabs-window-item style="overflow-y: auto" value="timeAccountTransactions">
                    <v-data-table-virtual
                        hover
                        :items="
                            timeAccountTransactions.map(t => ({
                                ...t,
                                created_at: DateTime.fromISO(t.created_at).toFormat('dd.MM.yyyy HH:ii'),
                                transactionType: getTransactionType(t),
                                from: time_accounts.find(ta => ta.id == t.from_id)?.name,
                                to: time_accounts.find(ta => ta.id == t.to_id)?.name,
                                amount: roundTo(t.amount, 2),
                            }))
                        "
                        :headers="[
                            { title: '', key: 'transactionType', width: 0, sortable: false },
                            { title: 'Durchgeführt', key: 'created_at' },
                            { title: 'Von', key: 'from' },
                            { title: 'Nach', key: 'to' },
                            { title: 'Durch', key: 'modified_by' },
                            { title: 'Beschreibung', key: 'description' },
                            {
                                title: 'Stunden',
                                key: 'amount',
                                align: 'end',
                                width: 0,
                                cellProps: { class: 'pe-8' },
                                headerProps: { class: 'pe-8' },
                            },
                        ]"
                    >
                        <template v-slot:item.transactionType="{ item }">
                            <v-icon icon="mdi-plus-circle" color="success" v-if="item.transactionType == 'positive'"></v-icon>
                            <v-icon icon="mdi-minus-circle" color="error" v-else-if="item.transactionType == 'negative'"></v-icon>
                            <v-icon icon="mdi-circle" color="grey" v-else-if="item.transactionType == 'transfer'"></v-icon>
                        </template>

                        <template v-slot:item.amount="{ item }">
                            <v-chip color="success" v-if="item.transactionType == 'positive'">
                                <span>+ {{ item.amount }}</span>
                            </v-chip>
                            <v-chip color="error" v-else-if="item.transactionType == 'negative'">
                                <span>- {{ item.amount }}</span>
                            </v-chip>
                            <v-chip color="grey" v-else-if="item.transactionType == 'transfer'">
                                <span class="text-black"> {{ item.amount }}</span>
                            </v-chip>
                        </template>

                        <template v-slot:item.modified_by="{ item }">
                            <v-chip color="purple-darken-1" v-if="item.modified_by">{{ item.user.first_name }} {{ item.user.last_name }}</v-chip>
                            <span v-else>System </span>
                        </template>
                        <template v-slot:bottom>
                            <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                        </template>
                    </v-data-table-virtual>
                </v-tabs-window-item>
            </v-tabs-window>
        </v-card>
    </AdminLayout>
</template>
