<script setup lang="ts">
import { Country, CountryProp, DateString, FederalState, Group, OperatingSite, Permission, User, UserLeaveDays, Weekday } from '@/types/types';
import { getStates } from '@/utils';
import { DateTime, Info } from 'luxon';
import { nextTick } from 'vue';
import HertaUserFormSections from './HertaUserFormSections.vue';
import PermissionSelector from './PermissionSelector.vue';
import { FormData, UserProp } from './userFormTypes';

const props = defineProps<{
    user?: UserProp;
    supervisors: Pick<User, 'id' | 'first_name' | 'last_name'>[];
    operating_sites: Pick<OperatingSite, 'id' | 'name'>[];
    groups: Pick<Group, 'id' | 'name'>[];
    mode: 'create' | 'edit';
    countries: CountryProp[];
    permissions: { name: Permission[keyof Permission]; label: string }[];
}>();

const emit = defineEmits<{
    close: [];
}>();

const WEEKDAYS = Info.weekdays('long', { locale: 'en' }).map(e => e.toLowerCase()) as Weekday[];

const userForm = useForm<FormData>({
    first_name: '',
    last_name: '',
    email: '',
    date_of_birth: null as null | string,
    city: '',
    zip: '',
    street: '',
    house_number: '',
    address_suffix: '',
    country: props.countries[0]?.value ?? ('' as Country),
    federal_state: '',
    phone_number: '',
    staff_number: null,
    job_role: null,
    password: '',
    group_id: null,
    operating_site_id: null,
    supervisor_id: null,
    is_supervisor: false,
    home_office: false,
    home_office_hours_per_week: null, //TODO: check if we need active_since

    user_working_hours: [],
    user_leave_days: [],
    user_working_weeks: [],
    initialRemainingLeaveDays: 0,

    overtime_calculations_start: DateTime.now().toFormat('yyyy-MM-dd') as DateString,
    organizationUser: {
        organization_permission: null,
        operatingSite_permission: null,
        group_permission: null,
        absence_permission: null,
        absenceType_permission: null,
        timeAccount_permission: null,
        timeAccountSetting_permission: null,
        timeAccountTransaction_permission: null,
        user_permission: null,
        specialWorkingHoursFactor_permission: null,
        workLogPatch_permission: null,
        workLog_permission: null,
        ticket_permission: null,
    },
    groupUser: {
        absenceType_permission: null,
        group_permission: null,
        absence_permission: null,
        timeAccount_permission: null,
        timeAccountSetting_permission: null,
        timeAccountTransaction_permission: null,
        user_permission: null,
        workLogPatch_permission: null,
        workLog_permission: null,
        ticket_permission: null,
    },
    operatingSiteUser: {
        absenceType_permission: null,
        operatingSite_permission: null,
        absence_permission: null,
        timeAccount_permission: null,
        timeAccountSetting_permission: null,
        timeAccountTransaction_permission: null,
        user_permission: null,
        workLog_permission: null,
        workLogPatch_permission: null,
        ticket_permission: null,
    },
});

