<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface BreadcrumbProps {
    pageTitle: string,
    items?: Array<{ name: string, link: string }>,
}

defineProps<BreadcrumbProps>()

const goBack = () => {
    window.history.back()
}
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="flex gap-2 items-center">
            <button @click="goBack" class="text-dark-text dark:text-gray-400">
                <span class="icon-[tabler--chevron-left]" style="width: 24px; height: 24px;"></span>
            </button>
            <h2 class="text-xl font-semibold text-brand-900 dark:text-white/90">
                {{ pageTitle }}
            </h2>
        </div>
        <nav>
            <ol class="flex items-center gap-1.5">
            <li v-for="(item, index) in items" :key="index" class="flex items-center">
                <Link v-if="item.link" :href="item.link" class="text-gray-500 font-medium hover:text-secondary">{{ item.name }}</Link>
                <span v-else class="text-primary font-medium">{{ item.name }}</span>
                <span v-if="index < (items?.length ?? 0) - 1" class="mx-2 text-gray-400">
                    <span class="icon-[tabler--chevron-right]" style="width: 16px; height: 16px;"></span>
                </span>
            </li>
            </ol>
        </nav>
    </div>
</template>
