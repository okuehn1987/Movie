<script setup lang="ts">
import { Group, OperatingSite, User, UserPermission, UserWorkingWeek, UserWorkingHours, Weekday } from '@/types/types';
import { useForm } from '@inertiajs/vue3';
import { DateTime, Info } from 'luxon';

const props = defineProps<{
    user?: User & { currentWorkingHours: UserWorkingHours; userWorkingWeek: UserWorkingWeek };
    supervisors: Pick<User, 'id' | 'first_name' | 'last_name'>[];
    operating_sites: Pick<OperatingSite, 'id' | 'name'>[];
    permissions: UserPermission[];
    groups: Pick<Group, 'id' | 'name'>[];
    mode: 'create' | 'edit';
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
    country: '',
    federal_state: '',
    phone_number: '',
    staff_number: 0 as null | number,
    password: '',
    group_id: null as null | Group['id'],
    operating_site_id: null as null | OperatingSite['id'],
    supervisor_id: null as null | User['id'],
    is_supervisor: false,
    userWorkingHours: 0,
    userWorkingHoursSince: new Date(),
    userWorkingWeek: [] as Weekday[],
    userWorkingWeekSince: new Date(),
    permissions: [] as UserPermission['name'][],
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
    userForm.country = props.user.country ?? '';
    userForm.federal_state = props.user.federal_state ?? '';
    userForm.phone_number = props.user.phone_number ?? '';
    userForm.staff_number = props.user.staff_number;
    userForm.password = props.user.password;
    userForm.group_id = props.user.group_id;
    userForm.operating_site_id = props.user.operating_site_id;
    userForm.supervisor_id = props.user.supervisor_id;
    userForm.userWorkingHours = props.user.currentWorkingHours?.weekly_working_hours ?? 0;
    userForm.userWorkingHoursSince = new Date(props.user.currentWorkingHours.active_since);
    for (const weekday of Info.weekdays('long', { locale: 'en' }).map(e => e.toLowerCase()) as Weekday[]) {
        if (props.user.userWorkingWeek[weekday]) userForm.userWorkingWeek.push(weekday);
    }
    userForm.userWorkingWeekSince = new Date(props.user.userWorkingWeek.active_since);
    for (const permission of props.permissions) {
        if (props.user[permission.name]) userForm.permissions.push(permission.name);
    }
}

function submit() {
    const form = userForm.transform(data => ({
        ...data,
        userWorkingHoursSince: DateTime.fromJSDate(userForm.userWorkingHoursSince).toFormat('yyyy-MM-dd'),
        userWorkingWeekSince: DateTime.fromJSDate(userForm.userWorkingWeekSince).toFormat('yyyy-MM-dd'),
        date_of_birth: data.date_of_birth ? new Date(data.date_of_birth).toISOString() : null,
    }));
    if (props.mode == 'edit' && props.user) form.patch(route('user.update', { user: props.user.id }), {});
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
</script>
<template>
    <v-card :title="mode === 'create' ? 'Mitarbeiter hinzufügen' : props.user?.first_name + ' ' + props.user?.last_name">
        <template #append>
            <slot name="append"></slot>
        </template>
        <v-divider></v-divider>
        <v-card-text>
            <v-form @submit.prevent="submit">
                <v-row>
                    <v-col cols="12"><h3>Allgemeine Informationen</h3></v-col>

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
                            label="Geburtsdatum"
                            required
                            :error-messages="userForm.errors.date_of_birth"
                        ></v-text-field>
                    </v-col>

                    <v-col cols="12"><h3>Adresse</h3></v-col>

                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.street" label="Straße" :error-messages="userForm.errors.street"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="userForm.house_number"
                            label="Hausnummer"
                            :error-messages="userForm.errors.house_number"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.zip" label="Postleitzahl" :error-messages="userForm.errors.zip"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="userForm.city" label="Ort" :error-messages="userForm.errors.city"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            required
                            v-model="userForm.federal_state"
                            label="Bundesland"
                            :error-messages="userForm.errors.federal_state"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field required v-model="userForm.country" label="Land" :error-messages="userForm.errors.country"></v-text-field>
                    </v-col>

                    <v-col cols="12"><h3>Struktur</h3></v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="userForm.operating_site_id"
                            :items="operating_sites.map(o => ({ title: o.name, value: o.id }))"
                            label="Wähle die Betriebsstätte des Mitarbeiters aus."
                            :error-messages="userForm.errors.operating_site_id"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="userForm.group_id"
                            :items="groups.map(g => ({ title: g.name, value: g.id }))"
                            label="Wähle eine Abteilung aus, zu die der Mitarbeiter gehören soll."
                            :error-messages="userForm.errors.group_id"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="userForm.supervisor_id"
                            :items="supervisors.map(s => ({ title: s.first_name + ' ' + s.last_name, value: s.id }))"
                            label="Wähle einen Vorgesetzten"
                            :error-messages="userForm.errors.supervisor_id"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-checkbox v-model="userForm.is_supervisor" label="Ist ein Vorgesetzter"></v-checkbox>
                    </v-col>
                    <v-col cols="12"><h3>Wöchentliche Arbeitszeit</h3></v-col>

                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="userForm.userWorkingHours"
                            label="Trage die wöchentliche Arbeitszeit des Mitarbeiters ein"
                            :error-messages="userForm.errors.userWorkingHours"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-date-input
                            prepend-icon=""
                            v-model="userForm.userWorkingHoursSince"
                            label="seit"
                            :error-messages="userForm.errors.userWorkingHoursSince"
                        ></v-date-input>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="userForm.userWorkingWeek"
                            multiple
                            :items="Info.weekdays().map((e, i) => ({ title: e, value: Info.weekdays('long', { locale: 'en' })[i]?.toLowerCase() }))"
                            label="Wähle die Arbeitstage des Mitarbeiters aus"
                            :error-messages="userForm.errors.userWorkingWeek"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-date-input
                            prepend-icon=""
                            v-model="userForm.userWorkingWeekSince"
                            label="seit"
                            :error-messages="userForm.errors.userWorkingWeekSince"
                        ></v-date-input>
                    </v-col>

                    <v-col cols="12"><h3>Berechtigungen</h3></v-col>

                    <v-col cols="12" md="6" v-for="permission in permissions" :key="permission.name">
                        <v-checkbox v-model="userForm.permissions" :value="permission.name" :label="permission.label" hide-details></v-checkbox>
                    </v-col>

                    <v-col cols="12" class="text-end">
                        <v-btn type="submit" color="primary" :loading="userForm.processing">Speichern</v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-card-text>
    </v-card>
</template>
<style scoped>
/** we have so many fields, we want to condense it down a lil */
.v-col-md-6,
.v-col-md-12 {
    padding-block: 4px;
}
</style>
