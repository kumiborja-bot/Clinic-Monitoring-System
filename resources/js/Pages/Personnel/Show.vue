<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/dateFormat';

const props = defineProps({ personnel: Object });

const bmiColor = (c) => ({ 'Underweight': 'bg-yellow-100 text-yellow-800', 'Normal': 'bg-green-100 text-green-800', 'Overweight': 'bg-orange-100 text-orange-800', 'Obese': 'bg-red-100 text-red-800' }[c] || 'bg-gray-100 text-gray-600');
</script>

<template>
    <Head :title="personnel.name" />
    <AuthenticatedLayout>
        <template #header>Personnel Profile</template>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
            <div class="h-24 bg-[#132639]"></div>
            <div class="px-6 pb-6 mt-8">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="w-16 h-16 rounded-xl bg-mustard-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg ring-4 ring-white">{{ personnel.name?.charAt(0) }}</div>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">BMI & Vitals</h3>
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
    </AuthenticatedLayout>
</template>
