<script setup lang="ts">
import { User } from '@/types/types';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import { HomeOfficeDayProp } from './disputeTypes';

type HomeOfficeDayDeleteProp = HomeOfficeDayProp & {
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

const props = defineProps<{
    requestedDeletes: HomeOfficeDayDeleteProp[];
}>();

const homeOfficeToDelete = ref<HomeOfficeDayDeleteProp | null>(
    props.requestedDeletes?.find(h => h.id == Number(route().params['openHomeOfficeDayDelete'])) ?? null,
);
const showHomeOfficeDialog = ref(!!homeOfficeToDelete.value);

const currentPage = ref(1);

function openHomeOfficePatch(item: HomeOfficeDayDeleteProp) {
    const homeOfficePatch = props.requestedDeletes?.find(p => p.id === item.id);
    if (!homeOfficePatch) return;
    homeOfficeToDelete.value = homeOfficePatch;
    showHomeOfficeDialog.value = true;
}

const deleteHomeOfficeForm = useForm({});
function deleteHomeOffice() {
    if (!homeOfficeToDelete.value) return;
    deleteHomeOfficeForm.delete(route('homeOfficeDay.destroy', { homeOfficeDay: homeOfficeToDelete.value.id }), {
        onSuccess: () => {
            showHomeOfficeDialog.value = false;
        },
    });
}

const denyDeleteHomeOfficeForm = useForm({});
function denyHomeOfficeDelete() {
    if (!homeOfficeToDelete.value) return;
    denyDeleteHomeOfficeForm.delete(route('homeOfficeDay.denyDestroy', { homeOfficeDay: homeOfficeToDelete.value.id }), {
        onSuccess: () => {
            showHomeOfficeDialog.value = false;
        },
    });
}
</script>
<template>
    <v-data-table
        hover
        items-per-page="5"
        v-model:page="currentPage"
        no-data-text="keine Abwesenheitsanträge vorhanden."
        @click:row="(_:unknown,row:Record<'item',HomeOfficeDayDeleteProp>) => openHomeOfficePatch(row.item)"
        :items="
            requestedDeletes.map(h => ({
                id: h.id,
                user: h.user.first_name + ' ' + h.user.last_name,
                start: DateTime.fromSQL(h.date).toFormat('dd.MM.yyyy'),
                end: DateTime.fromSQL(h.date).toFormat('dd.MM.yyyy'),
            }))
        "
        :headers="[
            { title: 'Mitarbeiter', key: 'user' },
            { title: 'Von', key: 'start' },
            { title: 'Bis', key: 'end' },
        ]"
    >
        <template v-slot:bottom>
            <v-pagination v-if="requestedDeletes.length > 5" v-model="currentPage" :length="Math.ceil(requestedDeletes.length / 5)"></v-pagination>
        </template>
    </v-data-table>

    <v-dialog v-if="homeOfficeToDelete" v-model="showHomeOfficeDialog" max-width="1000">
        <v-card :title="'Abwesenheit von ' + homeOfficeToDelete.user.first_name + ' ' + homeOfficeToDelete.user.last_name">
            <template #append>
                <v-btn icon variant="text" @click="showHomeOfficeDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-divider></v-divider>
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-text-field readonly>Homeoffice</v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Von:"
                            :model-value="DateTime.fromSQL(homeOfficeToDelete.date).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Bis:"
                            :model-value="DateTime.fromSQL(homeOfficeToDelete.date).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-col cols="12" class="d-flex justify-end ga-2">
                <v-btn color="primary" @click.stop="denyHomeOfficeDelete()" :loading="denyDeleteHomeOfficeForm.processing">Löschen ablehnen</v-btn>
                <v-btn color="error" @click.stop="deleteHomeOffice()" :loading="deleteHomeOfficeForm.processing">Abwesenheit Löschen</v-btn>
            </v-col>
        </v-card>
    </v-dialog>
</template>
