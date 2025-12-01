<script setup lang="ts">
import { RelationPick, WorkLogPatch } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id' | 'comment'> &
    RelationPick<'workLogPatch', 'log', 'id' | 'start' | 'end' | 'is_home_office'> &
    RelationPick<'workLogPatch', 'user', 'id' | 'first_name' | 'last_name'>;

const props = defineProps<{
    patches: PatchProp[];
}>();

const patchDialog = ref<PatchProp | null>(props.patches?.find(patch => patch.id == Number(route().params['openPatch'])) ?? null);
const showPatchDialog = ref(!!patchDialog.value);
const submitPatchSuccess = ref(false);

const currentPage = ref(1);

// TODO: momentanen überstunden ohne aktuellen Log
// TODO: bei abwesenheit buttons disablen

function openPatch(_: unknown, row: { item: { id: WorkLogPatch['id'] } }) {
    const patch = props.patches?.find(p => p.id === row.item.id);
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
    <v-data-table
        hover
        items-per-page="5"
        v-model:page="currentPage"
        no-data-text="keine Zeitkorrekturen vorhanden."
        @click:row="openPatch"
        :items="
            patches.map(patch => ({
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
            <v-pagination v-if="patches.length > 5" v-model="currentPage" :length="Math.ceil(patches.length / 5)"></v-pagination>
        </template>
    </v-data-table>

    <v-dialog v-if="patchDialog" v-model="showPatchDialog" max-width="1000">
        <v-card :title="'Zeitkorrektur von ' + patchDialog.user.first_name + ' ' + patchDialog.user.last_name">
            <template #append>
                <v-btn icon variant="text" @click="showPatchDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-divider></v-divider>
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-data-table-virtual
                            :headers="[
                                { title: '', key: 'version', sortable: false },
                                { title: 'Start', key: 'start', sortable: false },
                                { title: 'Ende', key: 'end', sortable: false },
                                { title: 'Homeoffice', key: 'is_home_office', sortable: false },
                            ]"
                            :items="
                                [patchDialog.log, patchDialog].map((p, i) => ({
                                    version: i == 0 ? 'Alter Stand:' : 'Neuer Stand:',
                                    start: DateTime.fromSQL(p.start).toFormat('dd.MM.yyyy HH:mm'),
                                    end: p.end ? DateTime.fromSQL(p.end).toFormat('dd.MM.yyyy HH:mm') : 'kein Ende',
                                    is_home_office: p.is_home_office ? 'Ja' : 'Nein',
                                }))
                            "
                        ></v-data-table-virtual>
                    </v-col>
                    <v-col cols="12">
                        <v-textarea v-model="patchDialog.comment" readonly label="Bemerkung" variant="filled" max-rows="10" auto-grow></v-textarea>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-col cols="12" class="d-flex justify-end ga-3">
                <v-btn color="error" @click.stop="changePatchStatus(false)">Ablehnen</v-btn>
                <v-btn color="success" @click.stop="changePatchStatus(true)">Akzeptieren</v-btn>
            </v-col>
        </v-card>
    </v-dialog>
</template>
