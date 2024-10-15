<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Group, OperatingSite, User, UserPermission } from '@/types/types';
import { useForm } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import UserForm from './UserForm.vue';

const props = defineProps<{
    user: User;
    groups: Group[];
    operating_sites: OperatingSite[];
    permissions: UserPermission[];
}>();

const userForm = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    date_of_birth: props.user.date_of_birth,
    city: props.user.city,
    zip: props.user.zip,
    street: props.user.street,
    house_number: props.user.house_number,
    address_suffix: props.user.address_suffix,
    country: props.user.country,
    federal_state: props.user.federal_state,
    phone_number: props.user.phone_number,
    staff_number: props.user.staff_number,
    group_id: props.user.group_id,
    operating_site_id: props.user.operating_site_id,
});
console.log(userForm.date_of_birth);

function submit() {
    userForm
        .transform(data => ({
            ...data,
            date_of_birth: DateTime.fromISO(new Date(data.date_of_birth + '').toISOString()),
        }))
        .patch(route('user.update', { user: props.user.id }), {
            onSuccess: () => userForm.reset(),
        });
}
</script>
<template>
    <AdminLayout title="User Show">
        <v-container>
            <v-card>
                <UserForm :userForm :groups :operating_sites :submit :permissions mode="edit"></UserForm>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
