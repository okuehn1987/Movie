<script setup lang="ts">
const bugForm = useForm({
    location: null as string | null,
    summary: null as string | null,
    description: null as string | null,
    attachments: [] as File[],
});
</script>
<template>
    <v-dialog max-width="1000">
        <template #activator="{ props: activatorProps }">
            <div v-bind="activatorProps">
                <v-icon icon="mdi-bug" class="me-2"></v-icon>
                Fehler melden
            </div>
        </template>
        <template #default="{ isActive }">
            <v-card title="Fehler melden">
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click="
                            isActive.value = false;
                            bugForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            bugForm.post(route('reportBug.store'), {
                                onSuccess: () => {
                                    isActive.value = false;
                                    bugForm.reset();
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="bugForm.location"
                                    :errorMessages="bugForm.errors.location"
                                    label="Wo ist der Fehler aufgetreten?"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="bugForm.summary"
                                    :errorMessages="bugForm.errors.summary"
                                    label="Fasse den Fehler zusammen"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    max-rows="16"
                                    auto-grow
                                    v-model="bugForm.description"
                                    :errorMessages="bugForm.errors.description"
                                    label="Zusätzliche Informationen zum Fehler"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12">
                                <v-file-input
                                    v-model="bugForm.attachments"
                                    :errorMessages="
                                     Object.entries(bugForm.errors)
                                     .filter(([k, v]) => k.includes('attachments') && v)
                            .map(([_]) => 'Alle Dateien müssen ein Bild sein.') as string[]
                                    "
                                    label="Anhang"
                                    multiple
                                    accept="image/*"
                                ></v-file-input>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn type="submit" color="primary">Absenden</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
