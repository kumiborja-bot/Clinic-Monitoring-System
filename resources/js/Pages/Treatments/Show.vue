<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDateTime } from '@/utils/dateFormat';

defineProps({ treatment: Object });
</script>

<template>
    <Head title="Treatment Details" />
    <AuthenticatedLayout>
        <template #header>Treatment Details</template>

        <div class="max-w-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Treatment Record</h3>
                    <span :class="treatment.status === 'completed' ? 'bg-green-400/20 text-green-300' : 'bg-amber-400/20 text-amber-300'"
                        class="px-3 py-1 rounded-full text-xs font-medium capitalize">{{ treatment.status }}</span>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Patient</p>
                            <p class="text-sm font-bold text-gray-800">{{ treatment.patient_name }}</p>
                            <span :class="treatment.patient_type_label === 'Student' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                class="mt-1 inline-block px-2 py-0.5 text-xs rounded-full font-medium">{{ treatment.patient_type_label }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Date & Time</p>
                            <p class="text-sm font-bold text-gray-800">{{ formatDateTime(treatment.treated_at) }}</p>
                            <p class="text-xs text-gray-500 mt-1">By: {{ treatment.treated_by_user?.name || '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Chief Complain</p>
                        <p class="text-sm text-gray-800 bg-red-50 rounded-lg px-4 py-3 border border-red-100">{{ treatment.diagnosis }}</p>
                    </div>
                    <div v-if="treatment.treatment_given">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Treatment Given</p>
                        <p class="text-sm text-gray-700">{{ treatment.treatment_given }}</p>
                    </div>
                    <div v-if="treatment.medication_given">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Medication Given</p>
                        <p class="text-sm text-gray-700">{{ treatment.medication_given }}</p>
                    </div>
                    <div v-if="treatment.notes">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Notes</p>
                        <p class="text-sm text-gray-600">{{ treatment.notes }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <Link :href="route('treatments.index')" class="text-sm text-navy-600 hover:text-navy-800 font-medium">← Back to Treatments</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
