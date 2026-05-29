<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatDateTime } from '@/utils/dateFormat';

const props = defineProps({
    treatments: Object,
    departments: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const departmentId = ref(props.filters?.department_id || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const applyFilters = () => {
    router.get(route('treatments.index'), {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

let t;
watch(search, () => { clearTimeout(t); t = setTimeout(applyFilters, 400); });

const deleteTreatment = (id) => { if (confirm('Delete this treatment record?')) router.delete(route('treatments.destroy', id)); };
</script>

<template>
    <Head title="Treatment Logbook" />
    <AuthenticatedLayout>
        <template #header>Treatment Logbook</template>

        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Search diagnosis..." class="pl-10 pr-4 py-2 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 w-64" />
                </div>
                <select v-model="departmentId" @change="applyFilters" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                    <option value="">All Departments</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.code }}</option>
                </select>
                <input v-model="dateFrom" @change="applyFilters" type="date" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                <input v-model="dateTo" @change="applyFilters" type="date" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
            </div>
            <Link :href="route('treatments.create')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#F0A500] hover:bg-[#F4B842] shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Treatment
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-navy-800 text-white">
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Patient</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Type</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Diagnosis</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Treatment</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Nurse</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Status</th>
                                <th class="px-4 py-3 text-center font-medium text-xs uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="t in treatments.data" :key="t.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ formatDateTime(t.treated_at) }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ t.patient_name }}</td>
                                <td class="px-4 py-3"><span :class="t.patient_type_label === 'Student' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'" class="px-2 py-0.5 text-xs rounded-full font-medium">{{ t.patient_type_label }}</span></td>
                                <td class="px-4 py-3 text-gray-700">{{ t.diagnosis }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs truncate max-w-xs">{{ t.treatment_given || '-' }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ t.treated_by_user?.name || '-' }}</td>
                                <td class="px-4 py-3"><span :class="t.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 text-xs rounded-full font-medium capitalize">{{ t.status }}</span></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route('treatments.show', t.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-[#0E4362] hover:text-white text-[#095D86] border border-[#095D86] transition-colors">View</Link>
                                        <button @click="deleteTreatment(t.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-red-600 hover:text-white text-red-500 border border-red-500 transition-colors">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!treatments.data?.length"><td colspan="8" class="px-4 py-12 text-center text-gray-400">No treatments found</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="treatments.links?.length > 3" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ treatments.from }}–{{ treatments.to }} of {{ treatments.total }}</p>
                    <div class="flex gap-1">
                        <template v-for="link in treatments.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-navy-800 text-white' : 'bg-gray-50 text-gray-600'" class="px-3 py-1 rounded-lg text-xs font-medium" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
