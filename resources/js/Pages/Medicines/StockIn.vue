<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    computed,
    watch,
} from 'vue';

const props = defineProps({
    medicines: Array,

    selectedMedicineId: {
        type: [
            String,
            Number,
        ],

        default: '',
    },
});

function localToday() {

    const date =
        new Date();

    const year =
        date.getFullYear();

    const month =
        String(
            date.getMonth() + 1
        ).padStart(2, '0');

    const day =
        String(
            date.getDate()
        ).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const form = useForm({
    medicine_id:
        props.selectedMedicineId || '',

    supplier: '',

    quantity: 1,

    transaction_date:
        localToday(),

    notes: '',
});

const selectedMedicine =
    computed(() =>
        props.medicines.find(
            medicine =>
                medicine.id
                == form.medicine_id
        )
    );

watch(
    selectedMedicine,
    medicine => {

        if (medicine) {
            form.supplier =
                medicine.supplier || '';
        }
    },
    {
        immediate: true,
    }
);

const submit = () => {

    form.post(
        route(
            'medicines.stock-in'
        )
    );
};
</script>

<template>

    <Head title="Stock In Medicine" />

    <AuthenticatedLayout>

        <template #header>
            Stock In Medicine
        </template>

        <form
            @submit.prevent="submit"
        >

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
            >

                <div
                    class="px-6 py-4 bg-[#132639]"
                >

                    <h3
                        class="text-sm font-bold text-white uppercase tracking-wider"
                    >
                        Receive Medicine Stock
                    </h3>

                    <p
                        class="text-xs text-gray-300 mt-1"
                    >
                        Medicines entered here will appear in the IN column of the Stock Card.
                    </p>

                </div>

                <div
                    class="p-6 space-y-5"
                >

                    <!-- Medicine -->

                    <div>

                        <label
                            class="block text-xs font-semibold text-gray-600 mb-1"
                        >
                            Medicine *
                        </label>

                        <select
                            v-model="form.medicine_id"
                            required
                            class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                        >

                            <option value="">
                                Select Medicine
                            </option>

                            <option
                                v-for="medicine in medicines"
                                :key="medicine.id"
                                :value="medicine.id"
                            >
                                {{ medicine.name }}
                                ({{ medicine.stock_quantity }} {{ medicine.unit }} remaining)
                            </option>

                        </select>

                        <p
                            v-if="form.errors.medicine_id"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.medicine_id }}
                        </p>

                    </div>

                    <!-- Current Stock -->

                    <div
                        v-if="selectedMedicine"
                        class="p-4 rounded-lg bg-blue-50 border border-blue-100"
                    >

                        <p
                            class="text-sm font-semibold text-blue-800"
                        >
                            {{ selectedMedicine.name }}
                        </p>

                        <p
                            class="text-xs text-blue-600 mt-1"
                        >
                            Current Stock:
                            <strong>
                                {{ selectedMedicine.stock_quantity }}
                                {{ selectedMedicine.unit }}
                            </strong>
                        </p>

                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-4"
                    >

                        <!-- Supplier -->

                        <div>

                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1"
                            >
                                Supplier *
                            </label>

                            <input
                                v-model="form.supplier"
                                type="text"
                                required
                                placeholder="e.g., C&C"
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                            />

                            <p
                                v-if="form.errors.supplier"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.supplier }}
                            </p>

                        </div>

                        <!-- Quantity -->

                        <div>

                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1"
                            >
                                Quantity Received *
                            </label>

                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="1"
                                required
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                            />

                            <p
                                v-if="form.errors.quantity"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.quantity }}
                            </p>

                        </div>

                        <!-- Date -->

                        <div>

                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1"
                            >
                                Date Received *
                            </label>

                            <input
                                v-model="form.transaction_date"
                                type="date"
                                required
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                            />

                            <p
                                v-if="form.errors.transaction_date"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.transaction_date }}
                            </p>

                        </div>

                    </div>

                    <!-- Notes -->

                    <div>

                        <label
                            class="block text-xs font-semibold text-gray-600 mb-1"
                        >
                            Notes
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Optional notes..."
                            class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                        ></textarea>

                    </div>

                </div>

            </div>

            <div
                class="flex justify-end gap-3 mt-6"
            >

                <a
                    :href="route('medicines.index')"
                    class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2.5 text-sm font-bold rounded-lg bg-[#F0A500] hover:bg-[#F4B842] text-[#132639] disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Adding Stock...'
                            : 'Add Stock'
                    }}
                </button>

            </div>

        </form>

    </AuthenticatedLayout>

</template>