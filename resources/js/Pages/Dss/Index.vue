<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    dssRecords: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

const applyFilters = () => {
    router.get(route('dss.index'), {
        search: search.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

// Modal State
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    diagnosis: '',
    suggestion: '',
    follow_up: '',
    medication: '',
    duration_unit: '',
    duration_value: '',
});

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (record) => {
    isEditing.value = true;
    editingId.value = record.id;
    form.clearErrors();
    form.diagnosis = record.diagnosis || '';
    form.suggestion = record.suggestion || '';
    form.follow_up = record.follow_up || '';
    form.medication = record.medication || '';
    form.duration_unit = record.duration_unit || '';
    form.duration_value = record.duration_value !== null ? record.duration_value : '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('dss.update', editingId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('dss.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const deleteRecord = (id) => {
    if (confirm('Are you sure you want to delete this decision support record? This will instantly remove it from diagnostic suggestions.')) {
        router.delete(route('dss.destroy', id), {
            preserveScroll: true,
        });
    }
};

// CSV Import
const fileInput = ref(null);
const importForm = useForm({
    file: null
});

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        importForm.file = file;
        importForm.post(route('dss.import-csv'), {
            preserveScroll: true,
            onSuccess: () => {
                importForm.reset();
            },
            onError: (errors) => {
                alert(errors.file || 'An error occurred during import.');
            }
        });
    }
};
</script>

<template>
    <Head title="Decision Support System Master" />
    <AuthenticatedLayout>
        <template #header>Decision Support Master Data</template>

        <!-- Top Bar -->
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input v-model="search" type="text" placeholder="Search diagnosis or treatment..."
                    class="pl-10 pr-4 py-2 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 w-80" />
            </div>

            <div class="flex items-center gap-3">
                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" class="hidden" />
                
                <button @click="triggerFileInput" :disabled="importForm.processing"
                    class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-xs hover:bg-gray-50 disabled:opacity-50 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    {{ importForm.processing ? 'Importing...' : 'Upload DSS CSV' }}
                </button>

                <button @click="openAddModal"
                    class="px-4 py-2 text-sm font-bold text-white bg-[#132639] hover:bg-[#0E4362] rounded-lg shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add DSS Record
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <th class="px-5 py-3.5">Diagnosis</th>
                            <th class="px-5 py-3.5">Suggestion / Treatment</th>
                            <th class="px-5 py-3.5">Medication</th>
                            <th class="px-5 py-3.5">Duration</th>
                            <th class="px-5 py-3.5">Follow-Up</th>
                            <th class="px-5 py-3.5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                        <tr v-for="record in dssRecords.data" :key="record.id" class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-4 font-semibold text-[#132639]">
                                {{ record.diagnosis }}
                            </td>
                            <td class="px-5 py-4 max-w-xs truncate" :title="record.suggestion">
                                {{ record.suggestion }}
                            </td>
                            <td class="px-5 py-4">
                                <span v-if="record.medication" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#095D86]">
                                    {{ record.medication }}
                                </span>
                                <span v-else class="text-xs text-gray-400">None</span>
                            </td>
                            <td class="px-5 py-4">
                                <span v-if="record.duration_value">
                                    {{ record.duration_value }} {{ record.duration_unit }}
                                </span>
                                <span v-else class="text-xs text-gray-400">-</span>
                            </td>
                            <td class="px-5 py-4">
                                <span :class="record.follow_up && record.follow_up.toLowerCase() !== 'no' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-50 text-gray-500 border-gray-200'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                    {{ record.follow_up || 'No' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(record)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded hover:bg-[#F0A500] hover:text-white text-[#F0A500] border border-[#F0A500] transition-colors"
                                        title="Edit record">
                                        Edit
                                    </button>
                                    <button @click="deleteRecord(record.id)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded hover:bg-red-600 hover:text-white text-red-500 border border-red-500 transition-colors"
                                        title="Delete record">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!dssRecords.data?.length">
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">No decision support records found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="dssRecords.links?.length > 3" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/30">
                <p class="text-xs text-gray-500">
                    Showing {{ dssRecords.from }} to {{ dssRecords.to }} of {{ dssRecords.total }} records
                </p>
                <div class="flex gap-1">
                    <template v-for="link in dssRecords.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url"
                            :class="link.active ? 'bg-navy-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all shadow-sm"
                            v-html="link.label" />
                        <span v-else class="px-3 py-1.5 text-xs text-gray-300 bg-white border border-gray-100 rounded-lg cursor-not-allowed select-none" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Add/Edit Record Modal -->
        <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                <div class="bg-white rounded-xl shadow-xl border border-gray-100 w-full max-w-lg overflow-hidden transform transition-all">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-navy-800 text-white flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider">
                            {{ isEditing ? 'Edit Decision Support Case' : 'New Decision Support Case' }}
                        </h3>
                        <button @click="closeModal" class="text-white/80 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Diagnosis *</label>
                            <input v-model="form.diagnosis" type="text" required placeholder="e.g. Acid Reflux, Asthma Attack"
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                            <p v-if="form.errors.diagnosis" class="text-xs text-red-500 mt-1">{{ form.errors.diagnosis }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Treatment Suggestion *</label>
                            <textarea v-model="form.suggestion" required rows="3" placeholder="Describe clinical treatment or procedure suggestion..."
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"></textarea>
                            <p v-if="form.errors.suggestion" class="text-xs text-red-500 mt-1">{{ form.errors.suggestion }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Medication Suggestion</label>
                                <input v-model="form.medication" type="text" placeholder="e.g. Antacid, Paracetamol"
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                                <p v-if="form.errors.medication" class="text-xs text-red-500 mt-1">{{ form.errors.medication }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Follow-Up Criteria</label>
                                <input v-model="form.follow_up" type="text" placeholder="e.g. Yes - if no improvement after 3 days"
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                                <p v-if="form.errors.follow_up" class="text-xs text-red-500 mt-1">{{ form.errors.follow_up }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Duration Value</label>
                                <input v-model="form.duration_value" type="number" min="0" placeholder="e.g. 3, 5, 7"
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                                <p v-if="form.errors.duration_value" class="text-xs text-red-500 mt-1">{{ form.errors.duration_value }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Duration Unit</label>
                                <select v-model="form.duration_unit"
                                    class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                    <option value="">None</option>
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                    <option value="weeks">Weeks</option>
                                </select>
                                <p v-if="form.errors.duration_unit" class="text-xs text-red-500 mt-1">{{ form.errors.duration_unit }}</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2 text-sm font-bold text-white bg-[#132639] hover:bg-[#0E4362] rounded-lg shadow-sm disabled:opacity-50 transition-colors">
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>
