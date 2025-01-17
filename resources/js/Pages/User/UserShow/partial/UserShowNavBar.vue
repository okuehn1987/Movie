<script setup lang="ts">
import { User } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
const props = defineProps<{
    user: Pick<User, 'id'>;
    tab: string;
}>();

const tab = ref(props.tab || 'generalInformation');
watch(tab, () => router.get(route('user.' + tab.value, tab.value === 'profile' ? {} : { user: props.user.id })));
</script>
<template>
    <v-tabs v-model="tab">
        <v-tab v-if="can('user', 'viewShow')" value="generalInformation">Allgemeine Informationen</v-tab>
        <v-tab v-if="can('absences', 'viewIndex')" value="absences">Abwesenheiten</v-tab>
        <v-tab v-if="can('timeAccount', 'viewIndex')" value="timeAccounts">Arbeitszeitkonten</v-tab>
        <v-tab v-if="can('timeAccountTransaction', 'viewIndex')" value="timeAccountTransactions">Transaktionen</v-tab>
        <v-tab v-if="can('user', 'viewIndex')" value="userOrganigram">Organigramm</v-tab>
        <v-tab v-if="user.id == $page.props.auth.user.id" value="profile">Profil</v-tab>
    </v-tabs>
</template>
<style lang="scss" scoped></style>
