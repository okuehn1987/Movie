<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import { User } from '@/types/types';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    user: User;
    users: Pick<User, 'id' | 'first_name' | 'last_name' | 'job_role'>[];
    substitute_ids: User['id'][];
}>();

const notificationForm = useForm({
    mail_notifications: (props.user.notification_channels ?? []).includes('mail'),
    app_notifications: (props.user.notification_channels ?? []).includes('database'),
});

const substituteForm = useForm({
    substitute_ids: props.substitute_ids,
});
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="profile" />
        <v-row>
            <v-col cols="12" md="6">
                <v-row>
                    <v-col cols="12">
                        <v-card>
                            <v-card-title>Benachrichtigungen</v-card-title>
                            <v-divider></v-divider>
                            <v-card-text>
                                <v-form @submit.prevent="notificationForm.post(route('profile.updateSettings'))">
                                    <v-row>
                                        <v-col cols="12">
                                            <p>Wählen Sie die gewünschten Benachrichtigungsarten.</p>
                                        </v-col>
                                        <v-col>
                                            <v-checkbox label="E-Mail Benachrichtigungen" v-model="notificationForm.mail_notifications"></v-checkbox>
                                        </v-col>
                                        <v-col>
                                            <v-checkbox label="App Benachrichtigungen" v-model="notificationForm.app_notifications"></v-checkbox>
                                        </v-col>
                                    </v-row>
                                    <div class="d-flex justify-end"><v-btn color="primary" type="submit">Speichern</v-btn></div>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="12">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </v-col>
                </v-row>
            </v-col>
            <v-col cols="12" md="6">
                <v-row>
                    <v-col cols="12">
                        <v-card>
                            <v-card-title>Vertretungen</v-card-title>
                            <v-card-text>
                                <v-form @submit.prevent="substituteForm.post(route('substitute.update'))">
                                    <v-row>
                                        <v-col cols="12">
                                            <v-autocomplete
                                                autocomplete="off"
                                                multiple
                                                v-model="substituteForm.substitute_ids"
                                                :error-messages="substituteForm.errors.substitute_ids"
                                                chips
                                                :items="
                                                    users.map(u => ({
                                                        title: `${u.first_name} ${u.last_name}`,
                                                        value: u.id,
                                                        props: { subtitle: u.job_role ?? '' },
                                                    }))
                                                "
                                                label="Vertretungen wählen"
                                            ></v-autocomplete>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-alert color="warning">
                                                Vertretungen erhalten all Ihre Befugnisse und Benachrichtigungen solange sie als Vertretung aufgeführt
                                                sind
                                            </v-alert>
                                        </v-col>
                                    </v-row>
                                    <div class="d-flex justify-end mt-4"><v-btn color="primary" type="submit">Speichern</v-btn></div>
                                </v-form>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="12">
                        <UpdatePasswordForm />
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </AdminLayout>
</template>
