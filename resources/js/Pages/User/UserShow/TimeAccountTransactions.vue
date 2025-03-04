<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Paginator, TimeAccount, TimeAccountSetting, TimeAccountTransaction, User } from '@/types/types';
import { formatDuration, usePagination } from '@/utils';
import { DateTime } from 'luxon';
import { toRefs } from 'vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';

const props = defineProps<{
    user: User;
    time_accounts: (Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> & {
        time_account_setting: TimeAccountSetting;
    })[];
    time_account_transactions: Paginator<TimeAccountTransaction & { user: Pick<User, 'id' | 'first_name' | 'last_name'> }>;
}>();

const {
    currentPage,
    data: timeAccountTransactions,
    lastPage,
    itemsPerPage,
} = usePagination(toRefs(props), 'time_account_transactions', { tab: 'timeAccountTransactions' });

function getTransactionType(t: TimeAccountTransaction) {
    if (t.from_id && t.to_id) return 'transfer';
    if (t.to_id) return 'positive';
    return 'negative';
}

function getAccountName(id: TimeAccountTransaction['from_id'] | TimeAccountTransaction['to_id']) {
    const account = props.time_accounts.find(ta => ta.id == id);
    if (!account) return '';
    return account?.name + (account?.deleted_at ? ' (gelöscht)' : '');
}
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="timeAccountTransactions"></UserShowNavBar>
        <v-data-table
            :itemsPerPage
            :items="
                timeAccountTransactions.map(t => ({
                    ...t,
                    created_at: DateTime.fromISO(t.created_at).toFormat('dd.MM.yyyy HH:mm'),
                    transactionType: getTransactionType(t),
                    from: getAccountName(t.from_id),
                    to: getAccountName(t.to_id),
                    amount: t.amount * (getTransactionType(t) == 'negative' ? -1 : 1),
                    formatted_amount: formatDuration(t.amount),
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
                    <span>+ {{ item.formatted_amount }}</span>
                </v-chip>
                <v-chip color="error" v-else-if="item.transactionType == 'negative'">
                    <span>- {{ item.formatted_amount }}</span>
                </v-chip>
                <v-chip color="grey" v-else-if="item.transactionType == 'transfer'">
                    <span class="text-black"> {{ item.formatted_amount }}</span>
                </v-chip>
            </template>

            <template v-slot:item.modified_by="{ item }">
                <v-chip color="purple-darken-1" v-if="item.modified_by">{{ item.user.first_name }} {{ item.user.last_name }}</v-chip>
                <span v-else>System </span>
            </template>
            <template v-slot:bottom>
                <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
            </template>
        </v-data-table>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
