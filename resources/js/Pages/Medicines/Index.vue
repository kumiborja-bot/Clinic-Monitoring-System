<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import { formatDate } from '@/utils/dateFormat';

Chart.register(...registerables);

const props = defineProps({
    medicines: Object,
    suppliers: Array,
    topDispensed: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const supplier = ref(props.filters?.supplier || '');
const chartRef = ref(null);
const selectedIds = ref([]);
const selectionMode = ref(false);
const deleting = ref(false);
const deleteMessage = ref('');
const deleteError = ref('');
const allOnPageSelected = computed(() => medicinesOnPage.value.length > 0 && selectedIds.value.length === medicinesOnPage.value.length);
const medicinesOnPage = computed(() => props.medicines.data || []);

watch(() => props.medicines, () => { selectedIds.value = []; });

const togglePageSelection = (event) => {
    selectedIds.value = event.target.checked ? medicinesOnPage.value.map(m => m.id) : [];
};

const cancelSelection = () => {
    selectionMode.value = false;
    selectedIds.value = [];
};

const toggleMedicine = (id) => {
    if (!selectionMode.value || deleting.value) return;
    selectedIds.value = selectedIds.value.includes(id)
        ? selectedIds.value.filter(selectedId => selectedId !== id)
        : [...selectedIds.value, id];
};

const bulkDelete = (all = false) => {
    if (deleting.value || (!all && !selectedIds.value.length)) return;
    const count = selectedIds.value.length;
    const message = all
        ? 'Delete ALL medicines in the entire inventory, including medicines on other pages and hidden by filters? Their dispensing logs and stock cards will also be deleted. This cannot be undone.'
        : `Delete ${count} selected medicine(s), including their dispensing logs and stock cards? This cannot be undone.`;
    if (!confirm(message)) return;

    deleting.value = true;
    deleteError.value = '';
    deleteMessage.value = '';
    router.delete(route(all ? 'medicines.delete-all' : 'medicines.bulk-delete'), {
        data: all ? {} : { ids: [...selectedIds.value] },
        preserveScroll: true,
        onSuccess: () => {
            cancelSelection();
            deleteMessage.value = all ? 'All medicines deleted.' : `${count} medicine(s) deleted.`;
        },
        onError: errors => { deleteError.value = Object.values(errors).flat().join(' ') || 'Unable to delete medicines. Please try again.'; },
        onFinish: () => { deleting.value = false; },
    });
};

const applyFilters = () => {
    router.get(route('medicines.index'), { search: search.value || undefined, supplier: supplier.value || undefined }, { preserveState: true, preserveScroll: true });
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
    <Head title="Medicine & Stocks Logbook" />
    <AuthenticatedLayout>
        <template #header>Medicine & Stocks Logbook</template>

        <!-- Top Actions -->
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input v-model="search" type="text" placeholder="Search medicine..." class="pl-10 pr-4 py-2 text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 w-64" />
                </div>
                <select v-model="supplier" @change="applyFilters" class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                    <option value="">All Suppliers</option>
                    <option v-for="c in suppliers" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Link :href="route('medicines.stock-in.form')" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#095D86] hover:bg-[#0E4362]">
                    Stock In
                </Link>
                <Link :href="route('medicines.logs')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#095D86] hover:bg-[#0E4362] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Medicine & Stock Log Book
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

        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_260px] gap-6 items-start">
            <!-- Medicine Table -->
            <div class="min-w-0 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3" :class="selectionMode ? 'bg-slate-50' : 'bg-white'">
                    <div class="text-sm font-semibold text-slate-700" aria-live="polite">
                        {{ selectionMode ? `${selectedIds.length} selected` : 'Medicine inventory' }}
                        <span v-if="selectionMode" class="block text-xs font-normal text-slate-500 mt-0.5">Choose medicines on this page.</span>
                    </div>
                    <button v-if="!selectionMode" type="button" :disabled="!medicinesOnPage.length" @click="selectionMode = true" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-40">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="4" /><path d="m8 12 3 3 5-6" /></svg>
                        Select
                    </button>
                    <div v-else class="flex flex-wrap items-center gap-2">
                        <button type="button" :disabled="deleting" @click="bulkDelete(true)" class="px-3 py-2 text-xs font-medium rounded-lg text-red-600 hover:bg-red-50 disabled:opacity-50">
                            Delete All Medicines
                        </button>
                        <button type="button" :disabled="!selectedIds.length || deleting" @click="bulkDelete(false)" class="px-3 py-2 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ deleting ? 'Deleting…' : `Delete Selected (${selectedIds.length})` }}
                        </button>
                        <button type="button" :disabled="deleting" @click="cancelSelection" class="px-3 py-2 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-50">
                            Cancel
                        </button>
                    </div>
                </div>
                <p v-if="deleteMessage" role="status" class="px-4 py-3 text-sm text-green-700 bg-green-50">{{ deleteMessage }}</p>
                <p v-if="deleteError" role="alert" class="px-4 py-3 text-sm text-red-700 bg-red-50">{{ deleteError }}</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-navy-800 text-white">
                                <th v-if="selectionMode" class="px-3 py-3 w-10">
                                    <input type="checkbox" aria-label="Select all medicines on this page" :checked="allOnPageSelected" :indeterminate="selectedIds.length > 0 && !allOnPageSelected" :disabled="deleting || !medicinesOnPage.length" @change="togglePageSelection" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Medicine</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Stock</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Unit</th>
                                <th class="px-4 py-3 text-left font-medium text-xs uppercase">Expiry</th>
                                <th v-if="!selectionMode" class="px-4 py-3 text-center font-medium text-xs uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="m in medicines.data" :key="m.id" @click="toggleMedicine(m.id)" class="transition-colors" :class="[selectionMode ? 'cursor-pointer' : '', selectedIds.includes(m.id) ? 'bg-blue-50 hover:bg-blue-100/60' : 'hover:bg-gray-50/50']">
                                <td v-if="selectionMode" class="px-3 py-3" @click.stop>
                                    <input v-model="selectedIds" :value="m.id" type="checkbox" :aria-label="`Select ${m.name}`" :disabled="deleting" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ m.name }}</p>
                                    <p v-if="m.description" class="text-xs text-gray-400 truncate max-w-xs">{{ m.description }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ m.supplier || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="stockColor(m)" class="px-2 py-0.5 rounded-full text-xs font-bold">{{ m.stock_quantity }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ m.unit }}</td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap" :class="m.expiry_date && new Date(m.expiry_date) < new Date() ? 'text-red-500 font-bold' : 'text-gray-500'">{{ m.expiry_date ? formatDate(m.expiry_date) : '-' }}</td>
                                <td v-if="!selectionMode" class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1 whitespace-nowrap">
                                        <Link :href="route('medicines.stock-card', m.id)" class="px-2.5 py-1.5 text-xs font-semibold rounded-md bg-sky-50 text-[#095D86] hover:bg-sky-100">
                                            Stock Card
                                        </Link>
                                        <Link :href="route('medicines.edit', m.id)" class="px-2.5 py-1.5 text-xs font-medium rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-800" title="Edit">
                                            Edit
                                        </Link>
                                        <button @click="deleteMedicine(m.id)" class="p-2 rounded-md text-slate-400 hover:bg-red-50 hover:text-red-600" :aria-label="`Delete ${m.name}`" title="Delete">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 10v7M14 10v7" /></svg>
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
