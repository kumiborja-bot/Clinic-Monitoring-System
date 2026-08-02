<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate, formatDateTime } from '@/utils/dateFormat';
import { useOfflineQueue } from '@/utils/offlineQueue';

const props = defineProps({ student: Object });

const bmiColor = (category) => {
    const c = { 'Underweight': 'bg-yellow-100 text-yellow-800', 'Normal': 'bg-green-100 text-green-800', 'Overweight': 'bg-orange-100 text-orange-800', 'Obese': 'bg-red-100 text-red-800' };
    return c[category] || 'bg-gray-100 text-gray-600';
};

const activeTab = ref('overview');

const { isOnline, addToQueue } = useOfflineQueue();
const offlineQueued = ref(false);

// Maritime document upload
const docForm = useForm({ file: null, description: '' });

const fileToBase64 = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
};

const uploadDoc = async () => {
    if (isOnline.value) {
        docForm.post(route('maritime.store', props.student.id), {
            onSuccess: () => { docForm.reset(); },
            forceFormData: true,
        });
    } else {
        const fileBase64 = await fileToBase64(docForm.file);
        await addToQueue('maritime_document', route('maritime.store', props.student.id), {
            student_id: props.student.id,
            file_base64: fileBase64,
            file_name: docForm.file.name,
            file_type: docForm.file.type,
            description: docForm.description,
        });
        offlineQueued.value = true;
        docForm.reset();
        setTimeout(() => { offlineQueued.value = false; }, 4000);
    }
};

