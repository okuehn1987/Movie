<script setup lang="ts">
import {
    Group,
    OperatingSite,
    User,
    UserWorkingWeek,
    UserWorkingHours,
    Weekday,
    CountryProp,
    Country,
    OrganizationUser,
    Permission,
    GroupUser,
    OperatingSiteUser,
    Writeable,
} from '@/types/types';
import { getMaxScrollHeight, getStates } from '@/utils';
import { useForm } from '@inertiajs/vue3';
import { DateTime, Info } from 'luxon';
import { ref } from 'vue';
import PermissionSelector from './UserFormPartials/PermissionSelector.vue';

const props = defineProps<{
    user?: User & {
        currentWorkingHours: UserWorkingHours;
        userWorkingWeek: UserWorkingWeek;
        organization_user: OrganizationUser;
        operating_site_user: OperatingSiteUser;
        group_user: GroupUser;
    };
    supervisors: Pick<User, 'id' | 'first_name' | 'last_name'>[];
    operating_sites: Pick<OperatingSite, 'id' | 'name'>[];
    groups: Pick<Group, 'id' | 'name'>[];
    mode: 'create' | 'edit';
    countries: CountryProp[];
    permissions: { name: Permission[keyof Permission]; label: string }[];
}>();

const emit = defineEmits<{
    success: [];
}>();

const userForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    date_of_birth: null as null | string,
    city: '',
    zip: '',
    street: '',
    house_number: '',
    address_suffix: '',
    country: '' as Country,
    federal_state: '',
    phone_number: '',
    staff_number: null as null | string,
    password: '',
    group_id: null as null | Group['id'],
    operating_site_id: null as null | OperatingSite['id'],
    supervisor_id: null as null | User['id'],
    is_supervisor: false,
    home_office: false,
    home_office_hours_per_week: null as number | null,
    userWorkingHours: 0,
    userWorkingHoursSince: new Date(),
    userWorkingWeek: [] as Weekday[],
    userWorkingWeekSince: new Date(),
    organizationUser: {
        absence_permission: null,
        absenceType_permission: null,
        group_permission: null,
        operatingSite_permission: null,
        organization_permission: null,
        timeAccount_permission: null,
        timeAccountSetting_permission: null,
        timeAccountTransaction_permission: null,
        user_permission: null,
        specialWorkingHoursFactor_permission: null,
        workLogPatch_permission: null,
    } as Pick<OrganizationUser, Permission[keyof Permission]>,
    groupUser: {
        absence_permission: null,
        group_permission: null,
        timeAccount_permission: null,
        timeAccountSetting_permission: null,
        timeAccountTransaction_permission: null,
        user_permission: null,
        workLogPatch_permission: null,
    } as Pick<GroupUser, Permission['all' | 'group']>,
    operatingSiteUser: {
        absence_permission: null,
        operatingSite_permission: null,
        timeAccount_permission: null,
        timeAccountSetting_permission: null,
        timeAccountTransaction_permission: null,
        user_permission: null,
        workLogPatch_permission: null,
    } as Pick<OperatingSiteUser, Permission['all' | 'operatingSite']>,
});

