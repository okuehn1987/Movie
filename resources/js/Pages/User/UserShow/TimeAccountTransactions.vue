<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import TimeAccountTransactions from './partial/TimeAccountTransactions.vue';
import { Paginator, TimeAccount, TimeAccountSetting, TimeAccountTransaction, User } from '@/types/types';

defineProps<{
    user: User;
    time_accounts: (Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> & {
        time_account_setting: TimeAccountSetting;
    })[];
    time_account_transactions: Paginator<TimeAccountTransaction & { user: Pick<User, 'id' | 'first_name' | 'last_name'> }>;
}>();
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="timeAccountTransactions"></UserShowNavBar>
        <TimeAccountTransactions :time_accounts :time_account_transactions />
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
