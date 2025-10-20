<script setup lang="ts">
import { User } from '@/types/types';
import { DateTime } from 'luxon';
import { ref } from 'vue';

const props = defineProps<{
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
}>();

const showCreationDialog = ref(false);

const newWorkLogForm = useForm({
    start: new Date(),
    end: new Date(),
    comment: null as null | string,
    start_time: '09:00',
    end_time: '17:00',
    is_home_office: false,
});

function submit() {
    newWorkLogForm
        .transform(d => {
            const start_time = DateTime.fromFormat(d.start_time, 'HH:mm');
            const end_time = DateTime.fromFormat(d.end_time, 'HH:mm');
            return {
                ...d,
                start: DateTime.fromISO(d.start.toISOString()).set({
                    hour: start_time.hour,
                    minute: start_time.minute,
                    second: 0,
                }),
                end: DateTime.fromISO(d.end.toISOString()).set({
                    hour: end_time.hour,
                    minute: end_time.minute,
                    second: 0,
                }),
            };
        })
        .post(route('user.workLog.store', { user: props.user.id }), {
            onSuccess: () => {
                showCreationDialog.value = false;
                newWorkLogForm.reset();
            },
        });
}
</script>

<template>
    <v-dialog max-width="1000" v-model="showCreationDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="primary"><v-icon>mdi-plus</v-icon></v-btn>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="'Neue Buchung ' + (can('workLogPatch', 'update') ? 'erstellen' : 'beantragen')">
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click="
                            isActive.value = false;
                            newWorkLogForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form @submit.prevent="submit">
                        <v-row>
                            <v-col cols="12" md="3">
                                <v-date-input
                                    label="Start"
                                    required
                                    :error-messages="newWorkLogForm.errors.start"
                                    v-model="newWorkLogForm.start"
                                    variant="plain"
                                    style="height: 73px"
                                ></v-date-input>
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-text-field
                                    type="time"
                                    label="Start"
                                    required
                                    :error-messages="newWorkLogForm.errors.start_time"
                                    v-model="newWorkLogForm.start_time"
                                    variant="plain"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-date-input
                                    label="Ende"
                                    required
                                    :error-messages="newWorkLogForm.errors.end"
                                    v-model="newWorkLogForm.end"
                                    variant="plain"
                                    style="height: 73px"
                                ></v-date-input>
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-text-field
                                    type="time"
                                    label="Ende"
                                    required
                                    :error-messages="newWorkLogForm.errors.end_time"
                                    v-model="newWorkLogForm.end_time"
                                    variant="plain"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    label="Bemerkung (optional)"
                                    v-model="newWorkLogForm.comment"
                                    :error-messages="newWorkLogForm.errors.comment"
                                    variant="filled"
                                    rows="3"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-checkbox
                                    label="Homeoffice"
                                    required
                                    :error-messages="newWorkLogForm.errors.is_home_office"
                                    v-model="newWorkLogForm.is_home_office"
                                    variant="plain"
                                    hide-details
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn
                                    v-if="can('workLogPatch', 'create') || can('workLogPatch', 'update')"
                                    :loading="newWorkLogForm.processing"
                                    type="submit"
                                    color="primary"
                                >
                                    {{ can('workLogPatch', 'update') ? 'Speichern' : 'Beantragen' }}
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
