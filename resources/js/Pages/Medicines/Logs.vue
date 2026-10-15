<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';

import { ref } from 'vue';

const props = defineProps({
    logs: Object,
    medicines: Array,
    filters: Object,
});

const medicineId =
    ref(
        props.filters
            ?.medicine_id || ''
    );

const patientType =
    ref(
        props.filters
            ?.patient_type || ''
    );

const dateFrom =
    ref(
        props.filters
            ?.date_from || ''
    );

const dateTo =
    ref(
        props.filters
            ?.date_to || ''
    );

const applyFilters = () => {

    router.get(
        route(
            'medicines.logs'
        ),
        {
            medicine_id:
                medicineId.value
                || undefined,

            patient_type:
                patientType.value
                || undefined,

            date_from:
                dateFrom.value
                || undefined,

            date_to:
                dateTo.value
                || undefined,
        },
        {
            preserveState:
                true,

            preserveScroll:
                true,

            replace:
                true,
        }
    );
};

const formatDate = value => {

    if (!value) {
        return '-';
    }

    return new Date(
        value
    ).toLocaleDateString(
        'en-US',
        {
            month:
                'short',

            day:
                'numeric',

            year:
                'numeric',
        }
    );
};

const formatTime = value => {

    if (!value) {
        return '-';
    }

    return new Date(
        value
    ).toLocaleTimeString(
        'en-US',
        {
            hour:
                'numeric',

            minute:
                '2-digit',
        }
    );
};
</script>

<template>

    <Head title="Medicine Log Book" />

    <AuthenticatedLayout>

        <template #header>
            Medicine Log Book
        </template>

        <div
            class="space-y-6"
        >

            <!-- Filters -->

            <div
                class="flex flex-col xl:flex-row xl:items-center justify-between gap-4"
            >

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 flex-1"
                >

                    <select
                        v-model="medicineId"
                        @change="applyFilters"
                        class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                    >

                        <option value="">
                            All Medicines
                        </option>

                        <option
                            v-for="medicine in medicines"
                            :key="medicine.id"
                            :value="medicine.id"
                        >
                            {{ medicine.name }}
                        </option>

                    </select>

                    <select
                        v-model="patientType"
                        @change="applyFilters"
                        class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                    >

                        <option value="">
                            All Types
                        </option>

                        <option value="student">
                            Students
                        </option>

                        <option value="personnel">
                            Personnel
                        </option>

                    </select>

                    <input
                        v-model="dateFrom"
                        @change="applyFilters"
                        type="date"
                        class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                    />

                    <input
                        v-model="dateTo"
                        @change="applyFilters"
                        type="date"
                        class="text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500"
                    />

                </div>

                <Link
                    :href="
                        route(
                            'medicines.index'
                        )
                    "
                    class="text-sm font-medium text-[#132639] hover:underline whitespace-nowrap"
                >
                    ← Back to Inventory
                </Link>

            </div>

            <!-- Table -->

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
            >

                <div
                    class="overflow-x-auto"
                >

                    <table
                        class="w-full text-sm"
                    >

                        <thead>

                            <tr
                                class="bg-navy-800 text-white"
                            >

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Date
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Medicine
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Supplier
                                </th>

                                <th
                                    class="px-4 py-3 text-center text-xs uppercase"
                                >
                                    Qty
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Patient
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Type
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Dispensed By
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Notes
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs uppercase"
                                >
                                    Time
                                </th>

                            </tr>

                        </thead>

                        <tbody
                            class="divide-y divide-gray-100"
                        >

                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="hover:bg-gray-50"
                            >

                                <td
                                    class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap"
                                >
                                    {{
                                        formatDate(
                                            log.dispensed_at
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 font-medium text-gray-800"
                                >
                                    {{
                                        log.medicine
                                            ?.name
                                            || '-'
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-xs text-gray-600"
                                >
                                    {{
                                        log.medicine
                                            ?.supplier
                                            || '-'
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-center font-semibold"
                                >
                                    {{ log.quantity }}
                                </td>

                                <td
                                    class="px-4 py-3 text-gray-700"
                                >
                                    {{ log.patient_name }}
                                </td>

                                <td
                                    class="px-4 py-3 text-xs"
                                >
                                    {{
                                        log.patient_type_label
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-xs text-gray-600"
                                >
                                    {{
                                        log.dispenser
                                            ?.name
                                            || '-'
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-xs text-gray-500"
                                >
                                    {{ log.notes || '-' }}
                                </td>

                                <td
                                    class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap"
                                >
                                    {{
                                        formatTime(
                                            log.dispensed_at
                                        )
                                    }}
                                </td>

                            </tr>

                            <tr
                                v-if="
                                    !logs.data
                                        ?.length
                                "
                            >

                                <td
                                    colspan="9"
                                    class="py-14 text-center text-gray-400"
                                >
                                    No dispensing records
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- Pagination -->

                <div
                    v-if="
                        logs.links
                            ?.length > 3
                    "
                    class="px-4 py-3 border-t border-gray-100 flex justify-end gap-1"
                >

                    <template
                        v-for="link in logs.links"
                        :key="link.label"
                    >

                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="
                                link.active
                                    ? 'bg-navy-800 text-white'
                                    : 'bg-gray-50 text-gray-600'
                            "
                            class="px-3 py-1.5 rounded-lg text-xs"
                            v-html="link.label"
                        />

                    </template>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>