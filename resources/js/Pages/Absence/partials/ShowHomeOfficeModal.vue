<script setup lang="ts">
import { User } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { HomeOfficeDayProp } from '../utils';

const emit = defineEmits<{
    homeOfficeReload: [];
}>();
const props = defineProps<{
    users: Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'>[];
    selectedHomeOffice: HomeOfficeDayProp;
}>();

const selectedUser = defineModel<User['id']>('selectedUser', { required: true });
const openModal = defineModel<boolean>({ required: true });

function openDispute() {
    router.get(
        route('dispute.index', {
            openHomeOffice: props.selectedHomeOffice.home_office_day_generator_id,
        }),
    );
}
function withdrawRequest() {
    const options = {
        onSuccess: () => {
            openModal.value = false;
            emit('homeOfficeReload');
        },
    };

    router.delete(
        route('homeOfficeDayGenerator.destroy', { homeOfficeDayGenerator: props.selectedHomeOffice.home_office_day_generator_id }),
        options,
    );
}
</script>
<template>
    <v-dialog max-width="1000" v-model="openModal">
        <template #default="{ isActive }">
            <v-card
                :title="
                    'Abwesenheit' +
                    (selectedUser != $page.props.auth.user.id
                        ? ' von ' + users.map(u => ({ ...u, name: u.first_name + ' ' + u.last_name })).find(u => u.id === selectedUser)?.name
                        : '')
                "
            >
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form readonly>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field>Homeoffice</v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    type="date"
                                    label="Von"
                                    :model-value="selectedHomeOffice.home_office_day_generator.start"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field type="date" label="Bis" :model-value="selectedHomeOffice.home_office_day_generator.end"></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn v-if="can('user', 'viewDisputes')" @click.stop="openDispute" type="button" color="primary">
                                    Antrag öffnen
                                </v-btn>
                                <v-btn v-else-if="can('absence', 'deleteDispute')" @click.stop="withdrawRequest" type="button" color="primary">
                                    Antrag zurückziehen
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
