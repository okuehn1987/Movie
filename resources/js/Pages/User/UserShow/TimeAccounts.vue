<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import { TimeAccount, TimeAccountSetting, User, UserWorkingHours, UserWorkingWeek } from '@/types/types';
import TimeAccounts from './partial/TimeAccounts.vue';

defineProps<{
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
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="timeAccounts" />
        <TimeAccounts :user :time_accounts :time_account_settings :defaultTimeAccountId />
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