if (props.user) {
    userForm.first_name = props.user.first_name;
    userForm.last_name = props.user.last_name;
    userForm.email = props.user.email;
    userForm.date_of_birth = props.user.date_of_birth;
    userForm.city = props.user.city ?? '';
    userForm.zip = props.user.zip ?? '';
    userForm.street = props.user.street ?? '';
    userForm.house_number = props.user.house_number ?? '';
    userForm.address_suffix = props.user.address_suffix ?? '';
    userForm.country = props.user.country;
    userForm.federal_state = props.user.federal_state;
    userForm.phone_number = props.user.phone_number ?? '';
    userForm.staff_number = props.user.staff_number;
    userForm.password = props.user.password;
    userForm.group_id = props.user.group_id;
    userForm.operating_site_id = props.user.operating_site_id;
    userForm.supervisor_id = props.user.supervisor_id;
    userForm.home_office = props.user.home_office;
    userForm.home_office_hours_per_week = props.user.home_office_hours_per_week;
    userForm.userWorkingHours = props.user.currentWorkingHours?.weekly_working_hours ?? 0;
    userForm.userWorkingHoursSince = new Date(props.user.currentWorkingHours.active_since);
    for (const weekday of Info.weekdays('long', { locale: 'en' }).map(e => e.toLowerCase()) as Weekday[]) {
        if (props.user.userWorkingWeek[weekday]) userForm.userWorkingWeek.push(weekday);
    }
    userForm.userWorkingWeekSince = new Date(props.user.userWorkingWeek.active_since);
    userForm.organizationUser = props.user.organization_user;
    userForm.groupUser = props.user.group_user ?? userForm.groupUser;
    userForm.operatingSiteUser = props.user.operating_site_user;
}
function submit() {
    const form = userForm.transform(data => ({
        ...data,
        userWorkingHoursSince: DateTime.fromJSDate(userForm.userWorkingHoursSince).toFormat('yyyy-MM-dd'),
        userWorkingWeekSince: DateTime.fromJSDate(userForm.userWorkingWeekSince).toFormat('yyyy-MM-dd'),
        date_of_birth: data.date_of_birth ? new Date(data.date_of_birth).toISOString() : null,

        organizationUser: Object.fromEntries(Object.entries(data.organizationUser).filter(([k]) => props.permissions.find(p => p.name == k))),
        groupUser: Object.fromEntries(Object.entries(data.groupUser).filter(([k]) => props.permissions.find(p => p.name == k))),
        operatingSiteUser: Object.fromEntries(Object.entries(data.operatingSiteUser).filter(([k]) => props.permissions.find(p => p.name == k))),
    }));
    if (props.mode == 'edit' && props.user) form.patch(route('user.update', { user: props.user.id }), { onError: e => console.log(e) });
    else {
        form.post(route('user.store'), {
            onSuccess: () => {
                userForm.reset();
                emit('success');
            },
            onError: e => console.log(e),
        });
    }
}

const step = ref(1);

