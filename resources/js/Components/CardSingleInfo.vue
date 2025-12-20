<template>
    <div class="flex flex-col px-8 py-6 bg-light-bg rounded-2xl border border-gray-200 dark:bg-gray-800 dark:border-gray-700 gap-4">
        <div class="flex justify-between items-center">
            <div class="flex flex-col gap-2">
                <h1 class="text-xl font-semibold text-dark-text dark:text-white/90">{{ title }}</h1>
                <p class="text-gray-500">No. Transaksi #{{ subTitle }}</p>
            </div>
            <button
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-dark-text shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                See all
            </button>
        </div>
        <div class="card-info bg-white dark:bg-gray-800 px-8 py-6 rounded-xl border border-stroke dark:border-gray-700 flex flex-col gap-2">
            <div class="flex flex-col">
                <!-- TODO: makeit 2 column -->
                <div v-for="d in data" :key="data.key" class="">
                    <div class="flex flex-col">
                        <p class="">{{ d.label }}</p>
                        <h1 class="font-semibold font-body text-xl">{{ d.content }}</h1>
                    </div>
                </div>
            </div>
    </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    subTitle: {
        type: String,
        required: false,
    },
    data: {
        type: Array,
        required: true,
    },
})
</script>
