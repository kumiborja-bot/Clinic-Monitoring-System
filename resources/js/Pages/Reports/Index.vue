<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Head,
    useForm,
} from '@inertiajs/vue3';

import {
    nextTick,
    onMounted,
    ref,
    watch,
} from 'vue';

import {
    Chart,
    registerables,
} from 'chart.js';

Chart.register(
    ...registerables
);

const props = defineProps({
    departments: Array,
    medicines: Array,
    topDiagnoses: Array,
    treatmentTrends: Array,
    topMedicines: Array,
    overallBmiStats: Object,
});

const activeReport =
    ref('treatment');

const treatmentForm =
    useForm({
        date_from: '',
        date_to: '',
        department_id: '',
    });

const medicineForm =
    useForm({
        medicine_id: '',
        date_from: '',
        date_to: '',
    });

const deptForm =
    useForm({
        department_id: '',
    });

const generateTreatment = () => {

    window.location.href =
        route(
            'reports.treatment-summary',
            treatmentForm.data()
        );
};

const generateMedicine = () => {

    window.location.href =
        route(
            'reports.medicine-usage',
            medicineForm.data()
        );
};

const generateDept = () => {

    window.location.href =
        route(
            'reports.department',
            deptForm.data()
        );
};

/*
|--------------------------------------------------------------------------
| Charts
|--------------------------------------------------------------------------
*/

const treatmentDiagnosisCanvas =
    ref(null);

const treatmentTrendCanvas =
    ref(null);

const medicineCanvas =
    ref(null);

const bmiCanvas =
    ref(null);

let treatmentDiagnosisChart =
    null;

let treatmentTrendChart =
    null;

let medicineChartInstance =
    null;

let bmiChartInstance =
    null;

const renderTreatmentCharts =
    () => {

        if (
            treatmentDiagnosisCanvas.value
            &&
            props.topDiagnoses?.length
        ) {

            treatmentDiagnosisChart
                ?.destroy();

            treatmentDiagnosisChart =
                new Chart(
                    treatmentDiagnosisCanvas.value,
                    {
                        type:
                            'doughnut',

                        data: {
                            labels:
                                props.topDiagnoses.map(
                                    item =>
                                        item.diagnosis
                                ),

                            datasets: [
                                {
                                    data:
                                        props.topDiagnoses.map(
                                            item =>
                                                item.total
                                        ),

                                    backgroundColor: [
                                        '#132639',
                                        '#F0A500',
                                        '#0E4362',
                                        '#095D86',
                                        '#F4B842',
                                        '#3b5a9f',
                                        '#2d4a7a',
                                    ],

                                    borderWidth:
                                        0,
                                },
                            ],
                        },

                        options: {
                            responsive:
                                true,

                            maintainAspectRatio:
                                false,

                            plugins: {
                                legend: {
                                    position:
                                        'bottom',
                                },
                            },
                        },
                    }
                );
        }

        if (
            treatmentTrendCanvas.value
            &&
            props.treatmentTrends?.length
        ) {

            treatmentTrendChart
                ?.destroy();

            treatmentTrendChart =
                new Chart(
                    treatmentTrendCanvas.value,
                    {
                        type:
                            'line',

                        data: {
                            labels:
                                props.treatmentTrends.map(
                                    item =>
                                        item.date
                                ),

                            datasets: [
                                {
                                    label:
                                        'Treatments',

                                    data:
                                        props.treatmentTrends.map(
                                            item =>
                                                item.total
                                        ),

                                    borderColor:
                                        '#132639',

                                    backgroundColor:
                                        'rgba(19, 38, 57, 0.1)',

                                    tension:
                                        0.3,

                                    fill:
                                        true,
                                },
                            ],
                        },

                        options: {
                            responsive:
                                true,

                            maintainAspectRatio:
                                false,

                            plugins: {
                                legend: {
                                    display:
                                        false,
                                },
                            },

                            scales: {
                                y: {
                                    beginAtZero:
                                        true,
                                },
                            },
                        },
                    }
                );
        }
    };

const renderMedicineChart =
    () => {

        if (
            !medicineCanvas.value
            ||
            !props.topMedicines?.length
        ) {
            return;
        }

        medicineChartInstance
            ?.destroy();

        medicineChartInstance =
            new Chart(
                medicineCanvas.value,
                {
                    type:
                        'bar',

                    data: {
                        labels:
                            props.topMedicines.map(
                                item =>
                                    item.medicine
                                        ?.name
                                    || 'Unknown'
                            ),

                        datasets: [
                            {
                                label:
                                    'Total Dispensed',

                                data:
                                    props.topMedicines.map(
                                        item =>
                                            item.total
                                    ),

                                backgroundColor:
                                    '#F0A500',

                                borderRadius:
                                    6,
                            },
                        ],
                    },

                    options: {
                        indexAxis:
                            'y',

                        responsive:
                            true,

                        maintainAspectRatio:
                            false,

                        plugins: {
                            legend: {
                                display:
                                    false,
                            },
                        },

                        scales: {
                            x: {
                                beginAtZero:
                                    true,
                            },
                        },
                    },
                }
            );
    };