if (props.user) {
    userForm.first_name = props.user.first_name;
    userForm.last_name = props.user.last_name;
    userForm.email = props.user.email;
    userForm.date_of_birth = props.user.date_of_birth;
    userForm.city = props.user.current_address.city ?? '';
    userForm.zip = props.user.current_address.zip ?? '';
    userForm.street = props.user.current_address.street ?? '';
    userForm.house_number = props.user.current_address.house_number ?? '';
    userForm.address_suffix = props.user.current_address.address_suffix ?? '';
    userForm.country = props.user.current_address.country ?? ('' as Country);
    userForm.federal_state = props.user.current_address.federal_state ?? ('' as FederalState);
    userForm.phone_number = props.user.phone_number ?? '';
    userForm.staff_number = props.user.staff_number;
    userForm.job_role = props.user.job_role;
    userForm.password = props.user.password;
    userForm.group_id = props.user.group_id;
    userForm.operating_site_id = props.user.operating_site_id;
    userForm.supervisor_id = props.user.supervisor_id;
    userForm.home_office = props.user.home_office;
    userForm.home_office_hours_per_week = props.user.home_office_hours_per_week;
    userForm.overtime_calculations_start = props.user.overtime_calculations_start;

    userForm.user_leave_days = props.user.user_leave_days
        .filter(e => e !== null)
        .map(e => ({ ...e, active_since: DateTime.fromSQL(e.active_since).toFormat('yyyy-MM') }));
    if (userForm.user_leave_days.length == 0)
        userForm.user_leave_days.push({ id: null, active_since: DateTime.now().toFormat('yyyy-MM'), leave_days: 0 });

    userForm.user_working_hours = props.user.user_working_hours ?? [];
    if (userForm.user_working_hours.length == 0)
        userForm.user_working_hours.push({ id: null, active_since: DateTime.now().plus({ day: 1 }).toFormat('yyyy-MM-dd'), weekly_working_hours: 0 });

    for (const entry of props.user.user_working_weeks) {
        const weekdays = [] as Weekday[];
        for (const w of WEEKDAYS) if (entry[w]) weekdays.push(w);

        userForm.user_working_weeks.push({
            id: entry.id,
            active_since: entry.active_since,
            weekdays,
        });
    }
    if (userForm.user_working_weeks.length == 0)
        userForm.user_working_weeks.push({ id: null, active_since: DateTime.now().plus({ day: 1 }).toFormat('yyyy-MM-dd'), weekdays: [] });

    for (const key in userForm.organizationUser) {
        userForm.organizationUser[key as keyof typeof userForm.organizationUser] =
            props.user.organization_user[key as keyof typeof userForm.organizationUser];
    }
    for (const key in userForm.groupUser) {
        userForm.groupUser[key as keyof typeof userForm.groupUser] = (props.user.group_user ?? userForm.groupUser)[
            key as keyof typeof userForm.groupUser
        ];
    }
    for (const key in userForm.operatingSiteUser) {
        userForm.operatingSiteUser[key as keyof typeof userForm.operatingSiteUser] =
            props.user.operating_site_user[key as keyof typeof userForm.operatingSiteUser];
    }
}
function submit() {
    const form = userForm.transform(data => ({
        ...data,
        organizationUser: Object.fromEntries(Object.entries(data.organizationUser).filter(([k]) => props.permissions.find(p => p.name == k))),
        groupUser: Object.fromEntries(Object.entries(data.groupUser).filter(([k]) => props.permissions.find(p => p.name == k))),
        operatingSiteUser: Object.fromEntries(Object.entries(data.operatingSiteUser).filter(([k]) => props.permissions.find(p => p.name == k))),
    }));
    const onError = () => {
        nextTick(() => {
            const alerts = [...document.querySelectorAll('#userForm [role="alert"]')];
            const error = alerts.filter(e => e.children.length > 0)[0] as HTMLElement | undefined;

            if (error) {
                // offsetParent is the nearest positioned ancestor (in this case luckily the card in which the error is located)
                error.offsetParent?.scrollIntoView({
                    behavior: 'smooth',
                });
            }
        });
    };

    if (props.mode == 'edit' && props.user) form.patch(route('user.update', { user: props.user.id }), { onError });
    else
        form.post(route('user.store'), {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
            onError,
        });
}

