<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    personnel: Object,
    departments: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const departmentId = ref(props.filters?.department_id || '');

const applyFilters = () => {
    router.get(route('personnel.index'), {
        search: search.value || undefined,
        department_id: departmentId.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(applyFilters, 400); });

const deletePersonnel = (id) => {
    if (confirm('Are you sure you want to delete this personnel?')) {
        router.delete(route('personnel.destroy', id));
    }
};
</script>

<template>
    <Head title="Personnel" />
    <AuthenticatedLayout>
        <template #header>Personnel</template>

        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Search personnel..." class="pl-10 pr-4 py-2 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 w-64" />
                </div>
                <select v-model="departmentId" @change="applyFilters" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                    <option value="">All Departments</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.code }}</option>
                </select>
            </div>
            <Link :href="route('personnel.create')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#F0A500] hover:bg-[#F4B842] shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Personnel
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Personel Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-navy-800 text-white">
                            <th class="px-4 py-3 text-left font-medium text-xs uppercase tracking-wider">Employee ID</th>
                            <th class="px-4 py-3 text-left font-medium text-xs uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-xs uppercase tracking-wider">Department</th>
                            <th class="px-4 py-3 text-left font-medium text-xs uppercase tracking-wider">Position</th>
                            <th class="px-4 py-3 text-left font-medium text-xs uppercase tracking-wider">Sex</th>
                            <th class="px-4 py-3 text-left font-medium text-xs uppercase tracking-wider">BMI</th>
                            <th class="px-4 py-3 text-center font-medium text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="p in personnel.data" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ p.employee_id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ p.name }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-navy-100 text-navy-800">{{ p.department?.code }}</span></td>
                            <td class="px-4 py-3 text-gray-600">{{ p.position || '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ p.sex || '-' }}</td>
                            <td class="px-4 py-3">
                                <span v-if="p.medical_record?.bmi_category"
                                    :class="{'bg-green-100 text-green-800': p.medical_record.bmi_category === 'Normal', 'bg-yellow-100 text-yellow-800': p.medical_record.bmi_category === 'Underweight', 'bg-orange-100 text-orange-800': p.medical_record.bmi_category === 'Overweight', 'bg-red-100 text-red-800': p.medical_record.bmi_category === 'Obese'}"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ p.medical_record.bmi }} - {{ p.medical_record.bmi_category }}
                                </span>
                                <span v-else class="text-xs text-gray-400">No record</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('personnel.show', p.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-[#0E4362] hover:text-white text-[#095D86] border border-[#095D86] transition-colors" title="View">
                                        View
                                    </Link>
                                    <Link :href="route('personnel.edit', p.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-[#F0A500] hover:text-white text-[#F0A500] border border-[#F0A500] transition-colors" title="Edit">
                                        Edit
                                    </Link>
                                    <button @click="deletePersonnel(p.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-red-600 hover:text-white text-red-500 border border-red-500 transition-colors" title="Delete">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!personnel.data?.length">
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">No personnel found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="personnel.links?.length > 3" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">Showing {{ personnel.from }} to {{ personnel.to }} of {{ personnel.total }}</p>
                <div class="flex gap-1">
                    <template v-for="link in personnel.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-navy-800 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors" v-html="link.label" />
                        <span v-else class="px-3 py-1 text-xs text-gray-300" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </div>
    </AuthenticatedLayout>
</template>
