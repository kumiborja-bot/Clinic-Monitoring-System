<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ departments: Array });

const activeReport = ref('treatment');

const treatmentForm = useForm({ date_from: '', date_to: '', department_id: '' });
const medicineForm = useForm({ date_from: '', date_to: '' });
const deptForm = useForm({ department_id: '' });

const generateTreatment = () => {
    window.location.href = route('reports.treatment-summary', treatmentForm.data());
};
const generateMedicine = () => {
    window.location.href = route('reports.medicine-usage', medicineForm.data());
};
const generateDept = () => {
    window.location.href = route('reports.department', deptForm.data());
};
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <template #header>Reports</template>

        <div class="w-full space-y-6">
            <!-- Report Type Tabs -->
            <div class="flex gap-1 bg-white rounded-lg p-1 shadow-sm border border-gray-100 w-fit">
                <button v-for="tab in [{key:'treatment', label:'Treatment Summary'}, {key:'medicine', label:'Medicine Usage'}, {key:'department', label:'Department Report'}]"
                    :key="tab.key" @click="activeReport = tab.key"
                    :class="activeReport === tab.key ? 'bg-navy-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all">
                    {{ tab.label }}
                </button>
            </div>

            <!-- Treatment Summary Report -->
            <div v-if="activeReport === 'treatment'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Treatment Summary Report</h3>
                    <p class="text-xs text-gray-400 mt-1">Generate a PDF report of all treatments within a date range</p>
                </div>
                <form @submit.prevent="generateTreatment" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Date From *</label>
                            <input v-model="treatmentForm.date_from" type="date" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Date To *</label>
                            <input v-model="treatmentForm.date_to" type="date" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                            <select v-model="treatmentForm.department_id" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.name }}</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" :disabled="treatmentForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ treatmentForm.processing ? 'Generating...' : 'Generate PDF' }}
                    </button>
                </form>
            </div>

            <!-- Medicine Usage Report -->
            <div v-if="activeReport === 'medicine'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Medicine Usage Report</h3>
                    <p class="text-xs text-gray-400 mt-1">Generate a PDF report of medicine dispensing within a date range</p>
                </div>
                <form @submit.prevent="generateMedicine" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Date From *</label>
                            <input v-model="medicineForm.date_from" type="date" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Date To *</label>
                            <input v-model="medicineForm.date_to" type="date" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                    </div>
                    <button type="submit" :disabled="medicineForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ medicineForm.processing ? 'Generating...' : 'Generate PDF' }}
                    </button>
                </form>
            </div>

            <!-- Department Report -->
            <div v-if="activeReport === 'department'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Department Report</h3>
                    <p class="text-xs text-gray-400 mt-1">Generate a BMI and health overview for a specific department</p>
                </div>
                <form @submit.prevent="generateDept" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Department *</label>
                        <select v-model="deptForm.department_id" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                            <option value="">Select Department</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.full_name }}</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="deptForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ deptForm.processing ? 'Generating...' : 'Generate PDF' }}
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
