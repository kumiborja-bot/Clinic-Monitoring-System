<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    departments: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');

const applyFilters = () => {
    router.get(route('departments.index'), {
        search: search.value || undefined,
        type: type.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

const deleteDepartment = (id) => {
    if (confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
        router.delete(route('departments.destroy', id), {
            onError: (errors) => {
                alert(errors.error || 'An error occurred.');
            }
        });
    }
};
</script>

<template>
    <Head :title="filters.type === 'student' ? 'Student Departments' : (filters.type === 'personnel' ? 'Personnel Departments' : 'Departments')" />
    <AuthenticatedLayout>
        <template #header>
            {{ filters.type === 'student' ? 'Student Department Master' : (filters.type === 'personnel' ? 'Personnel Department Master' : 'Department Master') }}
        </template>

        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Search departments..." class="pl-10 pr-4 py-2 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 w-64" />
                </div>
                <select v-model="type" @change="applyFilters" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                    <option value="">All Types</option>
                    <option value="student">Student (Academic)</option>
                    <option value="personnel">Personnel (Administrative)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('master-data.index')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Master Data
                </Link>
                <Link :href="route('departments.create', { type: filters.type })" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#F0A500] hover:bg-[#F4B842] shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Department
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-navy-800 text-white">
                                <th class="px-6 py-3.5 text-left font-semibold text-xs uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3.5 text-left font-semibold text-xs uppercase tracking-wider">Abbreviation / Name</th>
                                <th class="px-6 py-3.5 text-left font-semibold text-xs uppercase tracking-wider">Full Name</th>
                                <th class="px-6 py-3.5 text-left font-semibold text-xs uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3.5 text-center font-semibold text-xs uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="dept in departments.data" :key="dept.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-sm font-bold text-navy-800">{{ dept.code }}</td>
                                <td class="px-6 py-4 font-medium text-gray-800">{{ dept.name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ dept.full_name }}</td>
                                <td class="px-6 py-4">
                                    <span :class="dept.type === 'student' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-blue-50 text-blue-700 border-blue-100'" class="px-2.5 py-1 rounded-full text-xs font-semibold border">
                                        {{ dept.type === 'student' ? 'Student' : 'Personnel' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link :href="route('departments.edit', dept.id)" class="px-3 py-1.5 text-xs font-semibold rounded-lg hover:bg-[#F0A500] hover:text-white text-[#F0A500] border border-[#F0A500] transition-colors" title="Edit">
                                            Edit
                                        </Link>
                                        <button @click="deleteDepartment(dept.id)" class="px-3 py-1.5 text-xs font-semibold rounded-lg hover:bg-red-600 hover:text-white text-red-500 border border-red-500 transition-colors" title="Delete">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!departments.data?.length">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <p class="text-sm font-semibold text-gray-500">No departments found</p>
                                        <p class="text-xs text-gray-400 mt-1">Try refining your search query or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="departments.links?.length > 3" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <p class="text-xs text-gray-500">Showing {{ departments.from }} to {{ departments.to }} of {{ departments.total }} departments</p>
                    <div class="flex gap-1">
                        <template v-for="link in departments.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-navy-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors" v-html="link.label" />
                            <span v-else class="px-3 py-1 text-xs text-gray-300 border border-gray-100 bg-gray-50 rounded-lg cursor-not-allowed" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
