<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    CountryProp,
    Group,
    GroupUser,
    OperatingSite,
    OperatingSiteUser,
    OrganizationUser,
    Permission,
    User,
    UserLeaveDays,
    UserWorkingHours,
    UserWorkingWeek,
} from '@/types/types';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import UserForm from '../UserForm.vue';
import { useMaxScrollHeight } from '@/utils';

defineProps<{
    user: User & {
        supervisor: Pick<User, 'id'>;
        organization_user: OrganizationUser;
        operating_site_user: OperatingSiteUser;
        group_user: GroupUser;

        userWorkingHours: UserWorkingHours[];
        userWorkingWeeks: UserWorkingWeek[];
        userLeaveDays: (UserLeaveDays | null)[];
    };
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
