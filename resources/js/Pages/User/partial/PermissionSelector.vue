<script setup lang="ts" generic="T extends Partial<Record<Permission[keyof Permission], PermissionValue>>">
import { Permission, PermissionValue } from '@/types/types';

defineProps<{
    permissions: { name: Permission[keyof Partial<Permission>]; label: string }[];
    label: string;
    errors: Record<string, string | undefined>;
    objKey: string;
    items: { label: string; keys: (keyof T)[] }[];
}>();

const permissionObj = defineModel<T>({ required: true });
</script>
<template>
    <v-col cols="12">
        <v-expansion-panels multiple elevation="1" variant="accordion">
            <v-expansion-panel v-for="item in items.filter(i => i.keys.length > 1)" :key="item.label">
                <v-expansion-panel-title expand-icon="mdi-chevron-double-down" collapse-icon="mdi-chevron-double-up">
                    <v-row @click.stop="() => {}">
                        <v-col cols="12" md="6">
                            <v-select
                                :items="[
                                    { title: 'Keine Rechte', value: null },
                                    { title: 'Lesen', value: 'read' },
                                    { title: 'Schreiben', value: 'write' },
                                ]"
                                :label="item.label"
                                @update:model-value="
                                    val => {
                                        item.keys.forEach(key => {
                                            permissionObj[key] = val;
                                        });
                                    }
                                "
                                variant="outlined"
                            ></v-select>
                        </v-col>
                    </v-row>
                </v-expansion-panel-title>

                <v-expansion-panel-text>
                    <v-row>
                        <template v-for="(_, key) in permissionObj" :key="key">
                            <v-col cols="12" sm="6" md="3" v-if="item.keys.includes(key as Permission[keyof Permission])">
                                <v-select
                                    :label="permissions.find(p => p.name == key)?.label"
                                    :items="[
                                        { title: 'Keine Rechte', value: null },
                                        { title: 'Lesen', value: 'read' },
                                        { title: 'Schreiben', value: 'write' },
                                    ]"
                                    v-model="permissionObj[key]"
                                    :error-messages="
                                        Object.entries(errors)
                                            .filter(([k, v]) => k.includes(key as string) && k.includes(objKey) && v)
                                            .map(([, v]) => v) as string[]
                                                                    "
                                    variant="outlined"
                                ></v-select>
                            </v-col>
                        </template>
                    </v-row>
                </v-expansion-panel-text>
            </v-expansion-panel>
        </v-expansion-panels>
        <v-row v-if="items.some(i => i.keys.length == 1)" class="mt-4">
            <v-col cols="12" md="6" v-for="item in items.filter(i => i.keys.length == 1)" :key="item.label">
                <v-select
                    :label="permissions.find(p => p.name == item.keys[0])?.label"
                    :items="[
                        { title: 'Keine Rechte', value: null },
                        { title: 'Lesen', value: 'read' },
                        { title: 'Schreiben', value: 'write' },
                    ]"
                    v-model="permissionObj[item.keys[0] as Permission[keyof Permission]]"
                    :error-messages="
                                        Object.entries(errors)
                                            .filter(([k, v]) => k.includes(item.keys[0] as Permission[keyof Permission]) && k.includes(objKey) && v)
                                            .map(([, v]) => v) as string[]
                                    "
                    variant="outlined"
                ></v-select>
            </v-col>
        </v-row>
    </v-col>
</template>
<style lang="scss" scoped></style>
