<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { User, WorkLog, WorkLogPatch, Paginator } from '@/types/types';
import { useNow } from '@/utils';
import { router, usePage } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id'> & {
    work_log: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

const props = defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    patches: Paginator<PatchProp[]> | null;
}>();
const page = usePage();
const now = useNow();

const patchDialog = ref<PatchProp | null>(null);
const showPatchDialog = ref(false);
const submitPatchSuccess = ref(false);

const currentPage = ref(props.patches?.current_page);

// TODO: momentanen überstunden ohne aktuellen Log
// TODO: bei abwesenheit buttons disablen

function changeWorkStatus(home_office = false) {
    router.post(route('workLog.store'), {
        is_home_office: home_office,
        id: props.lastWorkLog.end ? null : props.lastWorkLog.id,
    });
}

function openPatch(_: any, row: { item: { id: WorkLogPatch['id'] } }) {
    const patch = props.patches?.data.find(p => p.id === row.item.id);
    if (!patch) return;
    patchDialog.value = patch;
    showPatchDialog.value = true;
}

function changePatchStatus(accepted: boolean) {
    router.patch(
        route('workLogPatch.update', { workLogPatch: patchDialog.value?.id }),
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
    <AdminLayout title="Dashboard">
        <v-container>
            <v-row>
                <v-col cols="12" md="4">
                    <v-card>
                        <v-toolbar color="primary">
                            <v-toolbar-title>Arbeitszeit</v-toolbar-title>
                            <v-btn
                                :href="
                                    route('user.workLog.index', {
                                        user: page.props.auth.user.id,
                                    })
                                "
                            >
                                <v-icon icon="mdi-eye"></v-icon>
                            </v-btn>
                        </v-toolbar>
                        <v-card-text>
                            <div>
                                <div>
                                    Aktuelle Stunden:
                                    {{ now.diff(DateTime.fromSQL(lastWorkLog.start)).toFormat('hh:mm') }}
                                </div>
                                <div>
                                    {{ DateTime.fromSQL(lastWorkLog.start).toFormat('dd.MM HH:mm') }}
                                </div>
                            </div>

                            <v-alert color="danger" class="mt-2" v-if="now.diff(DateTime.fromSQL(lastWorkLog.start)).as('hours') > 16">
                                Es fehlt eine Meldung. Bitte Zeitkorrektur.
                                <v-btn color="primary" class="ms-2" :href="`/user/${page.props.auth.user.id}/workLogs?workLog=${lastWorkLog.id}`">
                                    Zeitkorrektur
                                </v-btn>
                            </v-alert>
                            <div v-else>
                                <template
                                    v-if="(page.props.auth.user.home_office && lastWorkLog.end) || (lastWorkLog.is_home_office && !lastWorkLog.end)"
                                >
                                    <v-btn @click.stop="changeWorkStatus(true)" color="primary" class="me-2">
                                        {{ lastWorkLog.end ? 'Kommen Homeoffice' : 'Gehen Homeoffice' }}
                                    </v-btn>
                                </template>
                                <v-btn @click.stop="changeWorkStatus()" color="primary" v-if="lastWorkLog.end || !lastWorkLog.is_home_office">
                                    {{ lastWorkLog.end ? 'Kommen' : 'Gehen' }}
                                </v-btn>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4" v-if="supervisor">
                    <v-card>
                        <v-toolbar color="primary" title="Vorgesetzter"></v-toolbar>
                        <v-card-text>
                            {{ supervisor.first_name }}
                            {{ supervisor.last_name }}
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4">
                    <v-card>
                        <v-toolbar color="primary" title="Informationen"></v-toolbar>
                        <v-card-text>text</v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" md="4" v-if="page.props.auth.user.work_log_patching">
                    <v-card>
                        <v-toolbar color="primary" title="Zeitkorrekturen"></v-toolbar>
                        <v-card-text>
                            <div>
                                <v-alert v-if="submitPatchSuccess" color="success" closable class="mb-4">
                                    Die Zeitkorrektur wurde erfolgreich angepasst.
                                </v-alert>
                            </div>
                            <div v-if="patches == null || patches.data.length < 1">keine Zeitkorrekturen vorhanden.</div>
                            <div v-else>
                                <v-data-table
                                    hover
                                    v-model:page="patches.current_page"
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
                            </div>
                        </v-card-text>
                        <v-dialog v-if="patchDialog" v-model="showPatchDialog" max-width="1000">
                            <v-card>
                                <v-toolbar color="primary" :title="patchDialog.user.first_name + ' ' + patchDialog.user.last_name"></v-toolbar>
                                <v-card-text>
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
                                                end: DateTime.fromSQL(p.end).toFormat('dd.MM.yyyy HH:mm'),
                                                is_home_office: p.is_home_office ? 'Ja' : 'Nein',
                                            }))
                                        "
                                    ></v-data-table-virtual>
                                    <div class="d-flex justify-space-between mt-4">
                                        <v-btn color="primary" @click="showPatchDialog = false">Abbrechen</v-btn>
                                        <div>
                                            <v-btn color="error me-2" @click.stop="changePatchStatus(false)">Ablehnen</v-btn>
                                            <v-btn color="primary" @click.stop="changePatchStatus(true)">Akzeptieren</v-btn>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AdminLayout>
</template>
