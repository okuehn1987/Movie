<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CountryProp, Group, OperatingSite, Permission, Relations, User, UserLeaveDays } from '@/types/types';
import { useMaxScrollHeight } from '@/utils';
import UserForm from '../partial/UserForm.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';

defineProps<{
    user: User & {
        supervisor: Pick<User, 'id'>;
        user_leave_days: (UserLeaveDays | null)[];
    } & Pick<
            Relations<'user'>,
            | 'organization_user'
            | 'operating_site_user'
            | 'group_user'
            | 'user_working_hours'
            | 'user_working_weeks'
            | 'current_address'
            | 'home_office_day_generators'
        >;
    possibleSupervisors: Pick<User, 'id' | 'first_name' | 'last_name'>[];
    operating_sites: Pick<OperatingSite, 'id' | 'name'>[];
    groups: Pick<Group, 'id' | 'name'>[];
    countries: CountryProp[];
    permissions: { name: Permission[keyof Permission]; label: string }[];
}>();

const tableHeight = useMaxScrollHeight(48);
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="generalInformation"></UserShowNavBar>
        <div style="overflow: auto" :style="{ maxHeight: tableHeight }">
            <UserForm :countries :supervisors="possibleSupervisors" :user :groups :operating_sites mode="edit" :permissions></UserForm>
        </div>
    </AdminLayout>
</template>
