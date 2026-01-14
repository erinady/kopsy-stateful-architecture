<script setup lang="ts">
import { computed } from 'vue'

interface SavingData {
    simpanan_pokok: number
    simpanan_wajib: number
    simpanan_sukarela: number
}

const props = withDefaults(defineProps<{
    savings?: SavingData
}>(), {
    savings: () => ({
        simpanan_pokok: 0,
        simpanan_wajib: 0,
        simpanan_sukarela: 0,
    }),
})

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value)
}

const totalSimpanan = computed(() => {
    return (props.savings?.simpanan_pokok ?? 0) + (props.savings?.simpanan_wajib ?? 0) + (props.savings?.simpanan_sukarela ?? 0)
})

const savingItems = [
    {
        label: 'Total Simpanan Pokok',
        key: 'simpanan_pokok',
        color: 'blue',
        icon: 'mdi--bank',
    },
    {
        label: 'Total Simpanan Wajib',
        key: 'simpanan_wajib',
        color: 'green',
        icon: 'mdi--check-circle',
    },
    {
        label: 'Total Simpanan Sukarela',
        key: 'simpanan_sukarela',
        color: 'purple',
        icon: 'mdi--heart',
    },
]
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
        <div
            v-for="item in savingItems"
            :key="item.key"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border-l-4"
            :class="{
                'border-l-blue-600': item.color === 'blue',
                'border-l-green-600': item.color === 'green',
                'border-l-purple-600': item.color === 'purple',
            }"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium font-body text-gray-600 dark:text-gray-400">
                    {{ item.label }}
                </h3>
                <div
                    class="p-2 rounded-lg"
                    :class="{
                        'bg-blue-100 dark:bg-blue-900': item.color === 'blue',
                        'bg-green-100 dark:bg-green-900': item.color === 'green',
                        'bg-purple-100 dark:bg-purple-900': item.color === 'purple',
                    }"
                >
                    <span
                        :class="[`icon-[${item.icon}]`, {
                            'text-blue-600 dark:text-blue-300': item.color === 'blue',
                            'text-green-600 dark:text-green-300': item.color === 'green',
                            'text-purple-600 dark:text-purple-300': item.color === 'purple',
                        }]"
                        style="font-size: 1.5rem"
                    />
                </div>
            </div>
            <p class="text-2xl font-bold font-head text-gray-900 dark:text-white">
                {{ formatCurrency(props.savings?.[item.key as keyof SavingData] ?? 0) }}
            </p>
            <p class="text-xs text-gray-500 font-body dark:text-gray-500 mt-2">
                Saldo simpanan Anda
            </p>
        </div>
    </div>
</template>