function isLeaveDayDisabled(item: { id: UserLeaveDays['id'] | null; active_since: string }) {
    if (props.user && !can('user', 'update')) return true;
    const original = props.user?.user_leave_days.find(e => e?.id === item.id);
    if (original) return original.active_since < DateTime.now().startOf('month').toFormat('yyyy-MM-dd');
    return false;
}
</script>
<template>
    <v-form id="userForm" @submit.prevent="submit" :disabled="user && !can('user', 'update')">
        <v-card class="mb-4" title="Persönliche Daten">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.first_name" label="Vorname" :error-messages="userForm.errors.first_name"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.last_name" label="Nachname" :error-messages="userForm.errors.last_name"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.email" label="Email" :error-messages="userForm.errors.email"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6" v-if="mode == 'create'">
                        <v-text-field v-model="userForm.password" label="Passwort" :error-messages="userForm.errors.password"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            type="date"
                            v-model="userForm.date_of_birth"
                            label="Geburtsdatum (optional)"
                            :error-messages="userForm.errors.date_of_birth"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="userForm.staff_number"
                            label="Personalnummer (optional)"
                            :error-messages="userForm.errors.staff_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="userForm.job_role"
                            label="Stellenbezeichnung (optional)"
                            :error-messages="userForm.errors.job_role"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6" v-if="mode == 'create'"></v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.street" label="Straße (optional)" :error-messages="userForm.errors.street"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="userForm.house_number"
                            label="Hausnummer (optional)"
                            :error-messages="userForm.errors.house_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.zip" label="Postleitzahl (optional)" :error-messages="userForm.errors.zip"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.city" label="Ort (optional)" :error-messages="userForm.errors.city"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            data-testid="land"
                            label="Land"
                            :items="countries"
                            :error-messages="userForm.errors.country"
                            v-model="userForm.country"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            data-testid="federal_state"
                            label="Bundesland"
                            :items="getStates(userForm.country, countries)"
                            :disabled="(user && !can('user', 'update')) || !userForm.country"
                            :error-messages="userForm.errors.federal_state"
                            v-model="userForm.federal_state"
                        ></v-select>
                    </v-col>
                </v-row>
            </v-card-text>
            <template #append v-if="mode == 'create'">
                <v-btn icon variant="text" @click="emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
        </v-card>

        <HertaUserFormSections v-if="can('app', 'herta')" :mode :user v-model:user-form="userForm"></HertaUserFormSections>

        <v-card class="mb-4" title="Urlaubstage">
            <v-card-text>
                <v-row>
                    <v-col cols="12" v-if="userForm.errors.user_leave_days">
                        <v-alert type="error">{{ userForm.errors.user_leave_days }}</v-alert>
                    </v-col>
                    <v-col cols="12">
                        <v-data-table-virtual
                            :items="userForm.user_leave_days"
                            :headers="[
                                {
                                    title: 'Anzahl der Urlaubstage',
                                    key: 'leave_days',
                                    width: '50%',
                                    sortable: false,
                                },
                                {
                                    title: 'Aktiv seit',
                                    key: 'active_since',
                                    sortable: false,
                                },
                                {
                                    title: '',
                                    key: 'actions',
                                    align: 'end',
                                    sortable: false,
                                },
                            ]"
                        >
                            <template v-slot:header.actions>
                                <v-btn
                                    v-if="!user || can('user', 'update')"
                                    color="primary"
                                    @click.stop="userForm.user_leave_days.push({ active_since: '', id: null, leave_days: 0 })"
                                >
                                    <v-icon icon="mdi-plus"></v-icon>
                                </v-btn>
                            </template>
                            <template v-slot:item.leave_days="{ item, index }">
                                <v-text-field
                                    data-testid="userLeaveDays"
                                    type="number"
                                    variant="underlined"
                                    v-model="item.leave_days"
                                    :error-messages="userForm.errors[`user_leave_days.${index}.leave_days`]"
                                    :disabled="isLeaveDayDisabled(item)"
                                ></v-text-field>
                            </template>
                            <template v-slot:item.active_since="{ item, index }">
                                <v-text-field
                                    data-testid="userLeaveDays-since"
                                    type="month"
                                    :min="mode == 'edit' ? DateTime.now().startOf('month').toFormat('yyyy-MM') : undefined"
                                    variant="underlined"
                                    v-model="item.active_since"
                                    :disabled="isLeaveDayDisabled(item)"
                                    :error-messages="userForm.errors[`user_leave_days.${index}.active_since`]"
                                ></v-text-field>
                            </template>
                            <template v-slot:item.actions="{ item, index }">
                                <v-btn
                                    color="error"
                                    @click.stop="userForm.user_leave_days.splice(index, 1)"
                                    v-if="(!user || can('user', 'update')) && (!item.id || item.active_since > DateTime.now().toFormat('yyyy-MM-dd'))"
                                >
                                    <v-icon icon="mdi-delete"></v-icon>
                                </v-btn>
                            </template>
                        </v-data-table-virtual>
                    </v-col>
                    <v-col cols="12" v-if="mode == 'create'">
                        <v-text-field
                            type="number"
                            v-model="userForm.initialRemainingLeaveDays"
                            :label="`Trage die aus ${DateTime.now().year - 1} übernommenen Urlaubstage des Mitarbeitenden ein`"
                            :error-messages="userForm.errors.initialRemainingLeaveDays"
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <v-card class="mb-4" title="Homeoffice">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-checkbox
                            v-model="userForm.home_office"
                            label="Darf der Mitarbeitende Homeoffice machen?"
                            :error-messages="userForm.errors.home_office"
                            @update:model-value="
                                v => {
                                    if (!v) userForm.home_office_hours_per_week = null;
                                }
                            "
                        ></v-checkbox>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            type="number"
                            v-model="userForm.home_office_hours_per_week"
                            label="Homeoffice Stunden pro Woche (optional)"
                            :disabled="!userForm.home_office"
                            :error-messages="userForm.errors.home_office_hours_per_week"
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <v-card class="mb-4" title="Organisation">
            <v-card-text>
                <v-row>
                    <PermissionSelector
                        v-model="userForm.organizationUser"
                        objKey="organizationUser"
                        :permissions
                        :errors="userForm.errors"
                        label="Organisationsrechte"
                        :items="[
                            {
                                label: 'Zeiterfassung',
                                keys: [
                                    'workLog_permission',
                                    'workLogPatch_permission',
                                    'timeAccount_permission',
                                    'timeAccountSetting_permission',
                                    'timeAccountTransaction_permission',
                                ],
                            },
                            { label: 'Abwesenheiten', keys: ['absence_permission', 'absenceType_permission'] },
                            { label: 'Mitarbeiter', keys: ['user_permission'] },
                            { label: 'Organisation', keys: ['organization_permission'] },
                            { label: 'Ticket', keys: ['ticket_permission'] },
                        ]"
                    />
                </v-row>
            </v-card-text>
        </v-card>
        <v-card class="mb-4" title="Betriebsstätte">
            <v-card-text>
                <v-row>
                    <v-col cols="12" class="mb-4">
                        <v-select
                            v-model="userForm.operating_site_id"
                            :items="operating_sites.map(o => ({ title: o.name, value: o.id }))"
                            label="Wähle die Betriebsstätte des Mitarbeiters aus."
                            :error-messages="userForm.errors.operating_site_id"
                            data-testid="userOperatingSiteSelection"
                        ></v-select>
                    </v-col>
                    <PermissionSelector
                        v-model="userForm.operatingSiteUser"
                        objKey="operatingSiteUser"
                        :permissions
                        :errors="userForm.errors"
                        label="Wähle die Rechte des Mitarbeitenden für die ausgewählte Betriebsstätte aus"
                        data-testid="userOperatingSitePermissions"
                        :items="[
                            {
                                label: 'Zeiterfassung',
                                keys: [
                                    'workLog_permission',
                                    'workLogPatch_permission',
                                    'timeAccount_permission',
                                    'timeAccountSetting_permission',
                                    'timeAccountTransaction_permission',
                                ],
                            },
                            { label: 'Abwesenheiten', keys: ['absence_permission', 'absenceType_permission'] },
                            { label: 'Mitarbeiter', keys: ['user_permission'] },
                            { label: 'Betriebsstätte', keys: ['operatingSite_permission'] },
                            { label: 'Ticket', keys: ['ticket_permission'] },
                        ]"
                    />
                </v-row>
            </v-card-text>
        </v-card>
        <v-card class="mb-4" title="Abteilung">
            <v-card-text>
                <v-row>
                    <v-col cols="12" class="mb-4">
                        <v-select
                            v-model="userForm.group_id"
                            :items="groups.map(g => ({ title: g.name, value: g.id }))"
                            label="Wähle eine Abteilung aus, zu die der Mitarbeiter gehören soll. (optional)"
                            :error-messages="userForm.errors.group_id"
                            data-testid="userGroupSelection"
                        ></v-select>
                    </v-col>
                    <PermissionSelector
                        v-if="userForm?.group_id"
                        v-model="userForm.groupUser"
                        objKey="groupUser"
                        :permissions
                        :errors="userForm.errors"
                        label="Abteilungsrechte"
                        data-testid="userGroupPermissions"
                        :items="[
                            {
                                label: 'Zeiterfassung',
                                keys: [
                                    'workLog_permission',
                                    'workLogPatch_permission',
                                    'timeAccount_permission',
                                    'timeAccountSetting_permission',
                                    'timeAccountTransaction_permission',
                                ],
                            },
                            { label: 'Abwesenheiten', keys: ['absence_permission', 'absenceType_permission'] },
                            { label: 'Mitarbeiter', keys: ['user_permission'] },
                            { label: 'Abteilung', keys: ['group_permission'] },
                            { label: 'Ticket', keys: ['ticket_permission'] },
                        ]"
                    />
                </v-row>
            </v-card-text>
        </v-card>
        <v-card class="mb-4" title="Vorgesetzter">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="userForm.supervisor_id"
                            :items="supervisors.map(s => ({ title: s.first_name + ' ' + s.last_name, value: s.id }))"
                            label="Wähle einen Vorgesetzten, falls vorhanden (optional)"
                            :error-messages="userForm.errors.supervisor_id"
                            data-testid="userSupervisorSelection"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-checkbox v-model="userForm.is_supervisor" label="Ist ein Vorgesetzter"></v-checkbox>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <div class="d-flex justify-end">
            <v-btn type="submit" color="primary" :loading="userForm.processing">Speichern</v-btn>
        </div>
    </v-form>
</template>
<style scoped>
/* * we have so many fields, we want to condense it down a lil */
[class*='v-col-'] {
    padding-block: 4px;
}
</style>