const renderBmiChart =
    () => {

        if (
            !bmiCanvas.value
            ||
            !props.overallBmiStats
        ) {
            return;
        }

        bmiChartInstance
            ?.destroy();

        bmiChartInstance =
            new Chart(
                bmiCanvas.value,
                {
                    type:
                        'doughnut',

                    data: {
                        labels: [
                            'Underweight',
                            'Normal',
                            'Overweight',
                            'Obese',
                        ],

                        datasets: [
                            {
                                data: [
                                    props.overallBmiStats
                                        .underweight || 0,

                                    props.overallBmiStats
                                        .normal || 0,

                                    props.overallBmiStats
                                        .overweight || 0,

                                    props.overallBmiStats
                                        .obese || 0,
                                ],

                                backgroundColor: [
                                    '#eab308',
                                    '#22c55e',
                                    '#f97316',
                                    '#ef4444',
                                ],

                                borderWidth:
                                    0,
                            },
                        ],
                    },

                    options: {
                        responsive:
                            true,

                        maintainAspectRatio:
                            false,

                        plugins: {
                            legend: {
                                position:
                                    'bottom',
                            },
                        },
                    },
                }
            );
    };

const renderActiveChart =
    () => {

        nextTick(() => {

            if (
                activeReport.value
                === 'treatment'
            ) {
                renderTreatmentCharts();
            }

            if (
                activeReport.value
                === 'medicine'
            ) {
                renderMedicineChart();
            }

            if (
                activeReport.value
                === 'department'
            ) {
                renderBmiChart();
            }
        });
    };

onMounted(
    renderActiveChart
);

watch(
    activeReport,
    renderActiveChart
);
</script>

