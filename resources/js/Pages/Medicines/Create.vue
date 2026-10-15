<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useOfflineQueue } from '@/utils/offlineQueue';

const props = defineProps({ medicine: { type: Object, default: null } });
const isEdit = computed(() => !!props.medicine);
const today = new Date();
const localToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

const form = useForm({
    name: props.medicine?.name || '',
    description: props.medicine?.description || '',
    supplier: props.medicine?.supplier || '',
    unit: props.medicine?.unit || '',
    stock_quantity: props.medicine?.stock_quantity ?? 0,
    initial_stock_date: localToday,
    reorder_level: props.medicine?.reorder_level ?? 10,
    expiry_date: props.medicine?.expiry_date ? props.medicine.expiry_date.split('T')[0] : '',
});

const { isOnline, addToQueue } = useOfflineQueue();
const offlineQueued = ref(false);

const submit = async () => {
    if (isEdit.value) {
        form.put(route('medicines.update', props.medicine.id));
    } else if (isOnline.value) {
        form.post(route('medicines.store'));
    } else {
        await addToQueue('medicine', route('medicines.store'), form.data());
        offlineQueued.value = true;
        form.reset();
        setTimeout(() => { offlineQueued.value = false; }, 4000);
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Medicine' : 'Add Medicine'" />
    <AuthenticatedLayout>
        <template #header>{{ isEdit ? 'Edit Medicine' : 'Add New Medicine' }}</template>

        <!-- Offline queued success message -->
        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <div v-if="offlineQueued" class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200/60 flex items-center gap-3 shadow-sm">
                <div class="w-9 h-9 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-700">Medicine queued offline</p>
                    <p class="text-xs text-amber-500/80">It will sync automatically when you reconnect.</p>
                </div>
            </div>
        </transition>

        <form @submit.prevent="submit">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Medicine Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Medicine Name *</label>
                        <input v-model="form.name" type="text" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                        <textarea v-model="form.description" rows="2" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Supplier *</label>
                            <input v-model="form.supplier" type="text" required maxlength="255" placeholder="Supplier name" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                            <p v-if="form.errors.supplier" class="text-xs text-red-500 mt-1">{{ form.errors.supplier }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Unit *</label>
                            <select v-model="form.unit" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                <option value="">Select</option>
                                <option value="tablet">Tablet</option>
                                <option value="capsule">Capsule</option>
                                <option value="bottle">Bottle</option>
                                <option value="sachet">Sachet</option>
                                <option value="ml">ml</option>
                                <option value="piece">Piece</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ isEdit ? 'Current Stock' : 'Initial Stock Quantity *' }}</label>
                            <input v-model="form.stock_quantity" type="number" min="0" required :disabled="isEdit" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 disabled:bg-gray-100" />
                            <p v-if="isEdit" class="text-xs text-gray-500 mt-1">Use Stock In or Dispense to change the stock balance.</p>
                            <p v-if="form.errors.stock_quantity" class="text-xs text-red-500 mt-1">{{ form.errors.stock_quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Reorder Level</label>
                            <input v-model="form.reorder_level" type="number" min="0" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Expiry Date</label>
                            <input v-model="form.expiry_date" type="date" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        </div>
                    </div>
                    <div v-if="!isEdit">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Initial Stock Date *</label>
                        <input v-model="form.initial_stock_date" type="date" required :max="localToday" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        <p v-if="form.errors.initial_stock_date" class="text-xs text-red-500 mt-1">{{ form.errors.initial_stock_date }}</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <a :href="route('medicines.index')" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</a>
                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                    {{ form.processing ? 'Saving...' : (isEdit ? 'Update Medicine' : (isOnline ? 'Add Medicine' : '📥 Queue Offline')) }}
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
