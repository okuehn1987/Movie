<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Group, User } from "@/types/types";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps<{
    groups: (Group & { users: User[] })[];
    users: Pick<User, "id">[];
}>();

const groupForm = useForm({
    name: "",
    users: [],
});

const expanded = ref([]);

function submit() {
    groupForm.post(route("group.store"), {
        onSuccess: () => groupForm.reset(),
    });
}
</script>
<template>
    <AdminLayout title="Abteilungen">
        <v-container>
            <v-data-table-virtual
                :headers="[
                    { title: '#', key: 'id' },
                    { title: 'Abteilungsname', key: 'name' },
                    { title: '', key: 'data-table-expand' },
                    { title: '', key: 'action', align: 'end' },
                ]"
                :items="groups"
                v-model:expanded="expanded"
                hover
            >
                <template v-slot:header.action>
                    <v-dialog max-width="1000">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" color="primary">
                                <v-icon icon="mdi-plus"></v-icon>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card>
                                <v-form @submit.prevent="submit">
                                    <v-toolbar
                                        color="primary"
                                        class="mb-4"
                                        title="Abteilung erstellen"
                                    ></v-toolbar>
                                    <v-row>
                                        <v-col cols="12">
                                            <v-text-field
                                                v-model="groupForm.name"
                                                label="Abteilungsname"
                                                class="px-8"
                                                variant="underlined"
                                            ></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-select
                                                v-model="groupForm.users"
                                                class="px-8"
                                                :items="users"
                                                item-title="name"
                                                item-value="id"
                                                label="Wähle Mitarbeiter aus, die zur Abteilung gehören"
                                                multiple
                                                variant="underlined"
                                            ></v-select>
                                        </v-col>
                                    </v-row>
                                    <v-card-actions>
                                        <div class="d-flex justify-end w-100">
                                            <v-btn
                                                color="error"
                                                variant="elevated"
                                                class="me-2"
                                                @click="isActive.value = false"
                                            >
                                                Abbrechen
                                            </v-btn>
                                            <v-btn
                                                type="submit"
                                                color="primary"
                                                variant="elevated"
                                                >Erstellen
                                            </v-btn>
                                        </div>
                                    </v-card-actions>
                                </v-form>
                            </v-card>
                        </template>
                    </v-dialog>
                </template>
                <template v-slot:expanded-row="{ columns, item }">
                    <v-data-table-virtual
                        hover
                        :headers="[
                            { title: '#', key: 'id' },
                            { title: 'Vorname', key: 'first_name' },
                            { title: 'Nachname', key: 'last_name' },
                            { title: 'Email', key: 'email' },
                            {
                                title: 'Personalnummer',
                                key: 'staff_number',
                            },
                            { title: 'Geburtsdatum', key: 'date_of_birth' },
                        ]"
                        :items="item.users"
                    >
                    </v-data-table-virtual>
                </template>
            </v-data-table-virtual> </v-container
    ></AdminLayout>
</template>
