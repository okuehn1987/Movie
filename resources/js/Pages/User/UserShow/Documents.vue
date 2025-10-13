<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import { User } from '@/types/types';
import { DateTime } from 'luxon';

const props = defineProps<{
    user: User;
}>();

const timestatementForm = useForm({
    start: DateTime.max(DateTime.now().minus({ month: 1 }), DateTime.fromISO(props.user.created_at)).toFormat('yyyy-MM'),
    end: DateTime.max(DateTime.now().minus({ month: 1 }), DateTime.fromISO(props.user.created_at)).toFormat('yyyy-MM'),
});
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="documents" />
        <v-card title="Zeitnachweise">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="5">
                        <v-text-field
                            type="month"
                            v-model="timestatementForm.start"
                            label="Von"
                            :min="DateTime.fromISO(user.created_at).toFormat('yyyy-MM')"
                            :max="DateTime.now().toFormat('yyyy-MM')"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="5">
                        <v-text-field
                            type="month"
                            v-model="timestatementForm.end"
                            label="Bis"
                            :min="DateTime.fromISO(user.created_at).toFormat('yyyy-MM')"
                            :max="DateTime.now().toFormat('yyyy-MM')"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-btn
                            color="primary"
                            target="_blank"
                            :href="route('user.timeStatementDoc', { user: user.id, start: timestatementForm.start, end: timestatementForm.end })"
                        >
                            Herunterladen
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </AdminLayout>
</template>
