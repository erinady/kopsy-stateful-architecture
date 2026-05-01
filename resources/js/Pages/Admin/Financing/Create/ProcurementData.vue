<script setup>
import BaseInputAdmin from '@/Components/Form/BaseInputAdmin.vue'
import Info from '@/Components/Form/Info.vue'
import { watch, computed } from 'vue'
import parseCurrencyAmount from '@/Composables/moneyParser.js'

const props = defineProps({
    form: Object,
    searchSupplierQuery: String,
    isLoadingSearchSupplier: Boolean,
    isSupplierSelected: Boolean,
    filteredSuppliers: Array,
})

watch(() => props.form.financing.cost_price, () => {
    const costPrice = parseFloat(props.form.financing.cost_price) || 0
    const marginAmount = costPrice * 0.08
    props.form.financing.margin_amount = marginAmount
}, { immediate: true })

const totalPrice = computed(() => {
    const costPrice = parseFloat(props.form.financing.cost_price) || 0
    const marginAmount = parseFloat(props.form.financing.margin_amount) || 0
    const downPayment = parseFloat(props.form.financing.down_payment) || 0

    return (costPrice + marginAmount - downPayment) || 0
})
</script>

<template>
    <section>
        <div class="card-layout mx-4">
            <h1 class="card-title">Informasi Pemasok</h1>
            <div class="grid grid-cols-2 gap-4 pt-4">
                <div class="col-span-1 relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Supplier <span class="text-red-500">*</span>
                    </label>

                    <div v-if="!isSupplierSelected" class="flex gap-2">
                        <input :value="searchSupplierQuery" @input="$emit('update:searchSupplierQuery', $event.target.value)"
                            type="text" placeholder="Cari supplier..."
                            class="flex-1 px-4 font-body text-sm py-2.5 border border-gray-300 rounded-lg focus:border-brand-300 focus:ring-brand-500/10 focus:ring-3 shadow-theme-xs focus:outline-hidden" />

                        <!-- Loading indicator -->
                        <div v-if="isLoadingSearchSupplier" class="absolute right-5 top-10">
                            <div class="animate-spin w-5 h-5 border-2 border-primary border-t-transparent rounded-full">
                            </div>
                        </div>
                    </div>

                    <!-- Selected member display -->
                    <div v-else
                        class="flex items-center justify-between bg-light-bg border border-green-200 rounded-lg p-2.5">
                        <div>
                            <p class="text-sm text-green-600">{{ form.supplier.supplier_name }}</p>
                        </div>
                        <button class="text-primary" @click="$emit('resetSupplierSelection')">
                            <span class="icon-[tabler--x]"></span>
                        </button>
                    </div>

                    <!-- Search results dropdown -->
                    <div v-if="filteredSuppliers.length > 0 && !isSupplierSelected"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
                        <div v-for="supplier in filteredSuppliers" :key="supplier.id" @click="$emit('selectSupplier', supplier)"
                            class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b last:border-0">
                            <div class="font-medium text-dark-text">{{ supplier.supplier_name }}</div>
                        </div>
                    </div>

                    <!-- No results message -->
                    <div v-else-if="searchSupplierQuery && !isLoadingSearchSupplier && !isSupplierSelected"
                        class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg p-2.5 text-center text-gray-500 z-10">
                        Pemasok tidak ditemukan
                    </div>
                </div>
                <BaseInputAdmin v-model="form.supplier.contact" label="Kontak" placeholder="Masukkan kontak pemasok" />
                <BaseInputAdmin v-model="form.supplier.address" label="Alamat" type="textarea" rows="3"
                    placeholder="Masukkan alamat pemasok" />
                <BaseInputAdmin v-model="form.supplier.website_url" label="Alamat URL"
                    placeholder="Masukkan link terkait pemasok" />
            </div>
        </div>
        <div class="card-layout mx-4">
            <h1 class="card-title">Pengadaan Barang</h1>
            <div class="grid grid-cols-2 gap-4 pt-4">
                <BaseInputAdmin type="file" label="Bukti Pembelian" v-model="form.procurement_proof_file"
                    accept=".pdf,.jpg,.jpeg,.png" required />
                <BaseInputAdmin v-model.number="form.financing.down_payment" label="Uang Muka" type="number"
                    placeholder="Masukkan uang muka" />
                <BaseInputAdmin v-model.number="form.financing.cost_price" label="Harga Perolehan Barang" type="number"
                    placeholder="Masukkan harga perolehan barang" />
                <Info label="Margin (8%)" :value="parseCurrencyAmount(form.financing.margin_amount)" />
            </div>
            <div class="bg-light-bg flex justify-between border px-8 py-4 mt-6 rounded-lg">
                <div class="font-semibold text-primary">Total Harga Murabahah</div>
                <div class="font-semibold text-primary">{{ parseCurrencyAmount(totalPrice) }}</div>
            </div>
        </div>
    </section>
</template>
