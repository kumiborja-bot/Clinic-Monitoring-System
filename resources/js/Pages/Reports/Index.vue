<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, watch, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    departments: Array,
    topDiagnoses: Array,
    treatmentTrends: Array,
    topMedicines: Array,
    overallBmiStats: Object,
});

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

// Chart references & instances
const treatmentDiagnosisCanvas = ref(null);
const treatmentTrendCanvas = ref(null);
const medicineCanvas = ref(null);
const bmiCanvas = ref(null);

let treatmentDiagnosisChart = null;
let treatmentTrendChart = null;
let medicineChartInstance = null;
let bmiChartInstance = null;

const renderTreatmentCharts = () => {
    if (treatmentDiagnosisCanvas.value && props.topDiagnoses?.length) {
        if (treatmentDiagnosisChart) treatmentDiagnosisChart.destroy();
        treatmentDiagnosisChart = new Chart(treatmentDiagnosisCanvas.value, {
            type: 'doughnut',
            data: {
                labels: props.topDiagnoses.map(d => d.diagnosis),
                datasets: [{
                    data: props.topDiagnoses.map(d => d.total),
                    backgroundColor: ['#132639', '#F0A500', '#0E4362', '#095D86', '#F4B842', '#3b5a9f', '#2d4a7a'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                },
            },
        });
    }

    if (treatmentTrendCanvas.value && props.treatmentTrends?.length) {
        if (treatmentTrendChart) treatmentTrendChart.destroy();
        treatmentTrendChart = new Chart(treatmentTrendCanvas.value, {
            type: 'line',
            data: {
                labels: props.treatmentTrends.map(t => t.date),
                datasets: [{
                    label: 'Treatments',
                    data: props.treatmentTrends.map(t => t.total),
                    borderColor: '#132639',
                    backgroundColor: 'rgba(19, 38, 57, 0.1)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                },
            },
        });
    }
};

const renderMedicineChart = () => {
    if (medicineCanvas.value && props.topMedicines?.length) {
        if (medicineChartInstance) medicineChartInstance.destroy();
        medicineChartInstance = new Chart(medicineCanvas.value, {
            type: 'bar',
            data: {
                labels: props.topMedicines.map(m => m.medicine?.name || 'Unknown'),
                datasets: [{
                    label: 'Total Dispensed',
                    data: props.topMedicines.map(m => m.total),
                    backgroundColor: '#F0A500',
                    borderRadius: 6,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        });
    }
};

const renderBmiChart = () => {
    if (bmiCanvas.value && props.overallBmiStats) {
        if (bmiChartInstance) bmiChartInstance.destroy();
        bmiChartInstance = new Chart(bmiCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Underweight', 'Normal', 'Overweight', 'Obese'],
                datasets: [{
                    data: [
                        props.overallBmiStats.underweight || 0,
                        props.overallBmiStats.normal || 0,
                        props.overallBmiStats.overweight || 0,
                        props.overallBmiStats.obese || 0,
                    ],
                    backgroundColor: ['#eab308', '#22c55e', '#f97316', '#ef4444'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                },
            },
        });
    }
};

const renderActiveTabChart = () => {
    nextTick(() => {
        if (activeReport.value === 'treatment') {
            renderTreatmentCharts();
        } else if (activeReport.value === 'medicine') {
            renderMedicineChart();
        } else if (activeReport.value === 'department') {
            renderBmiChart();
        }
    });
};

onMounted(() => {
    renderActiveTabChart();
});

watch(activeReport, () => {
    renderActiveTabChart();
});
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <template #header>Reports & Analytics</template>

        <div class="w-full space-y-6">
            <!-- Report Type Tabs -->
            <div class="flex gap-1 bg-white rounded-lg p-1 shadow-sm border border-gray-100 w-fit">
                <button v-for="tab in [{key:'treatment', label:'Treatment Summary'}, {key:'medicine', label:'Medicine Usage'}, {key:'department', label:'Department Report'}]"
                    :key="tab.key" @click="activeReport = tab.key"
                    :class="activeReport === tab.key ? 'bg-navy-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-semibold rounded-md transition-all">
                    {{ tab.label }}
                </button>
            </div>

            <!-- Treatment Summary Report -->
            <div v-if="activeReport === 'treatment'" class="space-y-6">
                <!-- Visual Analytics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Top Diagnoses Distribution</h4>
                        <div class="h-60" v-if="topDiagnoses?.length">
                            <canvas ref="treatmentDiagnosisCanvas"></canvas>
                        </div>
                        <div v-else class="h-60 flex items-center justify-center text-gray-400 text-sm">No treatment data recorded</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Recent Treatment Volume Trend</h4>
                        <div class="h-60" v-if="treatmentTrends?.length">
                            <canvas ref="treatmentTrendCanvas"></canvas>
                        </div>
                        <div v-else class="h-60 flex items-center justify-center text-gray-400 text-sm">No recent treatment trend data</div>
                    </div>
                </div>

                <!-- PDF Generation Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-navy-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Generate Treatment Summary PDF</h3>
                            <p class="text-xs text-gray-400 mt-1">Export detailed treatment records filtered by date range and department</p>
                        </div>
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
                                    <optgroup label="Academic Departments (Students)">
                                        <option v-for="dept in departments.filter(d => !d.type || d.type === 'student')" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.name }}</option>
                                    </optgroup>
                                    <optgroup label="Non-Academic Departments (Personnel)">
                                        <option v-for="dept in departments.filter(d => d.type === 'personnel')" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.name }}</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <button type="submit" :disabled="treatmentForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ treatmentForm.processing ? 'Generating...' : 'Generate Treatment PDF' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Medicine Usage Report -->
            <div v-if="activeReport === 'medicine'" class="space-y-6">
                <!-- Visual Analytics Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Most Dispensed Medicines Chart</h4>
                    <div class="h-64" v-if="topMedicines?.length">
                        <canvas ref="medicineCanvas"></canvas>
                    </div>
                    <div v-else class="h-64 flex items-center justify-center text-gray-400 text-sm">No medicine dispensing data recorded</div>
                </div>

                <!-- PDF Generation Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Generate Medicine Usage PDF</h3>
                        <p class="text-xs text-gray-400 mt-1">Export medicine dispensing logs within a specific date range</p>
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
                            {{ medicineForm.processing ? 'Generating...' : 'Generate Medicine PDF' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Department Report -->
            <div v-if="activeReport === 'department'" class="space-y-6">
                <!-- Visual Analytics Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Overall BMI Category Breakdown Chart</h4>
                    <div class="h-64" v-if="overallBmiStats">
                        <canvas ref="bmiCanvas"></canvas>
                    </div>
                    <div v-else class="h-64 flex items-center justify-center text-gray-400 text-sm">No BMI record data found</div>
                </div>

                <!-- PDF Generation Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Generate Department Health Report PDF</h3>
                        <p class="text-xs text-gray-400 mt-1">Export a complete BMI and medical overview PDF for a selected department</p>
                    </div>
                    <form @submit.prevent="generateDept" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Department *</label>
                            <select v-model="deptForm.department_id" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                <option value="">Select Department</option>
                                <optgroup label="Academic Departments (Students)">
                                    <option v-for="dept in departments.filter(d => !d.type || d.type === 'student')" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.full_name }}</option>
                                </optgroup>
                                <optgroup label="Non-Academic Departments (Personnel)">
                                    <option v-for="dept in departments.filter(d => d.type === 'personnel')" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.full_name }}</option>
                                </optgroup>
                            </select>
                        </div>
                        <button type="submit" :disabled="deptForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ deptForm.processing ? 'Generating...' : 'Generate Department PDF' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