const deleteDoc = (id) => {
    if (confirm('Are you sure you want to delete this maritime document?')) {
        router.delete(route('maritime.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="student.name" />
    <AuthenticatedLayout>
        <template #header>Student Profile</template>

        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
            <div class="h-24 bg-[#132639]"></div>
            <div class="px-6 pb-6 mt-8">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="w-16 h-16 rounded-xl bg-mustard-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg ring-4 ring-white">
                        {{ student.name?.charAt(0) }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-800">{{ student.name }}</h2>
                        <p class="text-sm text-gray-500">{{ student.student_id_number }} · {{ student.department?.code }} · {{ student.course?.name || 'N/A' }}</p>
                    </div>
                    <Link :href="route('students.edit', student.id)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg bg-[#F0A500] hover:bg-[#F4B842]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </Link>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 flex gap-1 bg-white rounded-lg p-1 shadow-sm border border-gray-100 w-fit">
            <button v-for="tab in ['overview', 'medical', 'treatments', 'documents']" :key="tab"
                @click="activeTab = tab"
                :class="activeTab === tab ? 'bg-navy-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium rounded-md transition-all capitalize">
                {{ tab }}
            </button>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Personal Information</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Birthdate</dt><dd class="font-medium">{{ formatDate(student.birthdate) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Age</dt><dd class="font-medium">{{ student.age || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Sex</dt><dd class="font-medium">{{ student.sex || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Civil Status</dt><dd class="font-medium">{{ student.civil_status || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Religion</dt><dd class="font-medium">{{ student.religion || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="font-medium text-right max-w-xs">{{ student.address || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Contact</dt><dd class="font-medium">{{ student.contact_no || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Year Level</dt><dd class="font-medium">{{ student.year_level || '-' }}</dd></div>
                </dl>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Emergency Contact</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ student.emergency_contact_person || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Relationship</dt><dd class="font-medium">{{ student.emergency_contact_relationship || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="font-medium text-right max-w-xs">{{ student.emergency_contact_address || '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Contact</dt><dd class="font-medium">{{ student.emergency_contact_no || '-' }}</dd></div>
                </dl>
            </div>
        </div>

        <!-- Medical Tab -->
        <div v-if="activeTab === 'medical'" class="space-y-6">
            <div v-if="student.medical_record" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Vitals & BMI</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500">Height</p>
                            <p class="text-lg font-bold text-navy-800">{{ student.medical_record.height_cm }} <span class="text-xs">cm</span></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500">Weight</p>
                            <p class="text-lg font-bold text-navy-800">{{ student.medical_record.weight_kg }} <span class="text-xs">kg</span></p>
                        </div>
                        <div class="col-span-2 rounded-lg p-3 text-center" :class="bmiColor(student.medical_record.bmi_category)">
                            <p class="text-xs opacity-75">BMI</p>
                            <p class="text-2xl font-bold">{{ student.medical_record.bmi }}</p>
                            <p class="text-sm font-semibold">{{ student.medical_record.bmi_category }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500">Blood Pressure</p>
                            <p class="text-sm font-bold">{{ student.medical_record.blood_pressure || '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-500">Pulse Rate</p>
                            <p class="text-sm font-bold">{{ student.medical_record.pulse_rate || '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider">Allergies</h3>
                        <div class="space-y-2">
                            <p v-if="student.medical_record.allergies" class="text-sm"><span class="text-red-600 font-medium">⚠</span> {{ student.medical_record.allergies }}</p>
                            <p v-if="student.medical_record.food_allergy" class="text-sm text-red-600">• Food Allergy</p>
                            <p v-if="student.medical_record.drug_allergy" class="text-sm text-red-600">• Drug Allergy</p>
                            <p v-if="!student.medical_record.allergies && !student.medical_record.food_allergy && !student.medical_record.drug_allergy" class="text-sm text-green-600">No known allergies ✓</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider">Family History</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-if="student.medical_record.family_hpn" class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-700">HPN</span>
                            <span v-if="student.medical_record.family_cancer" class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-700">Cancer</span>
                            <span v-if="student.medical_record.family_asthma" class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-700">Asthma</span>
                            <span v-if="student.medical_record.family_dm" class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-700">Diabetes</span>
                            <span v-if="!student.medical_record.family_hpn && !student.medical_record.family_cancer && !student.medical_record.family_asthma && !student.medical_record.family_dm"
                                class="text-sm text-gray-400">No family history recorded</span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <p class="text-gray-400 text-sm">No medical record yet.</p>
                <Link :href="route('students.edit', student.id)" class="mt-3 inline-block text-sm text-mustard-600 hover:text-mustard-700 font-medium">Add Medical Record →</Link>
            </div>
        </div>

        <!-- Treatments Tab -->
        <div v-if="activeTab === 'treatments'" class="grid grid-cols-1 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Diagnosis</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Treatment</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Nurse</th>
                                <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="t in student.treatments" :key="t.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ formatDateTime(t.treated_at) }}</td>
                                <td class="px-4 py-3 font-medium">{{ t.diagnosis }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ t.treatment_given || '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ t.treated_by_user?.name || '-' }}</td>
                                <td class="px-4 py-3"><span :class="t.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ t.status }}</span></td>
                            </tr>
                            <tr v-if="!student.treatments?.length">
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400">No treatment records</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Documents Tab (Maritime Only) -->
        <div v-if="activeTab === 'documents'">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Maritime Medical Documents</h3>

                <!-- Upload Form -->
                <form @submit.prevent="uploadDoc" class="mb-6 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Upload File (PDF, JPG, PNG)</label>
                            <input type="file" @input="docForm.file = $event.target.files[0]" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-navy-800 file:text-white file:text-xs file:font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                            <input v-model="docForm.description" type="text" placeholder="e.g., Medical Certificate"
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                    </div>
                    <button type="submit" :disabled="docForm.processing || !docForm.file"
                        class="mt-3 px-4 py-2 text-sm font-medium text-white rounded-lg disabled:opacity-50 bg-[#132639] hover:bg-[#0E4362]">
                        {{ isOnline ? 'Upload Document' : '📥 Queue Offline' }}
                    </button>
                    <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                                leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                        <p v-if="offlineQueued" class="mt-2 text-sm font-semibold text-amber-600">📥 Document queued offline — will sync when reconnected.</p>
                    </transition>
                </form>

                <!-- Document list -->
                <div v-if="student.maritime_documents?.length" class="space-y-2">
                    <div v-for="doc in student.maritime_documents" :key="doc.id"
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
                            <a :href="route('maritime.download', doc.id)" class="px-3 py-1 text-xs font-medium text-navy-600 bg-navy-50 rounded-lg hover:bg-navy-100">Download</a>
                            <button @click="deleteDoc(doc.id)" class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">Delete</button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-400 text-center py-6">No documents uploaded yet</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
