<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchablePatientSelect from '@/Components/SearchablePatientSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useOfflineQueue } from '@/utils/offlineQueue';

const props = defineProps({
    medicines: Array,
    students: Array,
    personnel: Array,
    departments: Array,
});

const {
    isOnline,
    addToQueue,
} = useOfflineQueue();

const offlineQueued =
    ref(false);

const form = useForm({
    medicine_id: '',
    patient_type: 'student',
    patient_id: '',
    quantity: 1,
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

const patientList =
    computed(() =>
        form.patient_type === 'student'
            ? props.students
            : props.personnel
    );

const submit = async () => {

    if (isOnline.value) {

        form.post(
            route(
                'medicines.dispense'
            )
        );

        return;
    }

    await addToQueue(
        'medicine_dispense',
        route(
            'medicines.dispense'
        ),
        form.data()
    );

    offlineQueued.value =
        true;

    form.reset();

    setTimeout(() => {
        offlineQueued.value =
            false;
    }, 4000);
};
</script>

<template>

    <Head title="Dispense Medicine" />

    <AuthenticatedLayout>

        <template #header>
            Dispense Medicine
        </template>

        <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >

            <div
                v-if="offlineQueued"
                class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200/60 flex items-center gap-3 shadow-sm"
            >

                <div
                    class="w-9 h-9 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0"
                >
                    <svg
                        class="w-5 h-5 text-amber-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <div>

                    <p
                        class="text-sm font-semibold text-amber-700"
                    >
                        Medicine dispense queued offline
                    </p>

                    <p
                        class="text-xs text-amber-500/80"
                    >
                        It will sync automatically when you reconnect.
                    </p>

                </div>

            </div>

        </transition>

        <form
            @submit.prevent="submit"
            class="w-full"
        >

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
            >

                <div
                    class="px-6 py-4 border-b border-gray-100 bg-mustard-500"
                >

                    <h3
                        class="text-sm font-bold text-navy-900 uppercase tracking-wider"
                    >
                        Dispense Form
                    </h3>

                </div>

                <div
                    class="p-6 space-y-4"
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
                                -
                                {{ medicine.supplier || 'No Supplier' }}
                                ({{ medicine.stock_quantity }} {{ medicine.unit }} available)
                            </option>

                        </select>

                    </div>

                    <!-- Selected Medicine -->

                    <div
                        v-if="selectedMedicine"
                        class="p-4 rounded-lg bg-blue-50 border border-blue-100"
                    >

                        <p
                            class="font-semibold text-blue-800 text-sm"
                        >
                            {{ selectedMedicine.name }}
                        </p>

                        <p
                            class="text-blue-600 text-xs mt-1"
                        >
                            Supplier:
                            <strong>
                                {{ selectedMedicine.supplier || '-' }}
                            </strong>
                        </p>

                        <p
                            class="text-blue-600 text-xs mt-1"
                        >
                            Available:
                            <strong>
                                {{ selectedMedicine.stock_quantity }}
                                {{ selectedMedicine.unit }}
                            </strong>
                        </p>

                    </div>

                    <!-- Patient -->

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                    >

                        <div>

                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1"
                            >
                                Patient Type *
                            </label>

                            <select
                                v-model="form.patient_type"
                                @change="form.patient_id = ''"
                                class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                            >

                                <option value="student">
                                    Student
                                </option>

                                <option value="personnel">
                                    Personnel
                                </option>

                            </select>

                        </div>

                        <div>

                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1"
                            >
                                Patient *
                            </label>

                            <SearchablePatientSelect
                                v-model="form.patient_id"
                                :patients="patientList"
                                :departments="departments"
                                :patient-type="form.patient_type"
                            />

                        </div>

                    </div>

                    <!-- Quantity -->

                    <div>

                        <label
                            class="block text-xs font-semibold text-gray-600 mb-1"
                        >
                            Quantity *
                        </label>

                        <input
                            v-model.number="form.quantity"
                            type="number"
                            min="1"
                            :max="
                                selectedMedicine
                                    ?.stock_quantity
                                    || 9999
                            "
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
                            class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                        ></textarea>

                    </div>

                </div>

            </div>

            <div
                class="flex items-center justify-end gap-3 mt-6"
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
                    class="px-6 py-2.5 text-sm font-bold rounded-lg shadow-sm bg-[#F0A500] hover:bg-[#F4B842] text-[#132639] disabled:opacity-50"
                >
                    {{
                        form.processing
                            ? 'Dispensing...'
                            : (
                                isOnline
                                    ? 'Dispense Medicine'
                                    : '📥 Queue Offline'
                            )
                    }}
                </button>

            </div>

        </form>

    </AuthenticatedLayout>

</template>