<script setup lang="ts">
import { Month, MonthStats, Year } from '@/types/types';
import { Info } from 'luxon';
import { ref, toRefs } from 'vue';

const props = defineProps<{
    stats: Record<Year, Partial<Record<Month, MonthStats>>>;
}>();
const { stats } = toRefs(props);

const currentYear = new Date().getFullYear().toString() as Year;
const currentMonth = (new Date().getMonth() + 1) as Month;

if (!(currentYear in stats.value)) {
    stats.value[currentYear] = {
        [currentMonth]: {
            year: currentYear,
            month: currentMonth,
            chat_cost: 0,
            chats: 0,
        },
    };
}

const expanded = ref<string[]>([currentYear]);
</script>
<template>
    <v-data-table-virtual
        v-model:expanded="expanded"
        fixed-header
        no-data-text="Keine Daten vorhanden"
        :headers="[
            { title: 'Zeitraum', key: 'year', width: '30%' },
            { title: 'Gesamte Chatkosten', key: 'chat_cost', width: '30%', align: 'center' },
            { title: 'Geführte Chats', key: 'chats', width: '30%', align: 'center' },
            { title: '', key: 'data-table-expand', width: '10%', sortable: false, align: 'end' },
        ]"
        :items="
            Object.entries(stats).map(([year, data]) => {
                const monthsData = Object.values(data);
                return {
                    year,
                    chat_cost: monthsData.reduce((sum, m) => sum + m.chat_cost, 0).toFixed(2) + '€',
                    chats: monthsData.reduce((sum, m) => sum + m.chats, 0),
                    monthsData,
                };
            })
        "
        item-value="year"
    >
        <template #header.data-table-expand />

        <template #expanded-row="{ columns, item }">
            <tr>
                <td :colspan="columns.length" class="pa-0">
                    <v-table density="comfortable" class="w-100">
                        <tbody>
                            <tr v-for="m in item.monthsData.toSorted((a, b) => a.month - b.month)" :key="m.month" class="bg-grey-lighten-4">
                                <td style="width: 30%; padding-left: 64px">{{ Info.months()[m.month - 1] }}</td>
                                <td style="width: 30%; text-align: center">{{ m.chat_cost.toFixed(2) }}€</td>
                                <td style="width: 30%; text-align: center">{{ m.chats }}</td>
                                <td style="width: 10%"></td>
                            </tr>
                        </tbody>
                    </v-table>
                </td>
            </tr>
        </template>
    </v-data-table-virtual>
</template>
