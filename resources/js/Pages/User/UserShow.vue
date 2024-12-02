<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    CountryProp,
    Group,
    GroupUser,
    OperatingSite,
    OperatingSiteUser,
    OrganizationUser,
    Paginator,
    Permission,
    TimeAccount,
    TimeAccountSetting,
    TimeAccountTransaction,
    Tree,
    User,
    UserWorkingHours,
    UserWorkingWeek,
} from '@/types/types';
import { ref } from 'vue';
import TimeAccounts from './ShowPartials/TimeAccounts.vue';
import TimeAccountTransactions from './ShowPartials/TimeAccountTransactions.vue';
import UserForm from './UserForm.vue';
import UserOrganigram from '../../Components/UserOrganigram.vue';

defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
        organization_user: OrganizationUser;
        operating_site_user: OperatingSiteUser;
        group_user: GroupUser;
    };
    supervisors: Pick<User, 'id' | 'first_name' | 'last_name'>[];

    time_accounts: (Pick<TimeAccount, 'id' | 'user_id' | 'balance' | 'balance_limit' | 'time_account_setting_id' | 'name' | 'deleted_at'> & {
        time_account_setting: TimeAccountSetting;
    })[];
    time_account_settings: TimeAccountSetting[];
    time_account_transactions: Paginator<TimeAccountTransaction & { user: Pick<User, 'id' | 'first_name' | 'last_name'> }>;
    defaultTimeAccountId: TimeAccount['id'];

    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: { name: Permission[keyof Permission]; label: string }[];

    organigramUsers: Tree<Pick<User, 'id' | 'first_name' | 'last_name' | 'email' | 'supervisor_id'>, 'all_supervisees'>[];
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name' | 'email'> | null;
    countries: CountryProp[];
}>();

const tab = ref(route().params['tab'] ?? 'generalInformation');
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name + ' bearbeiten'" :backurl="route('user.index')">
        <v-tabs v-model="tab">
            <v-tab value="generalInformation">Allgemeine Informationen</v-tab>
            <v-tab v-if="can('timeAccount', 'viewIndex')" value="timeAccounts">Arbeitszeitkonten</v-tab>
            <v-tab v-if="can('timeAccountTransaction', 'viewIndex')" value="timeAccountTransactions">Transaktionen</v-tab>
            <v-tab v-if="can('user', 'viewIndex')" value="userOrganigram">Organigramm</v-tab>
        </v-tabs>
        <v-card>
            <v-tabs-window v-model="tab">
                <v-tabs-window-item style="overflow-y: auto" value="generalInformation">
                    <UserForm :countries :supervisors :user :groups :operating_sites mode="edit" :permissions></UserForm>
                </v-tabs-window-item>

                <v-tabs-window-item v-if="can('timeAccount', 'viewIndex')" style="overflow-y: auto" value="timeAccounts">
                    <TimeAccounts :user :time_accounts :time_account_settings :defaultTimeAccountId />
                </v-tabs-window-item>

                <v-tabs-window-item v-if="can('timeAccountTransaction', 'viewIndex')" style="overflow-y: auto" value="timeAccountTransactions">
                    <TimeAccountTransactions :time_accounts :time_account_transactions />
                </v-tabs-window-item>

                <v-tabs-window-item v-if="can('user', 'viewIndex')" style="overflow-y: auto" value="userOrganigram">
                    <UserOrganigram :users="organigramUsers" :supervisor :currentUser="user" />
                </v-tabs-window-item>
            </v-tabs-window>
        </v-card>
    </AdminLayout>
</template>
