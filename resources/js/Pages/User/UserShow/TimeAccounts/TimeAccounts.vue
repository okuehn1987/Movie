<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { TimeAccount, TimeAccountSetting, User, UserWorkingHours, UserWorkingWeek } from '@/types/types';
import { accountType, formatDuration, getTruncationCycleDisplayName } from '@/utils';
import { computed } from 'vue';
import UserShowNavBar from '../partial/UserShowNavBar.vue';
import TimeAccountTransferForm from './partial/TimeAccountTransferForm.vue';
import NewTimeAccountForm from './partial/NewTimeAccountForm.vue';
import TimeAccountTransactionForm from './partial/TimeAccountTransactionForm.vue';
import TimeAccountSettingsForm from './partial/TimeAccountSettingsForm.vue';
import TimeAccountDeleteForm from './partial/TimeAccountDeleteForm.vue';

const props = defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        currentWorkingWeek: UserWorkingWeek;
    };

    time_accounts: (Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> & {
        time_account_setting: TimeAccountSetting;
    })[];
    time_account_settings: TimeAccountSetting[];
    defaultTimeAccountId: TimeAccount['id'];
}>();

const timeAccounts = computed(() => props.time_accounts.filter(t => t.deleted_at == null));

function getBalanceType(t: Pick<TimeAccount, 'balance'>) {
    if (t.balance > 0) return 'positive';
    if (t.balance < 0) return 'negative';
    return 'balanced';
}
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="timeAccounts" />
        <v-data-table-virtual
            :items="
                timeAccounts.map(account => ({
                    ...account,
                    type: accountType(account.time_account_setting.type),
                    truncation_cycle_length_in_months: getTruncationCycleDisplayName(account.time_account_setting.truncation_cycle_length_in_months),
                }))
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
                <TimeAccountTransferForm v-if="can('timeAccountTransaction', 'create')" :time_accounts />
                <NewTimeAccountForm v-if="can('timeAccount', 'create')" :user :time_account_settings />
            </template>
            <template v-slot:item.actions="{ item }">
                <div class="d-flex justify-end">
                    <TimeAccountTransactionForm v-if="can('timeAccountTransaction', 'create')" :item />
                    <TimeAccountSettingsForm v-if="can('timeAccount', 'update')" :item :time_account_settings :user />
                    <TimeAccountDeleteForm :item v-if="item.id !== defaultTimeAccountId && can('timeAccount', 'delete')" />
                    <div v-else-if="timeAccounts.length > 1 && can('timeAccount', 'delete')" style="width: 48px"></div>
                </div>
            </template>
            <template v-slot:item.balance="{ item }">
                <v-chip :color="{ positive: 'success', negative: 'error', balanced: 'black' }[getBalanceType(item)]">
                    {{ formatDuration(item.balance) }}
                </v-chip>
            </template>
            <template v-slot:item.balance_limit="{ item }">
                {{ formatDuration(item.balance_limit) }}
            </template>
            <template v-slot:item.type="{ item }">
                <v-chip color="purple-darken-1" v-if="item.id == defaultTimeAccountId">{{ item.type }}</v-chip>
                <span v-else>{{ item.type }}</span>
            </template>
        </v-data-table-virtual>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