<template>

    <Head title="Reports" />

    <AuthenticatedLayout>

        <template #header>
            Reports & Analytics
        </template>

        <div
            class="w-full space-y-6"
        >

            <!-- Tabs -->

            <div
                class="flex gap-1 bg-white rounded-lg p-1 shadow-sm border border-gray-100 w-fit"
            >

                <button
                    v-for="tab in [
                        {
                            key: 'treatment',
                            label: 'Treatment Summary'
                        },
                        {
                            key: 'medicine',
                            label: 'Medicine Usage'
                        },
                        {
                            key: 'department',
                            label: 'Department Report'
                        }
                    ]"
                    :key="tab.key"
                    @click="
                        activeReport =
                            tab.key
                    "
                    :class="
                        activeReport
                        === tab.key
                            ? 'bg-navy-800 text-white'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                    class="px-4 py-2 text-sm font-semibold rounded-md"
                >
                    {{ tab.label }}
                </button>

            </div>

            <!-- Treatment -->

            <div
                v-if="
                    activeReport
                    === 'treatment'
                "
                class="space-y-6"
            >

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                >

                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                    >

                        <h4
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3"
                        >
                            Top Diagnoses Distribution
                        </h4>

                        <div
                            v-if="
                                topDiagnoses
                                    ?.length
                            "
                            class="h-60"
                        >
                            <canvas
                                ref="treatmentDiagnosisCanvas"
                            ></canvas>
                        </div>

                        <div
                            v-else
                            class="h-60 flex items-center justify-center text-gray-400 text-sm"
                        >
                            No treatment data recorded
                        </div>

                    </div>

                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                    >

                        <h4
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3"
                        >
                            Recent Treatment Volume Trend
                        </h4>

                        <div
                            v-if="
                                treatmentTrends
                                    ?.length
                            "
                            class="h-60"
                        >
                            <canvas
                                ref="treatmentTrendCanvas"
                            ></canvas>
                        </div>

                        <div
                            v-else
                            class="h-60 flex items-center justify-center text-gray-400 text-sm"
                        >
                            No recent treatment data
                        </div>

                    </div>

                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >

                    <div
                        class="px-6 py-4 bg-navy-800"
                    >

                        <h3
                            class="text-sm font-bold text-white uppercase"
                        >
                            Generate Treatment Summary PDF
                        </h3>

                    </div>

                    <form
                        @submit.prevent="
                            generateTreatment
                        "
                        class="p-6 space-y-4"
                    >

                        <div
                            class="grid grid-cols-1 sm:grid-cols-3 gap-4"
                        >

                            <div>

                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1"
                                >
                                    Date From *
                                </label>

                                <input
                                    v-model="
                                        treatmentForm.date_from
                                    "
                                    type="date"
                                    required
                                    class="w-full text-sm rounded-lg border-gray-200"
                                />

                            </div>

                            <div>

                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1"
                                >
                                    Date To *
                                </label>

                                <input
                                    v-model="
                                        treatmentForm.date_to
                                    "
                                    type="date"
                                    required
                                    class="w-full text-sm rounded-lg border-gray-200"
                                />

                            </div>

                            <div>

                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1"
                                >
                                    Department
                                </label>

                                <select
                                    v-model="
                                        treatmentForm.department_id
                                    "
                                    class="w-full text-sm rounded-lg border-gray-200"
                                >

                                    <option value="">
                                        All Departments
                                    </option>

                                    <option
                                        v-for="department in departments"
                                        :key="department.id"
                                        :value="department.id"
                                    >
                                        {{ department.code }}
                                        -
                                        {{
                                            department.name
                                            ||
                                            department.full_name
                                        }}
                                    </option>

                                </select>

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white rounded-lg bg-[#132639] hover:bg-[#0E4362]"
                        >
                            Generate Treatment PDF
                        </button>

                    </form>

                </div>

            </div>

            <!-- Medicine Usage -->

            <div
                v-if="
                    activeReport
                    === 'medicine'
                "
                class="space-y-6"
            >

                <!-- Chart -->

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >

                    <h4
                        class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3"
                    >
                        Most Dispensed Medicines Chart
                    </h4>

                    <div
                        v-if="
                            topMedicines
                                ?.length
                        "
                        class="h-64"
                    >
                        <canvas
                            ref="medicineCanvas"
                        ></canvas>
                    </div>

                    <div
                        v-else
                        class="h-64 flex items-center justify-center text-gray-400 text-sm"
                    >
                        No medicine dispensing data recorded
                    </div>

                </div>

                <!-- Stock Card Generator -->

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >

                    <div
                        class="px-6 py-4 bg-navy-800"
                    >

                        <h3
                            class="text-sm font-bold text-white uppercase tracking-wider"
                        >
                            Generate Medicine Stock Card
                        </h3>

                        <p
                            class="text-xs text-gray-300 mt-1"
                        >
                            Select one medicine to generate its official Stock Card Form
                        </p>

                    </div>

                    <form
                        @submit.prevent="
                            generateMedicine
                        "
                        class="p-6 space-y-4"
                    >

                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-4"
                        >

                            <!-- Medicine -->

                            <div>

                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1"
                                >
                                    Medicine *
                                </label>

                                <select
                                    v-model="
                                        medicineForm.medicine_id
                                    "
                                    required
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                                >

                                    <option value="">
                                        Select Medicine
                                    </option>

                                    <option
                                        v-for="medicine in medicines"
                                        :key="medicine.id"
                                        :value="medicine.id"
                                    >
                                        {{ medicine.name }}

                                        {{
                                            medicine.supplier
                                                ? `- ${medicine.supplier}`
                                                : ''
                                        }}
                                    </option>

                                </select>

                            </div>

                            <!-- From -->

                            <div>

                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1"
                                >
                                    Date From *
                                </label>

                                <input
                                    v-model="
                                        medicineForm.date_from
                                    "
                                    type="date"
                                    required
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                                />

                            </div>

                            <!-- To -->

                            <div>

                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1"
                                >
                                    Date To *
                                </label>

                                <input
                                    v-model="
                                        medicineForm.date_to
                                    "
                                    type="date"
                                    required
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                                />

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white rounded-lg bg-[#132639] hover:bg-[#0E4362]"
                        >
                            Generate Stock Card PDF
                        </button>

                    </form>

                </div>

            </div>

            <!-- Department -->

            <div
                v-if="
                    activeReport
                    === 'department'
                "
                class="space-y-6"
            >

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >

                    <h4
                        class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3"
                    >
                        Overall BMI Category Breakdown
                    </h4>

                    <div
                        v-if="
                            overallBmiStats
                        "
                        class="h-64"
                    >
                        <canvas
                            ref="bmiCanvas"
                        ></canvas>
                    </div>

                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >

                    <div
                        class="px-6 py-4 bg-navy-800"
                    >

                        <h3
                            class="text-sm font-bold text-white uppercase"
                        >
                            Generate Department Health Report PDF
                        </h3>

                    </div>

                    <form
                        @submit.prevent="
                            generateDept
                        "
                        class="p-6 space-y-4"
                    >

                        <div>

                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1"
                            >
                                Department *
                            </label>

                            <select
                                v-model="
                                    deptForm.department_id
                                "
                                required
                                class="w-full text-sm rounded-lg border-gray-200"
                            >

                                <option value="">
                                    Select Department
                                </option>

                                <option
                                    v-for="department in departments"
                                    :key="department.id"
                                    :value="department.id"
                                >
                                    {{ department.code }}
                                    -
                                    {{
                                        department.name
                                        ||
                                        department.full_name
                                    }}
                                </option>

                            </select>

                        </div>

                        <button
                            type="submit"
                            class="px-5 py-2.5 text-sm font-bold text-white rounded-lg bg-[#132639] hover:bg-[#0E4362]"
                        >
                            Generate Department PDF
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>