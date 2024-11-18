<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, TimeAccount, TimeAccountSetting, User, UserPermission, UserWorkingHours, UserWorkingWeek } from '@/types/types';
import { accountType, fillNullishValues, getMaxScrollHeight, getTruncationCylceDisplayName } from '@/utils';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import UserForm from './UserForm.vue';

const props = defineProps<{
    user: User & {
        currentWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
    };
    time_accounts: (TimeAccount & { time_account_setting: TimeAccountSetting })[];
    time_account_settings: TimeAccountSetting[];
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();

const tab = ref<'generalInformation' | 'timeAccounts'>('generalInformation');

const userTimeAccountForm = useForm({
    balance_limit: props.user.currentWorkingHours.weekly_working_hours * 2,
    balance: 0,
    time_account_setting_id: props.time_account_settings[0]?.id ?? null,
    name: '',
});

const timeAccountTransactionForm = useForm({
    from_id: null,
    to_id: null,
    amount: 0,
    description: '',
});
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name + ' bearbeiten'" :backurl="route('user.index')">
        <v-tabs v-model="tab">
            <v-tab value="generalInformation">Allgemeine Informationen</v-tab>
            <v-tab value="timeAccounts">Arbeitszeitkonten</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item style="overflow-y: auto" :style="{ maxHeight: getMaxScrollHeight(48) }" value="generalInformation">
                <UserForm :user :groups :operating_sites :permissions mode="edit"></UserForm>
            </v-tabs-window-item>
            <v-tabs-window-item style="overflow-y: auto" value="timeAccounts">
                <v-card class="mb-4">
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
                            <v-dialog max-width="1000">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn v-bind="activatorProps" color="primary" class="me-2">
                                        <v-icon icon="mdi-transfer"></v-icon>
                                    </v-btn>
                                </template>
                                <template v-slot:default="{ isActive }">
                                    <v-card title="Stundentransaktion durchführen">
                                        <v-card-text>
                                            <v-form
                                                @submit.prevent="
                                                    timeAccountTransactionForm.post(route('timeAccountTransaction.store'), {
                                                        onSuccess: () => {
                                                            isActive.value = false;
                                                            timeAccountTransactionForm.reset();
                                                        },
                                                    })
                                                "
                                            >
                                                <v-row>
                                                    <v-col cols="12" md="4">
                                                        <v-select
                                                            :items="
                                                                time_accounts
                                                                    .filter(t => t.id != timeAccountTransactionForm.to_id)
                                                                    .map(t => ({
                                                                        title: t.name,
                                                                        value: t.id,
                                                                    }))
                                                            "
                                                            label="Von"
                                                            v-model="timeAccountTransactionForm.from_id"
                                                        ></v-select>
                                                    </v-col>
                                                    <v-col cols="12" md="4">
                                                        <v-select
                                                            :items="
                                                                time_accounts
                                                                    .filter(t => t.id != timeAccountTransactionForm.from_id)
                                                                    .map(t => ({
                                                                        title: t.name,
                                                                        value: t.id,
                                                                    }))
                                                            "
                                                            label="Nach"
                                                            v-model="timeAccountTransactionForm.to_id"
                                                        ></v-select>
                                                    </v-col>
                                                    <v-col cols="12" md="4">
                                                        <v-text-field label="Stunden" v-model="timeAccountTransactionForm.amount"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12">
                                                        <v-text-field
                                                            label="Beschreibung"
                                                            hint="Die Beschreibung ist für die darstellung der Historie"
                                                            v-model="timeAccountTransactionForm.description"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" class="text-end">
                                                        <v-btn type="submit" color="primary" :loading="timeAccountTransactionForm.processing">
                                                            Speichern
                                                        </v-btn>
                                                    </v-col>
                                                </v-row>
                                            </v-form>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-dialog>
                            <v-dialog max-width="1000">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn v-bind="activatorProps" color="primary">
                                        <v-icon icon="mdi-plus"></v-icon>
                                    </v-btn>
                                </template>
                                <template v-slot:default="{ isActive }">
                                    <v-card title="Neues Arbeitszeitkonto erstellen">
                                        <v-card-text>
                                            <v-form
                                                @submit.prevent="
                                                    userTimeAccountForm.post(route('user.timeAccount.store', { user: user.id }), {
                                                        onSuccess: () => (isActive.value = false),
                                                    })
                                                "
                                            >
                                                <v-row>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field
                                                            label="Name"
                                                            v-model="userTimeAccountForm.name"
                                                            :error-messages="userTimeAccountForm.errors.name"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-select
                                                            :items="
                                                                time_account_settings.map(s => ({
                                                                    title: `${accountType(s.type)} (${getTruncationCylceDisplayName(
                                                                        s.truncation_cycle_length_in_months,
                                                                    )})`,
                                                                    value: s.id,
                                                                }))
                                                            "
                                                            label="Typ"
                                                            v-model="userTimeAccountForm.time_account_setting_id"
                                                            :error-messages="userTimeAccountForm.errors.time_account_setting_id"
                                                        ></v-select>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field
                                                            label="Limit in Stunden"
                                                            :hint="
                                                                'entspricht ' +
                                                                userTimeAccountForm.balance_limit / user.currentWorkingHours.weekly_working_hours +
                                                                'x wöchentliche Arbeitszeit'
                                                            "
                                                            v-model="userTimeAccountForm.balance_limit"
                                                            :error-messages="userTimeAccountForm.errors.balance_limit"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" md="6">
                                                        <v-text-field
                                                            label="Startbetrag in Stunden"
                                                            v-model="userTimeAccountForm.balance"
                                                            :error-messages="userTimeAccountForm.errors.balance"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col cols="12" class="text-end">
                                                        <v-btn type="submit" color="primary" :loading="userTimeAccountForm.processing">
                                                            Speichern
                                                        </v-btn>
                                                    </v-col>
                                                </v-row>
                                            </v-form>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-dialog>
                        </template>
                    </v-data-table-virtual>
                </v-card>
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
