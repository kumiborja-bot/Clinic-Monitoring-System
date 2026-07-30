<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchablePatientSelect from '@/Components/SearchablePatientSelect.vue';
import SearchableMedicineSelect from '@/Components/SearchableMedicineSelect.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useOfflineQueue } from '@/utils/offlineQueue';
import axios from 'axios';

const props = defineProps({
    students: Array,
    personnel: Array,
    departments: Array,
    medicines: { type: Array, default: () => [] },
});

const { isOnline, addToQueue } = useOfflineQueue();
const offlineQueued = ref(false);

const form = useForm({
    patient_type: 'student',
    patient_id: '',
    diagnosis: '',
    treatment_given: '',
    medication_given: '',
    medicine_id: '',
    medicine_quantity: 1,
    notes: '',
    status: 'completed',
    follow_up_date: '',
});

const patientList = computed(() => form.patient_type === 'student' ? props.students : props.personnel);

const submit = async () => {
    if (isOnline.value) {
        form.post(route('treatments.store'));
    } else {
        await addToQueue('treatment', route('treatments.store'), form.data());
        offlineQueued.value = true;
        form.reset();
        setTimeout(() => { offlineQueued.value = false; }, 4000);
    }
};

const dssLoading = ref(false);
const getDssSuggestion = async () => {
    if (!form.diagnosis) {
        alert('Please enter a diagnosis first.');
        return;
    }
    dssLoading.value = true;
    try {
        const { data } = await axios.get('/api/csv-dss');
        if (data && data.length > 0) {
            // find exact or partial match
            const query = form.diagnosis.toLowerCase();
            const match = data.find(d => d.diagnosis.toLowerCase().includes(query) || query.includes(d.diagnosis.toLowerCase()));
            if (match) {
                if (match.suggestion) form.treatment_given = match.suggestion;
                if (match.medication) form.medication_given = match.medication;
                if (match.follow_up) {
                    form.notes = (form.notes ? form.notes + '\n' : '') + 'Follow-up: ' + match.follow_up;
                    if (match.follow_up.toLowerCase() !== 'no') {
                        form.status = 'follow-up';
                        // Auto-calculate follow-up date based on DSS rules
                        if (match.duration_value && match.duration_unit) {
                            const date = new Date();
                            const val = parseInt(match.duration_value);
                            if (match.duration_unit === 'days') {
                                date.setDate(date.getDate() + val);
                            } else if (match.duration_unit === 'weeks') {
                                date.setDate(date.getDate() + val * 7);
                            } else if (match.duration_unit === 'hours') {
                                date.setHours(date.getHours() + val);
                            }
                            form.follow_up_date = date.toISOString().split('T')[0];
                        } else {
                            // Default to next day if no duration given
                            const tomorrow = new Date();
                            tomorrow.setDate(tomorrow.getDate() + 1);
                            form.follow_up_date = tomorrow.toISOString().split('T')[0];
                        }
                    }
                }
                alert('DSS suggestion applied.');
            } else {
                alert('No DSS suggestion found for this diagnosis.');
            }
        }
    } catch (e) {
        console.error(e);
        alert('Failed to get DSS suggestion.');
    } finally {
        dssLoading.value = false;
    }
};
</script>

<template>
    <Head title="Record Treatment" />
    <AuthenticatedLayout>
        <template #header>Record New Treatment</template>

        <!-- Offline queued success message -->
        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <div v-if="offlineQueued" class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200/60 flex items-center gap-3 shadow-sm">
                <div class="w-9 h-9 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-700">Treatment queued offline</p>
                    <p class="text-xs text-amber-500/80">It will sync automatically when you reconnect.</p>
                </div>
            </div>
        </transition>

        <form @submit.prevent="submit" class="w-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Treatment Form</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Patient Type *</label>
                            <select v-model="form.patient_type" @change="form.patient_id = ''" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                <option value="student">Student</option>
                                <option value="personnel">Personnel</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Patient *</label>
                            <SearchablePatientSelect
                                v-model="form.patient_id"
                                :patients="patientList"
                                :departments="departments"
                                :patient-type="form.patient_type"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Diagnosis *</label>
                        <div class="flex gap-2">
                            <input v-model="form.diagnosis" type="text" required placeholder="e.g., Headache, Fever, Cough" class="flex-1 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                            <button type="button" @click="getDssSuggestion" :disabled="dssLoading" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-500 disabled:opacity-50 transition-colors shrink-0">
                                {{ dssLoading ? 'Loading...' : 'Get DSS Suggestion' }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Treatment Given</label>
                        <textarea v-model="form.treatment_given" rows="2" placeholder="Describe the treatment provided" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"></textarea>
                    </div>
                    <!-- Medicine from Inventory -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-blue-50/50 rounded-lg border border-blue-100 overflow-visible">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Medicine from Inventory</label>
                            <SearchableMedicineSelect
                                v-model="form.medicine_id"
                                :medicines="medicines"
                                placeholder="Search stocked medicine by name or category..."
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Quantity</label>
                            <input v-model="form.medicine_quantity" type="number" min="1" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Medication Notes / Additional Details</label>
                        <input v-model="form.medication_given" type="text" placeholder="e.g., Additional instructions or non-inventory meds" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                            <select v-model="form.status" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                <option value="completed">Completed</option>
                                <option value="follow-up">Follow-up Required</option>
                            </select>
                        </div>
                        <div v-if="form.status === 'follow-up'" class="transition-all duration-300">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Follow-up Date *</label>
                            <input v-model="form.follow_up_date" type="date" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                            <p class="text-[10px] text-gray-400 mt-1">This will trigger a system follow-up alert when due.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a :href="route('treatments.index')" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</a>
                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-sm font-bold text-white bg-[#132639] hover:bg-[#0E4362] rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all">
                    {{ form.processing ? 'Saving...' : (isOnline ? 'Record Treatment' : '📥 Queue Offline') }}
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
