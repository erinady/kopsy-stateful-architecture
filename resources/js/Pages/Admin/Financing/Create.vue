<script setup>
import AdminLayout from '@/Layouts/Admin/Layout.vue';
import PageBreadcrumb from '@/Components/PageBreadcrumb.vue';
import { ref, watch, computed } from 'vue';
import BaseInputAdmin from '@/Components/Form/BaseInputAdmin.vue';
import { useUserValidation } from '@/Composables/Validation/useUserValidation';
import Button from '../../../Components/Form/Button.vue';
import UserIcon from '@/Icons/UserIcon.vue';
import axios from 'axios';

const searchQuery = ref('')
const memberResults = ref([])
const isLoadingSearch = ref(false)
const selectedMember = ref(null)

const form = ref({
    member_number: '',
    name: '',
    nik: '',
    gender: '',
    birthplace: '',
    birthdate: '',
    last_education: '',
    address: '',
    residential_address: '',
    email: '',
    phone_number: '',
    // step 2
    marital_status: '',
    spouse_name: '',
    dependents: '',
    income: '',
    income_amount: '',
    cost_amount: '',
    // step 3
    product_name: '',
    product_type: '',
    brand: '',
    color: '',
    condition: '',
    qty: '',
    description: '',
    down_payment: '',
    // step 4
    cost_price: '',
    margin: '',
    // step 5
    tenor: '',
    monthly_installment: '',
})

const activeStep = ref(1)
const totalSteps = 5
const isMemberSelected = ref(false)

const breadcrumbItems = [
    { name: 'Dashboard', link: '/admin' },
    { name: 'Pengelolaan Pembiayaan Murabahah', link: '/admin/financing/list' },
    { name: 'Permohonan Pembiayaan Murabahah' },
];

const props = defineProps({
    educations: Array,
    marriageStatuses: Array,
    incomes: Array,
    costs: Array,
})

// Search members saat ketik
watch(() => searchQuery.value, async (query) => {
    if (!query || query.length < 2) {
        memberResults.value = []
        return
    }

    isLoadingSearch.value = true
    try {
        const response = await axios.get('/admin/users/list', {
            params: { q: query }
        })
        memberResults.value = response.data
    } catch (error) {
        console.error('Error searching members:', error)
        memberResults.value = []
    } finally {
        isLoadingSearch.value = false
    }
})

// Pilih member dari hasil search
const selectMember = (member) => {
    selectedMember.value = member
    searchQuery.value = member.name

    // Auto-fill form dengan data member
    form.value.member_number = member.member_number
    form.value.name = member.name
    form.value.nik = member.nik
    form.value.email = member.email
    form.value.phone_number = member.phone_number
    form.value.gender = member.gender || ''
    form.value.birthplace = member.birth_place || ''
    form.value.birthdate = member.birth_date || ''
    form.value.last_education = member.last_education || ''
    form.value.address = member.address || ''
    form.value.residential_address = member.residential_address || ''
    form.value.marital_status = member.marital_status || ''
    form.value.spouse_name = member.spouse_name || ''
    form.value.dependents = member.dependents || ''

    memberResults.value = []
    isMemberSelected.value = true
}

// Filter results
const filteredMembers = computed(() => {
    return memberResults.value.filter(m =>
        m.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        m.member_number.includes(searchQuery.value)
    )
})

function nextStep() {
    activeStep.value++
}

function prevStep() {
    activeStep.value--
}

const { errors } = useUserValidation(form)
</script>

