<script setup>
import AdminLayout from '@/Layouts/Admin/Layout.vue'
import PageBreadcrumb from '@/Components/PageBreadcrumb.vue'
import { ref } from 'vue'
import Button from '@/Components/Form/Button.vue'
import { useUserValidation } from '@/Composables/Validation/useUserValidation'
import { useFinancingForm } from '@/Composables/Form/useFinancingForm'
import PersonalData from './Create/PersonalData.vue'

import FinancialData from './Create/FinancialData.vue'
import FinancingObjectData from './Create/FinancingObjectData.vue'
import ProcurementData from './Create/ProcurementData.vue'
import FinalizationData from './Create/Finalization.vue'
import Stepper from './Create/Stepper.vue'
import Documents from './Create/Documents.vue'

const activeStep = ref(1)
const totalSteps = 5

const breadcrumbItems = [
    { name: 'Dashboard', link: '/admin' },
    { name: 'Pengelolaan Pembiayaan Murabahah', link: '/admin/financing/list' },
    { name: 'Permohonan Pembiayaan Murabahah' },
]

const props = defineProps({
    data: Object,
    financing: Object,
})

console.log('Create Financing - form data:', props.data) // Debugging line to check the structure of data prop

const {
    form,
    searchQuery,
    memberResults,
    isLoadingSearch,
    selectedMember,
    isMemberSelected,
    filteredMembers,
    searchSupplierQuery,
    supplierResults,
    isLoadingSearchSupplier,
    selectedSupplier,
    isSupplierSelected,
    filteredSuppliers,
    totalIncome,
    totalExpense,
    netIncome,
    selectMember,
    selectSupplier,
    addIncome,
    removeIncome,
    addExpense,
    removeExpense,
    addHeir,
    removeHeir,
    resetMemberSelection,
    submitDraft,
} = useFinancingForm(props.financing)

const { errors } = useUserValidation(form)

const nextStep = () => {
    activeStep.value++
}

const prevStep = () => {
    activeStep.value--
}
</script>

<template>
    <AdminLayout title="Permohonan Pembiayaan Murabahah">
        <PageBreadcrumb page-title="Permohonan Pembiayaan Murabahah" :items="breadcrumbItems" />
        <div class="grid grid-cols-6 gap-6">
            <div class="card-layout justify-between flex flex-col col-span-4 px-0!">
                <PersonalData v-if="activeStep === 1" :form="form" :search-query="searchQuery"
                    :is-loading-search="isLoadingSearch" :is-member-selected="isMemberSelected"
                    :filtered-members="filteredMembers" :data="props.data" :errors="errors"
                    @update:search-query="searchQuery = $event" @selectMember="selectMember" @addHeir="addHeir"
                    @removeHeir="removeHeir" @resetMemberSelection="resetMemberSelection" />

                <FinancialData v-if="activeStep === 2" :form="form" :data="props.data" @addIncome="addIncome"
                    @removeIncome="removeIncome" @addExpense="addExpense" @removeExpense="removeExpense" />

                <FinancingObjectData v-if="activeStep === 3" :form="form" :data="props.data" />

                <ProcurementData v-if="activeStep === 4" :form="form" :search-supplier-query="searchSupplierQuery"
                    :is-loading-search-supplier="isLoadingSearchSupplier" :is-supplier-selected="isSupplierSelected"
                    :filtered-suppliers="filteredSuppliers" @update:search-supplier-query="searchSupplierQuery = $event"
                    @selectSupplier="selectSupplier" />

                <FinalizationData v-if="activeStep === 5" :form="form" />

                <div :class="activeStep === 1 ? 'justify-end' : 'justify-between'" class="flex gap-4 p-4">
                    <Button v-if="activeStep > 1" @click="prevStep" variant="gray">
                        Kembali
                    </Button>
                    <div class="flex items-center gap-4 justify-end">
                        <Button variant="light" @click="submitDraft">
                            Simpan Sementara
                        </Button>
                        <Button v-if="activeStep < totalSteps && activeStep !== 5" @click="nextStep" variant="primary"
                            :disabled="(activeStep === 1 && !isMemberSelected) || (activeStep === 2 && form.member.incomes.length === 0) || (activeStep === 2 && form.member.expenses.length === 0) || (activeStep === 3 && !form.financing.name) || (activeStep === 4 && !form.financing.cost_price)">
                            Selanjutnya
                        </Button>
                        <Button v-else-if="activeStep === totalSteps" type="submit" variant="secondary">Ajukan
                            Permohonan</Button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full col-span-2 gap-6">
                <Stepper :active-step="activeStep" />
                <Documents :form="form" />
            </div>
        </div>
    </AdminLayout>
</template>
