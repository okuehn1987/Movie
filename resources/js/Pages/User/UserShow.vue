<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    Absence,
    AbsenceType,
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
    UserLeaveDays,
    UserWorkingHours,
    UserWorkingWeek,
} from '@/types/types';
import { ref } from 'vue';
import TimeAccounts from './ShowPartials/TimeAccounts.vue';
import TimeAccountTransactions from './ShowPartials/TimeAccountTransactions.vue';
import UserForm from './UserForm.vue';
import UserOrganigram from '../../Components/UserOrganigram.vue';
import { DateTime } from 'luxon';
import UpdateProfileInformationForm from '../Profile/Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from '../Profile/Partials/UpdatePasswordForm.vue';

defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        leaveDaysForYear: number;
        usedLeaveDaysForYear: number;
        userWorkingWeek: UserWorkingWeek;
        organization_user: OrganizationUser;
        operating_site_user: OperatingSiteUser;
        group_user: GroupUser;
        absences: (Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'user_id' | 'absence_type_id'> & {
            absence_type: Pick<AbsenceType, 'id' | 'name'>;
            usedDays: number;
        })[];
        user_leave_days: UserLeaveDays[];
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

    mustVerifyEmail?: boolean;
    status?: string;
}>();

const tab = ref(route().params['tab'] ?? 'generalInformation');
</script>
<template>
    <AdminLayout
        :title="`${user.first_name} ${user.last_name} ${can('user', 'update') ? 'bearbeiten' : ''}`"
        :backurl="user.id == $page.props.auth.user.id ? route('dashboard') : route('user.index')"
    >
        <v-tabs v-model="tab">
            <v-tab value="generalInformation">Allgemeine Informationen</v-tab>
            <v-tab value="absences">Abwesenheiten</v-tab>
            <v-tab v-if="can('timeAccount', 'viewIndex')" value="timeAccounts">Arbeitszeitkonten</v-tab>
            <v-tab v-if="can('timeAccountTransaction', 'viewIndex')" value="timeAccountTransactions">Transaktionen</v-tab>
            <v-tab v-if="can('user', 'viewIndex')" value="userOrganigram">Organigramm</v-tab>
            <v-tab v-if="user.id == $page.props.auth.user.id" value="profile">Profil</v-tab>
        </v-tabs>

        <v-tabs-window v-model="tab">
            <v-tabs-window-item style="overflow-y: auto" value="absences">
                <v-card>
                    <v-card-title>Abwesenheiten für das Jahr {{ DateTime.now().year }}</v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12">
                                <v-data-table-virtual
                                    id="userAbsenceTable"
                                    :items="
                                        user.absences.map(e => ({
                                            ...e,
                                            start: DateTime.fromSQL(e.start).toFormat('dd.MM.yyyy'),
                                            end: DateTime.fromSQL(e.end).toFormat('dd.MM.yyyy'),
                                            absence_type: e.absence_type.name,
                                            usedDays: e.usedDays.toString(),
                                        }))
                                    "
                                    :headers="[
                                        { title: 'Start', key: 'start' },
                                        { title: 'Ende', key: 'end' },
                                        { title: 'Status', key: 'status' },
                                        { title: 'Art', key: 'absence_type' },
                                        { title: 'Genutzte Tage', key: 'usedDays' },
                                    ]"
                                >
                                    <template v-slot:item.status="{ item }">
                                        <v-chip :color="{ accepted: 'success', created: 'warning', declined: 'error' }[item.status]">
                                            {{ { accepted: 'Genehmigt', created: 'Offen', declined: 'Abgelehnt' }[item.status] }}
                                        </v-chip>
                                    </template>
                                    <template v-slot:body.append>
                                        <tr class="font-weight-bold">
                                            <td colspan="4">
                                                Genutzte Urlaubstage für das Jahr
                                                {{ DateTime.now().year }}:
                                            </td>
                                            <td>
                                                {{ user.usedLeaveDaysForYear + ' von ' + user.leaveDaysForYear }}
                                            </td>
                                        </tr>
                                    </template>
                                </v-data-table-virtual>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-tabs-window-item>
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
            <v-tabs-window-item v-if="user.id == $page.props.auth.user.id" value="profile">
                <v-row>
                    <v-col cols="12" sm="6">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </v-col>
                    <v-col cols="12" sm="6">
                        <UpdatePasswordForm />
                    </v-col>
                </v-row>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