<template>
    <AdminLayout title="Permohonan Pembiayaan Murabahah">
        <PageBreadcrumb page-title="Permohonan Pembiayaan Murabahah" :items="breadcrumbItems" />
        <div class="grid grid-cols-6 gap-6">
            <div class="card-layout col-span-4 px-0!">
                <section v-if="activeStep === 1">
                    <div class="border-b border-gray-200 px-8 pb-4">
                        <h1 class="card-title">Identitas Pribadi</h1>
                    </div>
                    <div class="grid grid-cols-2 gap-6 p-4">
                        <!-- Member search input -->
                        <div class="col-span-2 relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cari Anggota <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari berdasarkan nama atau nomor anggota..."
                                :disabled="isMemberSelected"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                            />

                            <!-- Loading indicator -->
                            <div v-if="isLoadingSearch" class="absolute right-3 top-10">
                                <div class="animate-spin w-5 h-5 border-2 border-primary border-t-transparent rounded-full"></div>
                            </div>

                            <!-- Search results dropdown -->
                            <div v-if="filteredMembers.length > 0 && !isMemberSelected" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                                <div v-for="member in filteredMembers" :key="member.id"
                                    @click="selectMember(member)"
                                    class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b last:border-0">
                                    <div class="font-medium text-dark-text">{{ member.name }}</div>
                                    <div class="text-sm text-gray-500">{{ member.member_number }} • {{ member.email }}</div>
                                </div>
                            </div>

                            <!-- No results message -->
                            <div v-else-if="searchQuery && !isLoadingSearch && !isMemberSelected" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg p-4 text-center text-gray-500 z-10">
                                Anggota tidak ditemukan
                            </div>
                        </div>

                        <!-- Selected member info -->
                        <div v-if="isMemberSelected && selectedMember" class="col-span-2 bg-green-50 border border-green-200 rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-green-900">✓ Anggota terpilih:</p>
                                <p class="text-green-700 font-medium">{{ selectedMember.name }}</p>
                                <p class="text-sm text-green-600">{{ selectedMember.member_number }}</p>
                            </div>
                            <button
                                @click="() => { isMemberSelected = false; searchQuery = ''; selectedMember = null }"
                                class="text-green-600 hover:text-green-800 font-medium text-sm"
                            >
                                Ubah
                            </button>
                        </div>

                        <!-- Auto-filled fields (readonly) -->
                        <BaseInputAdmin label="Nomor Anggota" v-model="form.member_number" :readonly="true" />
                        <BaseInputAdmin label="Nama Lengkap" v-model="form.name" :readonly="true" />
                        <BaseInputAdmin label="NIK" v-model="form.nik" :readonly="true" />
                        <BaseInputAdmin label="Email" v-model="form.email" :readonly="true" />
                        <BaseInputAdmin label="Nomor Telepon" v-model="form.phone_number" :readonly="true" />

                        <!-- Editable fields -->
                        <BaseInputAdmin v-model="form.gender" label="Jenis Kelamin" type="radio" :selectables="[
                            { value: 'Laki-laki', text: 'Laki-laki' },
                            { value: 'Perempuan', text: 'Perempuan' }
                        ]" :error="errors.gender" />
                        <BaseInputAdmin label="Tempat Lahir" v-model="form.birthplace" :error="errors.birthplace" placeholder="Masukkan tempat lahir" />
                        <BaseInputAdmin label="Tanggal Lahir" type="date" v-model="form.birthdate" :error="errors.birthdate" />
                        <BaseInputAdmin label="Alamat Sesuai KTP" v-model="form.address" :error="errors.address" placeholder="Masukkan alamat sesuai KTP" />
                        <BaseInputAdmin label="Alamat Domisili" v-model="form.residential_address" :error="errors.residential_address" placeholder="Masukkan alamat domisili" />
                        <BaseInputAdmin v-model="form.last_education" label="Pendidikan Terakhir" type="select"
                            :selectables="educations.map(unit => ({ value: unit, text: unit }))"
                            :error="errors.last_education" />
                    </div>
                </section>
                <section v-if="activeStep === 2">
                    <div class="border-b border-gray-200 px-8 pb-4">
                        <h1 class="card-title">Status & Tanggungan Keluarga</h1>
                    </div>

                    <div class="flex flex-col">
                        <div class="border-b border-gray-200 grid grid-cols-2 gap-4 p-4">
                            <BaseInputAdmin v-model="form.marital_status" label="Status Perkawinan" type="select"
                                :selectables="marriageStatuses.map(unit => ({ value: unit, text: unit }))"
                                :error="errors.marital_status" />
                            <BaseInputAdmin label="Nama Pasangan" v-model="form.spouse_name" />
                            <BaseInputAdmin label="Jumlah Tanggungan Keluarga" v-model="form.dependents"
                                type="number" />
                        </div>
                        <div class="grid grid-cols-5 gap-4 items-end p-4 border-b border-gray-200">
                            <div class="col-span-2">
                                <BaseInputAdmin label="Data Penghasilan" v-model="form.income" type="select"
                                    :selectables="incomes.map(unit => ({ value: unit, text: unit }))" />
                            </div>
                            <div class="col-span-3 flex gap-4">
                                <BaseInputAdmin v-model="form.income_amount" required />
                                <Button size="small" variant="primary">Tambah</Button>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-4 items-end p-4 border-b border-gray-200">
                            <div class="col-span-2">
                                <BaseInputAdmin label="Data Pengeluaran" v-model="form.income" type="select"
                                    :selectables="incomes.map(unit => ({ value: unit, text: unit }))" />
                            </div>
                            <div class="col-span-3 flex gap-4">
                                <BaseInputAdmin v-model="form.income_amount" required />
                                <Button size="small" variant="primary">Tambah</Button>
                            </div>
                        </div>
                    </div>
                </section>
                <section v-if="activeStep === 3">
                    <div class="border-b border-gray-200 px-8 pb-4">
                        <h1 class="card-title">Objek Pembiayaan</h1>
                    </div>
                    <div class="grid grid-cols-2 gap-4 p-4">
                        <BaseInputAdmin label="Nama Produk" v-model="form.product_name" />
                    </div>
                </section>
                <section v-if="activeStep === 4">
                    <div class="border-b border-gray-200 px-8 pb-4">
                        <h1 class="card-title">Informasi Pemasok</h1>
                    </div>
                </section>
                <section v-if="activeStep === 5">
                    <div class="border-b border-gray-200 px-8 pb-4">
                        <h1 class="card-title">Hasil Verifikasi dan Persetujuan</h1>
                    </div>
                </section>
                <div class="flex justify-end gap-4 p-4">
                    <Button v-if="activeStep > 1" @click="prevStep" variant="light">
                        Kembali
                    </Button>
                    <Button v-if="activeStep < totalSteps" @click="nextStep" variant="primary" :disabled="activeStep === 1 && !isMemberSelected">
                        Selanjutnya
                    </Button>
                </div>
            </div>
            <div class="card-layout col-span-2 flex flex-col h-fit gap-4">
                <h1 class="card-title">Tahapan</h1>
                <div class="flex items-center gap-4">
                    <div :class="activeStep === 1 ? 'bg-primary text-white' : 'bg-white border border-stroke'"
                        class="rounded-full flex items-center justify-center h-11 w-11">
                        <UserIcon class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="font-medium text-dark-text">Identitas Pribadi</h1>
                        <p class="text-sm text-gray-400 font-body">Isi identitas pemohon</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div :class="activeStep === 2 ? 'bg-primary text-white' : 'bg-white border border-stroke'"
                        class="rounded-full flex items-center justify-center h-11 w-11">
                        <UserIcon class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="font-medium text-dark-text">Data Tanggungan dan Penghasilan</h1>
                        <p class="text-sm text-gray-400 font-body">Validasi kemampuan pemohon membayar angsuran</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div :class="activeStep === 3 ? 'bg-primary text-white' : 'bg-white border border-stroke'"
                        class="rounded-full flex items-center justify-center h-11 w-11">
                        <UserIcon class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="font-medium text-dark-text">Objek Pembiayaan</h1>
                        <p class="text-sm text-gray-400 font-body">Isi detail objek pembiayaan</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div :class="activeStep === 4 ? 'bg-primary text-white' : 'bg-white border border-stroke'"
                        class="rounded-full flex items-center justify-center h-11 w-11">
                        <UserIcon class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="font-medium text-dark-text">Pengadaan Barang</h1>
                        <p class="text-sm text-gray-400 font-body">Isi dan unggah bukti pembelian barang</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div :class="activeStep === 5 ? 'bg-primary text-white' : 'bg-white border border-stroke'"
                        class="rounded-full flex items-center justify-center h-11 w-11">
                        <UserIcon class="w-5 h-5" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="font-medium text-dark-text">Finalisasi Pembiayaan Murabahah</h1>
                        <p class="text-sm text-gray-400 font-body">Isi rincian angsuran dan unggah dokumen akad</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