const steps = ref([
    {
        isValidated: false,
        name: 'Allgemeine Angaben',
        fields: {
            first_name: [() => !!userForm.first_name],
            last_name: [() => !!userForm.last_name],
            email: [() => !!userForm.email],
            date_of_birth: [() => true],
            password: [() => props.mode !== 'create' || !!userForm.password],
            userWorkingHours: [() => !!userForm.userWorkingHours],
            userWorkingHoursSince: [() => !!userForm.userWorkingHoursSince],
            userWorkingWeek: [() => !!userForm.userWorkingWeek],
            userWorkingWeekSince: [() => !!userForm.userWorkingWeekSince],
        },
    },
    {
        isValidated: false,
        name: 'Adresse',
        fields: {
            street: [() => true],
            house_number: [() => true],
            zip: [() => true],
            city: [() => true],
            federal_state: [() => !!userForm.federal_state],
            country: [() => !!userForm.country],
        },
    },
    {
        isValidated: false,
        name: 'Berechtigungen',
        fields: {
            operating_site_id: [() => !!userForm.operating_site_id],
        },
    },
] as const);
</script>
<template>
    <v-card>
        <v-stepper v-model="step">
            <v-stepper-header>
                <template v-for="(s, index) in steps" :key="index">
                    <v-stepper-item
                        v-bind="{
                            editable: mode == 'edit' || s.isValidated,
                            rules: s.isValidated ? Object.values(s.fields).flat() : [],
                            step: index,
                            title: s.name,
                            value: index + 1,
                        }"
                    ></v-stepper-item>
                    <v-divider v-if="index < steps.length" :key="index"></v-divider>
                </template>
            </v-stepper-header>
            <v-form @submit.prevent="submit">
                <v-stepper-window>
                    <v-stepper-window-item :value="1">
                        <v-card-text>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.first_name"
                                        label="Vorname"
                                        required
                                        :error-messages="userForm.errors.first_name"
                                        :rules="steps[0].fields.first_name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.last_name"
                                        label="Nachname"
                                        required
                                        :error-messages="userForm.errors.last_name"
                                        :rules="steps[0].fields.last_name"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.email"
                                        label="Email"
                                        required
                                        :error-messages="userForm.errors.email"
                                        :rules="steps[0].fields.email"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6" v-if="mode == 'create'">
                                    <v-text-field
                                        v-model="userForm.password"
                                        label="Passwort"
                                        required
                                        :error-messages="userForm.errors.password"
                                        :rules="steps[0].fields.password"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        type="date"
                                        v-model="userForm.date_of_birth"
                                        label="Geburtsdatum"
                                        :error-messages="userForm.errors.date_of_birth"
                                        :rules="steps[0].fields.date_of_birth"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.staff_number"
                                        label="Personalnummer"
                                        :error-messages="userForm.errors.staff_number"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <h4>Arbeitszeiten</h4>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        type="number"
                                        v-model="userForm.userWorkingHours"
                                        label="Trage die wöchentliche Arbeitszeit des Mitarbeiters ein"
                                        required
                                        :error-messages="userForm.errors.userWorkingHours"
                                        :rules="steps[0].fields.userWorkingHours"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-date-input
                                        prepend-icon=""
                                        v-model="userForm.userWorkingHoursSince"
                                        label="seit"
                                        required
                                        :error-messages="userForm.errors.userWorkingHoursSince"
                                        :rules="steps[0].fields.userWorkingHoursSince"
                                    ></v-date-input>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select
                                        chips
                                        v-model="userForm.userWorkingWeek"
                                        multiple
                                        :items="
                                            Info.weekdays().map((e, i) => ({
                                                title: e,
                                                value: Info.weekdays('long', { locale: 'en' })[i]?.toLowerCase(),
                                            }))
                                        "
                                        label="Wähle die Arbeitstage des Mitarbeiters aus"
                                        :error-messages="userForm.errors.userWorkingWeek"
                                        :rules="steps[0].fields.userWorkingWeek"
                                    />
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-date-input
                                        prepend-icon=""
                                        v-model="userForm.userWorkingWeekSince"
                                        label="seit"
                                        :error-messages="userForm.errors.userWorkingWeekSince"
                                        :rules="steps[0].fields.userWorkingWeekSince"
                                    ></v-date-input>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-checkbox
                                        v-model="userForm.home_office"
                                        label="Homeoffice"
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
                                        label="Homeoffice Stunden pro Woche"
                                        :disabled="!userForm.home_office"
                                        :error-messages="userForm.errors.home_office_hours_per_week"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-stepper-window-item>
                    <v-stepper-window-item :value="2">
                        <v-card-text>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.street"
                                        label="Straße"
                                        :error-messages="userForm.errors.street"
                                        :rules="steps[1].fields.street"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.house_number"
                                        label="Hausnummer"
                                        :error-messages="userForm.errors.house_number"
                                        :rules="steps[1].fields.house_number"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.zip"
                                        label="Postleitzahl"
                                        :error-messages="userForm.errors.zip"
                                        :rules="steps[1].fields.zip"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="userForm.city"
                                        label="Ort"
                                        :error-messages="userForm.errors.city"
                                        :rules="steps[1].fields.city"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select
                                        data-testid="land"
                                        label="Land"
                                        required
                                        :items="countries"
                                        :error-messages="userForm.errors.country"
                                        v-model="userForm.country"
                                        :rules="steps[1].fields.country"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select
                                        data-testid="federal_state"
                                        label="Bundesland"
                                        :items="getStates(userForm.country, countries)"
                                        :disabled="!userForm.country"
                                        required
                                        :error-messages="userForm.errors.federal_state"
                                        v-model="userForm.federal_state"
                                        :rules="steps[1].fields.federal_state"
                                    ></v-select>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-stepper-window-item>
                    <v-stepper-window-item :value="3">
                        <v-card-text :style="mode == 'create' ? { maxHeight: getMaxScrollHeight(72 + 24 * 2 + 52) } : {}" class="overflow-auto">
                            <v-row>
                                <v-col cols="12"><h4>Organisation</h4></v-col>
                                <PermissionSelector
                                    v-model="userForm.organizationUser"
                                    objKey="organizationUser"
                                    :permissions
                                    :errors="userForm.errors"
                                    label="Organisationsrechte"
                                ></PermissionSelector>
                                <v-col cols="12"><h4>Betriebsstätte</h4></v-col>
                                <v-col cols="12" md="6">
                                    <v-select
                                        v-model="userForm.operating_site_id"
                                        :items="operating_sites.map(o => ({ title: o.name, value: o.id }))"
                                        label="Wähle die Betriebsstätte des Mitarbeiters aus."
                                        :error-messages="userForm.errors.operating_site_id"
                                        :rules="steps[2].fields.operating_site_id"
                                        data-testid="userOperatingSiteSelection"
                                    ></v-select>
                                </v-col>
                                <PermissionSelector
                                    v-model="userForm.operatingSiteUser"
                                    objKey="operatingSiteUser"
                                    :permissions
                                    :errors="userForm.errors"
                                    label="Betriebstättenrechte"
                                    data-testid="userOperatingSitePermissions"
                                ></PermissionSelector>
                                <v-col cols="12"><h4>Abteilung</h4></v-col>
                                <v-col cols="12" md="6">
                                    <v-select
                                        v-model="userForm.group_id"
                                        :items="groups.map(g => ({ title: g.name, value: g.id }))"
                                        label="Wähle eine Abteilung aus, zu die der Mitarbeiter gehören soll."
                                        :error-messages="userForm.errors.group_id"
                                        data-testid="userGroupSelection"
                                    ></v-select>
                                </v-col>
                                <PermissionSelector
                                    v-if="user?.group_id"
                                    v-model="userForm.groupUser"
                                    objKey="groupUser"
                                    :permissions
                                    :errors="userForm.errors"
                                    label="Abteilungsrechte"
                                ></PermissionSelector>
                                <v-col cols="12"><h4>Vorgesetzter</h4></v-col>
                                <v-col cols="12" md="6">
                                    <v-select
                                        v-model="userForm.supervisor_id"
                                        :items="supervisors.map(s => ({ title: s.first_name + ' ' + s.last_name, value: s.id }))"
                                        label="Wähle einen Vorgesetzten, falls vorhanden"
                                        :error-messages="userForm.errors.supervisor_id"
                                        data-testid="userSupervisorSelection"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-checkbox v-model="userForm.is_supervisor" label="Ist ein Vorgesetzter"></v-checkbox>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-stepper-window-item>
                </v-stepper-window>
            </v-form>
            <template v-slot:actions="{ next, prev }">
                <v-stepper-actions :disabled="false" :class="`justify-${step !== 1 ? 'space-between' : 'end'}`">
                    <template v-slot:prev>
                        <v-btn color="primary" variant="elevated" @click.stop="prev" v-if="step !== 1">Zurück</v-btn>
                    </template>
                    <template v-slot:next>
                        <v-btn
                            color="secondary"
                            variant="elevated"
                            class="text-end"
                            @click.stop="
                                () => {
                                    const s = steps[step - 1] as Writeable<typeof steps[number]>;
                                    if (
                                        s &&
                                        Object.values(s.fields)
                                            .flat()
                                            .some(f => !f())
                                    )
                                        return

                                    if (s) (s.isValidated as boolean) = true;

                                    if (step == 3) submit();
                                    else next();
                                }
                            "
                            >{{ step == 3 ? 'Speichern' : 'Weiter' }}</v-btn
                        >
                    </template>
                </v-stepper-actions>
            </template>
        </v-stepper>
    </v-card>
</template>
<style scoped>
/** we have so many fields, we want to condense it down a lil */
.v-col-md-6,
.v-col-md-12 {
    padding-block: 4px;
}
</style>
