<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';
import { ref, onMounted, watch, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({ personnel: Object });

const bmiColor = (c) => ({ 'Underweight': 'bg-yellow-100 text-yellow-800', 'Normal': 'bg-green-100 text-green-800', 'Overweight': 'bg-orange-100 text-orange-800', 'Obese': 'bg-red-100 text-red-800' }[c] || 'bg-gray-100 text-gray-600');

const activeTab = ref('overview');

const docForm = useForm({
    file: null,
    description: '',
});

const uploadDoc = () => {
    docForm.post(route('personnel-documents.store', props.personnel.id), {
        onSuccess: () => {
            docForm.reset();
        },
        forceFormData: true,
    });
};

const deleteDoc = (id) => {
    if (confirm('Are you sure you want to delete this document?')) {
        router.delete(route('personnel-documents.destroy', id), {
            preserveScroll: true,
        });
    }
};

const weightLogForm = useForm({
    recorded_date: new Date().toISOString().substring(0, 10),
    height_cm: props.personnel.medical_record?.height_cm || 170,
    weight_kg: props.personnel.medical_record?.weight_kg || '',
    remarks: '',
});

const submitWeightLog = () => {
    weightLogForm.post(route('personnel.weight-logs.store', props.personnel.id), {
        preserveScroll: true,
        onSuccess: () => {
            weightLogForm.weight_kg = '';
            weightLogForm.remarks = '';
        },
    });
};

const deleteWeightLog = (id) => {
    if (confirm('Are you sure you want to delete this weight log?')) {
        router.delete(route('personnel.weight-logs.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Weight Trend Chart
const weightChartCanvas = ref(null);
let weightChartInstance = null;

const renderWeightChart = () => {
    if (!weightChartCanvas.value || !props.personnel.weight_logs?.length) return;

    // Sort logs chronologically (oldest to newest) for chart timeline
    const sortedLogs = [...props.personnel.weight_logs].sort((a, b) => new Date(a.recorded_date) - new Date(b.recorded_date));

    if (weightChartInstance) weightChartInstance.destroy();

    weightChartInstance = new Chart(weightChartCanvas.value, {
        type: 'line',
        data: {
            labels: sortedLogs.map(l => formatDate(l.recorded_date)),
            datasets: [
                {
                    label: 'Weight (kg)',
                    data: sortedLogs.map(l => l.weight_kg),
                    borderColor: '#132639',
                    backgroundColor: 'rgba(19, 38, 57, 0.08)',
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'yWeight',
                    pointBackgroundColor: '#132639',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                },
                {
                    label: 'BMI',
                    data: sortedLogs.map(l => l.bmi),
                    borderColor: '#F0A500',
                    backgroundColor: 'rgba(240, 165, 0, 0.08)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.35,
                    fill: false,
                    yAxisID: 'yBmi',
                    pointBackgroundColor: '#F0A500',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } } },
                tooltip: {
                    padding: 10,
                    callbacks: {
                        afterBody: (items) => {
                            const index = items[0].dataIndex;
                            const cat = sortedLogs[index]?.bmi_category;
                            return cat ? `Category: ${cat}` : '';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
                yWeight: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Weight (kg)', font: { size: 11, weight: 'bold' } },
                    grid: { color: '#f3f4f6' },
                },
                yBmi: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'BMI Score', font: { size: 11, weight: 'bold' } },
                    grid: { drawOnChartArea: false },
                },
            },
        },
    });
};

onMounted(() => {
    if (activeTab.value === 'monitoring') {
        nextTick(renderWeightChart);
    }
});

watch([() => activeTab.value, () => props.personnel.weight_logs], () => {
    if (activeTab.value === 'monitoring') {
        nextTick(renderWeightChart);
    }
});
</script>

<template>
    <Head :title="personnel.name" />
    <AuthenticatedLayout>
        <template #header>Personnel Profile</template>

        <!-- Profile Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
            <div class="h-24 bg-[#132639]"></div>
            <div class="px-6 pb-6 mt-8">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="w-16 h-16 rounded-xl bg-mustard-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg ring-4 ring-white">
                        {{ personnel.name?.charAt(0) }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-800">{{ personnel.name }}</h2>
                        <p class="text-sm text-gray-500">{{ personnel.employee_id }} · {{ personnel.department?.code }} · {{ personnel.position || 'No Position' }}</p>
                    </div>
                    <Link :href="route('personnel.edit', personnel.id)" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg bg-[#F0A500] hover:bg-[#F4B842]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </Link>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mb-6 flex gap-1 bg-white rounded-lg p-1 shadow-sm border border-gray-100 w-fit">
            <button @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ? 'bg-[#132639] text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-semibold rounded-md transition-all">
                Overview
            </button>
            <button @click="activeTab = 'monitoring'"
                :class="activeTab === 'monitoring' ? 'bg-[#132639] text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-semibold rounded-md transition-all">
                Monthly Weight Monitoring
            </button>
            <button @click="activeTab = 'documents'"
                :class="activeTab === 'documents' ? 'bg-[#132639] text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-semibold rounded-md transition-all">
                Documents
            </button>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Personal Information</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Birthdate</dt><dd class="font-medium">{{ formatDate(personnel.birthdate) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Age</dt><dd class="font-medium">{{ personnel.age || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Sex</dt><dd class="font-medium">{{ personnel.sex || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Civil Status</dt><dd class="font-medium">{{ personnel.civil_status || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Religion</dt><dd class="font-medium">{{ personnel.religion || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="font-medium text-right max-w-xs">{{ personnel.address || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Contact</dt><dd class="font-medium">{{ personnel.contact_no || '-' }}</dd></div>
                </dl>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Emergency Contact</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ personnel.emergency_contact_person || '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Relationship</dt><dd class="font-medium">{{ personnel.emergency_contact_relationship || '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Contact</dt><dd class="font-medium">{{ personnel.emergency_contact_no || '-' }}</dd></div>
                    </dl>
                </div>

                <div v-if="personnel.medical_record" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Current BMI & Vitals</h3>
                        <button @click="activeTab = 'monitoring'" class="text-xs text-[#F0A500] hover:underline font-semibold">View Monthly Logs →</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500">Height</p>
                            <p class="text-lg font-bold text-navy-800">{{ personnel.medical_record.height_cm }} <span class="text-xs">cm</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500">Weight</p>
                            <p class="text-lg font-bold text-navy-800">{{ personnel.medical_record.weight_kg }} <span class="text-xs">kg</span></p>
                        </div>
                        <div class="col-span-2 rounded-lg p-3 text-center" :class="bmiColor(personnel.medical_record.bmi_category)">
                            <p class="text-xs opacity-75">BMI</p>
                            <p class="text-2xl font-bold">{{ personnel.medical_record.bmi }}</p>
                            <p class="text-sm font-semibold">{{ personnel.medical_record.bmi_category }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Monitoring Tab -->
        <div v-if="activeTab === 'monitoring'" class="space-y-6">
            <!-- Add Weight Log Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-2">Record Monthly Weight Monitoring</h3>
                <p class="text-xs text-gray-500 mb-4">Track personnel weight changes over time to monitor progress (e.g. Overweight ➔ Normal).</p>
                <form @submit.prevent="submitWeightLog" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Date *</label>
                        <input v-model="weightLogForm.recorded_date" type="date" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Height (cm) *</label>
                        <input v-model="weightLogForm.height_cm" type="number" step="0.1" required placeholder="170" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Weight (kg) *</label>
                        <input v-model="weightLogForm.weight_kg" type="number" step="0.1" required placeholder="e.g., 85" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Remarks</label>
                        <input v-model="weightLogForm.remarks" type="text" placeholder="e.g. Monthly checkup" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                    </div>
                    <div class="sm:col-span-2 md:col-span-4 flex justify-end">
                        <button type="submit" :disabled="weightLogForm.processing" class="px-5 py-2 text-sm font-semibold text-white rounded-lg bg-[#132639] hover:bg-[#0E4362] disabled:opacity-50 transition-all">
                            Save Weight Log
                        </button>
                    </div>
                </form>
            </div>

            <!-- Monthly Weight Trend Chart Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Monthly Weight & BMI Trend Chart</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Visual monthly monitoring chart tracking weight progress over time</p>
                    </div>
                    <span v-if="personnel.weight_logs?.length" class="text-xs px-2.5 py-1 rounded-full bg-navy-50 text-navy-800 font-medium">
                        {{ personnel.weight_logs.length }} Logged Readings
                    </span>
                </div>
                <div class="h-64" v-if="personnel.weight_logs?.length">
                    <canvas ref="weightChartCanvas"></canvas>
                </div>
                <div v-else class="h-64 flex flex-col items-center justify-center text-gray-400 text-sm border border-dashed border-gray-200 rounded-xl">
                    <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    <span>No weight logs recorded yet. Add an entry above to render the chart.</span>
                </div>
            </div>

            <!-- Weight Log History Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Monthly Weight Monitoring History</h3>

                <!-- Weight Change Progress Highlight -->
                <div v-if="personnel.weight_logs?.length >= 2" class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-blue-900 uppercase tracking-wider">Weight Trend Overview</p>
                        <p class="text-sm text-blue-800 mt-1">
                            Previous Record: <span class="font-bold">{{ personnel.weight_logs[1].weight_kg }} kg ({{ personnel.weight_logs[1].bmi_category }})</span>
                            ➔ Latest Record: <span class="font-bold">{{ personnel.weight_logs[0].weight_kg }} kg ({{ personnel.weight_logs[0].bmi_category }})</span>
                        </p>
                    </div>
                    <span v-if="personnel.weight_logs[1].bmi_category === 'Overweight' && personnel.weight_logs[0].bmi_category === 'Normal'"
                          class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-200 shadow-sm animate-pulse">
                        🎉 Great Progress: Overweight ➔ Normal
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Height (cm)</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Weight (kg)</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">BMI</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Remarks</th>
                                <th class="px-4 py-3 text-center font-medium text-xs text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="log in personnel.weight_logs" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700 font-medium">{{ formatDate(log.recorded_date) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ log.height_cm }} cm</td>
                                <td class="px-4 py-3 text-gray-900 font-bold">{{ log.weight_kg }} kg</td>
                                <td class="px-4 py-3 font-semibold">{{ log.bmi }}</td>
                                <td class="px-4 py-3">
                                    <span :class="bmiColor(log.bmi_category)" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                        {{ log.bmi_category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ log.remarks || '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="deleteWeightLog(log.id)" class="px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!personnel.weight_logs?.length">
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">No monthly weight monitoring entries recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Documents Tab -->
        <div v-if="activeTab === 'documents'">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Personnel Documents</h3>

                <!-- Upload Form -->
                <form @submit.prevent="uploadDoc" class="mb-6 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Upload File (PDF, JPG, PNG)</label>
                            <input type="file" @change="docForm.file = $event.target.files[0]" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-[#132639] file:text-white file:text-xs file:font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                            <input v-model="docForm.description" type="text" placeholder="e.g., Employment Contract"
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                    </div>
                    <button type="submit" :disabled="docForm.processing || !docForm.file"
                        class="mt-3 px-4 py-2 text-sm font-medium text-white rounded-lg disabled:opacity-50 bg-[#132639] hover:bg-[#0E4362]">
                        Upload Document
                    </button>
                </form>

                <!-- Document List -->
                <div v-if="personnel.personnel_documents?.length" class="space-y-2">
                    <div v-for="doc in personnel.personnel_documents" :key="doc.id"
                        class="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-navy-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-navy-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ doc.file_name }}</p>
                                <p class="text-xs text-gray-400">{{ doc.description || 'No description' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="route('personnel-documents.download', doc.id)" class="px-3 py-1 text-xs font-medium text-navy-600 bg-navy-50 rounded-lg hover:bg-navy-100">Download</a>
                            <button @click="deleteDoc(doc.id)" class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Delete</button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 text-center py-6">No documents uploaded yet</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
