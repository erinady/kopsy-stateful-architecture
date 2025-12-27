<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/Admin/Layout.vue'
import { Icon } from '@iconify/vue'

// Ringkasan (statis)
const summary = [
    {
        title: 'Jumlah Anggota Aktif',
        value: 1213,
        change: '+2.5%',
        trend: 'up',
        note: 'dari bulan lalu',
    },
    {
        title: 'Anggota Baru Bulan Ini',
        value: 12,
        change: '-2.5%',
        trend: 'down',
        note: 'dari bulan lalu',
    },
    {
        title: 'Menunggu Verifikasi',
        value: 10,
        link: 'Selengkapnya',
    },
]

// Data anggota (statis)
const members = Array.from({ length: 10 }, (_, i) => ({
    id: i + 1,
    no_anggota: 'SW15120004',
    name: 'Diana Larasati',
    joined_at: '12/9/2025',
    phone: '08936482876',
    total_simpanan: 'Penyetoran',
    status: 'Aktif',
    avatar: 'https://i.pravatar.cc/40?img=5',
}))
</script>

<template>
    <AdminLayout>
        <div class="font-body flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <!-- Title -->
            <div>
                <h1 class="font-heading text-2xl font-bold text-blue-900 dark:text-white">
                    Pengelolaan Data Anggota
                </h1>
            </div>

            <!-- Breadcrumb -->
            <div class="mt-2 sm:mt-0">
                <div class="flex items-center text-sm text-gray-500">
                    <Link href="/admin/dashboard" class="hover:text-blue-600">
                        Dashboard
                    </Link>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-blue-600 font-medium">Anggota</span>
                </div>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 mb-6">
            <h2 class="font-semibold mb-4 dark:text-gray-100">Ringkasan</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    v-for="item in summary"
                    :key="item.title"
                    class="border rounded-xl p-5 dark:text-gray-100"
                >
                    <div class="text-2xl font-bold">
                        {{ item.value }}
                    </div>

                    <div class="font-heading text-sm text-gray-500 mt-1">
                        {{ item.title }}
                    </div>

                    <div v-if="item.change" class="mt-2 text-xs flex items-center gap-2">
                        <span
                            :class="item.trend === 'up'
                                ? 'text-green-600 bg-green-100'
                                : 'text-red-600 bg-red-100'"
                            class="px-2 py-0.5 rounded-full"
                        >
                            {{ item.change }}
                        </span>
                        <span class="text-gray-400">{{ item.note }}</span>
                    </div>

                    <div v-if="item.link" class="mt-3 text-sm text-blue-600 cursor-pointer">
                        {{ item.link }} →
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
            <!-- Card Header -->
            <div class="flex justify-between items-center p-6 border-b">
                <div>
                    <h2 class="font-heading text-lg font-semibold text-gray-900 dark:text-gray-100">Data Anggota</h2>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 dark:text-gray-100">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Tampilkan</span>
                    <select class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                    <span class="text-sm text-gray-500">data per halaman</span>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-b">
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="pl-10 pr-4 py-2 border rounded-lg text-sm w-64"
                        />
                        <Icon
                            icon="mdi:magnify"
                            class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                        />
                    </div>

                    <button class="flex items-center gap-2 border rounded-lg px-4 py-2 text-sm">
                        <Icon icon="mdi:filter-variant" class="w-4 h-4" />
                        Filter
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700 dark:text-gray-100">
                        <tr>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">No</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">No Anggota</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">Profil Anggota</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">Tanggal Bergabung</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">Kontak</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">Total Simpanan</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">Status</th>
                            <th class="font-heading px-6 py-3 text-left text-sm font-medium">Aksi</th>
                        </tr>
                    </thead>

                    <tbody
                        class="font-body bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                        <tr
                            v-for="(member, index) in members" 
                            :key="member.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 text-sm dark:text-gray-300">
                                {{ index + 1 }}
                            </td>

                            <td class="px-6 py-4 text-sm dark:text-gray-300">
                                {{ member.no_anggota }}
                            </td>

                            <td class="px-6 py-4 flex items-center gap-3">
                                <img
                                    :src="member.avatar"
                                    class="w-9 h-9 rounded-full"
                                />
                                <span class="font-body text-sm text-gray-800 dark:text-gray-300">
                                    {{ member.name }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm dark:text-gray-300">
                                {{ member.joined_at }}
                            </td>

                            <td class="font-body text-sm px-6 py-4 flex items-center gap-2 dark:text-gray-300">
                                <Icon icon="mdi:whatsapp" class="text-green-500" />
                                {{ member.phone }}
                            </td>

                            <td class="px-6 py-4 text-sm dark:text-gray-300">
                                {{ member.total_simpanan }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-4 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                    {{ member.status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 dark:text-gray-300">
                                <div class="flex justify-center gap-3">
                                    <!-- Edit -->
                                    <button
                                        class="text-gray-500 hover:text-blue-600 transition"
                                        title="Edit"
                                    >
                                        <Icon icon="mdi:pencil-outline" class="w-5 h-5" />
                                    </button>

                                    <!-- View -->
                                    <button
                                        class="text-gray-500 hover:text-blue-600 transition"
                                        title="Detail"
                                    >
                                        <Icon icon="mdi:eye-outline" class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="p-6 flex justify-center items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    Sebelumnya
                </button>
                <span class="px-3 py-1 bg-blue-600 text-white rounded">
                    1
                </span>
                <span>2</span>
                <span>3</span>
                <button class="px-4 py-2 border rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    Berikutnya
                </button>
            </div>
        </div>
    </AdminLayout>
</template>