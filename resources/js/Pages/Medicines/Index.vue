<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import { formatDate } from '@/utils/dateFormat';

Chart.register(...registerables);

const props = defineProps({
    medicines: Object,
    categories: Array,
    topDispensed: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || '');
const chartRef = ref(null);

const applyFilters = () => {
    router.get(route('medicines.index'), { search: search.value || undefined, category: category.value || undefined }, { preserveState: true, preserveScroll: true });
};

let t;
watch(search, () => { clearTimeout(t); t = setTimeout(applyFilters, 400); });

onMounted(() => {
    if (props.topDispensed?.length && chartRef.value) {
        new Chart(chartRef.value, {
            type: 'bar',
            data: {
                labels: props.topDispensed.map(m => m.medicine?.name || 'Unknown'),
                datasets: [{ label: 'Dispensed', data: props.topDispensed.map(m => m.total), backgroundColor: '#d4a017', borderRadius: 6 }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    }
});

const deleteMedicine = (id) => { if (confirm('Delete this medicine?')) router.delete(route('medicines.destroy', id)); };

const stockColor = (m) => {
    if (m.stock_quantity === 0) return 'bg-red-100 text-red-700';
    if (m.stock_quantity <= (m.reorder_level || 10)) return 'bg-amber-100 text-amber-700';
    return 'bg-green-100 text-green-700';
};
</script>

<template>
    <Head title="Dispensing Log" />
    <AuthenticatedLayout>
        <template #header>Dispensing Log</template>

        <!-- Top Actions -->
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Search medicine..." class="pl-10 pr-4 py-2 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 w-64" />
                </div>
                <select v-model="category" @change="applyFilters" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                    <option value="">All Categories</option>
                    <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Link :href="route('medicines.logs')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#095D86] hover:bg-[#0E4362] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Medicine Log Book
                </Link>
                <Link :href="route('medicines.dispense.form')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all bg-[#F0A500] hover:bg-[#F4B842]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Dispense
                </Link>
                <Link :href="route('medicines.create')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all bg-[#132639] hover:bg-[#0E4362]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Medicine
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Medicine Table -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-navy-800 text-white">
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Medicine</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Category</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Stock</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Unit</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Expiry</th>
                                <th class="px-4 py-3 text-center font-medium text-xs uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="m in medicines.data" :key="m.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ m.name }}</p>
                                    <p v-if="m.description" class="text-xs text-gray-400 truncate max-w-xs">{{ m.description }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ m.category || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="stockColor(m)" class="px-2 py-0.5 rounded-full text-xs font-bold">{{ m.stock_quantity }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ m.unit }}</td>
                                <td class="px-4 py-3 text-xs" :class="m.expiry_date && new Date(m.expiry_date) < new Date() ? 'text-red-500 font-bold' : 'text-gray-500'">{{ m.expiry_date ? formatDate(m.expiry_date) : '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route('medicines.edit', m.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-[#F0A500] hover:text-white text-[#F0A500] border border-[#F0A500] transition-colors" title="Edit">
                                            Edit
                                        </Link>
                                        <button @click="deleteMedicine(m.id)" class="px-2 py-1 text-xs font-semibold rounded hover:bg-red-600 hover:text-white text-red-500 border border-red-500 transition-colors" title="Delete">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!medicines.data?.length"><td colspan="6" class="px-4 py-12 text-center text-gray-400">No medicines found</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="medicines.links?.length > 3" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ medicines.from }}–{{ medicines.to }} of {{ medicines.total }}</p>
                    <div class="flex gap-1">
                        <template v-for="link in medicines.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="link.active ? 'bg-navy-800 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Most Dispensed Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-mustard-500"></span>
                    Most Dispensed
                </h3>
                <div class="h-64" v-if="topDispensed?.length"><canvas ref="chartRef"></canvas></div>
                <div v-else class="h-64 flex items-center justify-center text-gray-400 text-sm">No dispensing data</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
