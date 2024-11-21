<script setup lang="ts">
import { Paginator, TimeAccount, TimeAccountSetting, TimeAccountTransaction, User } from '@/types/types';
import { roundTo, usePagination } from '@/utils';
import { DateTime } from 'luxon';
import { toRefs } from 'vue';

const props = defineProps<{
    time_accounts: (TimeAccount & { time_account_setting: TimeAccountSetting })[];
    time_account_transactions: Paginator<TimeAccountTransaction & { user: Pick<User, 'id' | 'first_name' | 'last_name'> }>;
}>();

const { currentPage, data: timeAccountTransactions, lastPage } = usePagination(toRefs(props), 'time_account_transactions');

function getTransactionType(t: TimeAccountTransaction): 'positive' | 'negative' | 'transfer' {
    if (t.from_id && t.to_id) return 'transfer';
    if (t.to_id) return 'positive';
    return 'negative';
}
</script>
<template>
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
</template>
<style lang="scss" scoped></style>
