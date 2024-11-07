<script setup lang="ts">
import { Paginator, User, WorkLog, WorkLogPatch } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref, watch } from 'vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id'> & {
    work_log: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

const props = defineProps<{
    patches: Paginator<PatchProp> | null;
}>();

const patchDialog = ref<PatchProp | null>(null);
const showPatchDialog = ref(false);
const submitPatchSuccess = ref(false);

const currentPage = ref(props.patches?.current_page);

watch(currentPage, () => {
    router.visit(
        route(route('dashboard'), {
            page: currentPage.value,
        }),
        {
            only: ['patches'],
        },
    );
});

// TODO: momentanen überstunden ohne aktuellen Log
// TODO: bei abwesenheit buttons disablen

function openPatch(_: unknown, row: { item: { id: WorkLogPatch['id'] } }) {
    const patch = props.patches?.data.find(p => p.id === row.item.id);
    if (!patch) return;
    patchDialog.value = patch;
    showPatchDialog.value = true;
}

function changePatchStatus(accepted: boolean) {
    if (!patchDialog.value) return;
    router.patch(
        route('workLogPatch.update', { workLogPatch: patchDialog.value.id }),
        { accepted: accepted },
        {
            onSuccess: () => {
                showPatchDialog.value = false;
                submitPatchSuccess.value = true;
            },
        },
    );
}
</script>
<template>
    <v-card title="Zeitkorrekturen">
        <v-card-text v-if="patches == null || patches.data.length < 1"> keine Zeitkorrekturen vorhanden. </v-card-text>
        <v-data-table
            v-else
            hover
            v-model:page="currentPage"
            :items-per-page="patches.per_page"
            item-value="id"
            @click:row="openPatch"
            :items="
                patches.data.map(patch => ({
                    id: patch.id,
                    user: patch.user.first_name + ' ' + patch.user.last_name,
                    date: DateTime.fromSQL(patch.start).toFormat('dd.MM.yyyy'),
                }))
            "
            :headers="[
                { title: 'Mitarbeiter', key: 'user' },
                { title: 'Datum', key: 'date' },
            ]"
        >
            <template v-slot:bottom>
                <v-pagination v-if="patches.last_page > 1" v-model="currentPage" :length="patches.last_page"></v-pagination>
            </template>
        </v-data-table>

        <v-dialog v-if="patchDialog" v-model="showPatchDialog" max-width="1000">
            <v-card :title="patchDialog.user.first_name + ' ' + patchDialog.user.last_name">
                <template #append>
                    <v-btn icon variant="text" @click="showPatchDialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-data-table-virtual
                    :headers="[
                        { title: '', key: 'version', sortable: false },
                        { title: 'Start', key: 'start', sortable: false },
                        { title: 'Ende', key: 'end', sortable: false },
                        { title: 'Homeoffice', key: 'is_home_office', sortable: false },
                    ]"
                    :items="
                        [patchDialog.work_log, patchDialog].map((p, i) => ({
                            version: i == 0 ? 'Alter Stand:' : 'Neuer Stand:',
                            start: DateTime.fromSQL(p.start).toFormat('dd.MM.yyyy HH:mm'),
                            end: p.end ? DateTime.fromSQL(p.end).toFormat('dd.MM.yyyy HH:mm') : 'kein Ende',
                            is_home_office: p.is_home_office ? 'Ja' : 'Nein',
                        }))
                    "
                ></v-data-table-virtual>

                <v-col cols="12" class="d-flex justify-end ga-3">
                    <v-btn color="error" @click.stop="changePatchStatus(false)">Ablehnen</v-btn>
                    <v-btn color="success" @click.stop="changePatchStatus(true)">Akzeptieren</v-btn>
                </v-col>
            </v-card>
        </v-dialog>
    </v-card>
</template>
