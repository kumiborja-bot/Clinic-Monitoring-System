<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    department: Object,
});

const form = useForm({
    code: props.department.code || '',
    name: props.department.name || '',
    full_name: props.department.full_name || '',
    type: props.department.type || 'student',
});

const submit = () => {
    form.put(route('departments.update', props.department.id));
};
</script>

<template>
    <Head title="Edit Department" />
    <AuthenticatedLayout>
        <template #header>Edit Department</template>

        <form @submit.prevent="submit" class="max-w-3xl">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-navy-800">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Department Details</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Department Code *</label>
                            <input v-model="form.code" type="text" required placeholder="e.g. BSIT" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                            <p v-if="form.errors.code" class="text-xs text-red-500 mt-1">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Department Type *</label>
                            <select v-model="form.type" required class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500">
                                <option value="student">Student (Academic)</option>
                                <option value="personnel">Personnel (Administrative)</option>
                            </select>
                            <p v-if="form.errors.type" class="text-xs text-red-500 mt-1">{{ form.errors.type }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Department Name *</label>
                        <input v-model="form.name" type="text" required placeholder="e.g. College of Computing and Library Studies" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Full Name *</label>
                        <input v-model="form.full_name" type="text" required placeholder="e.g. College of Computing and Library Studies (CCLS)" class="w-full text-sm rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500" />
                        <p v-if="form.errors.full_name" class="text-xs text-red-500 mt-1">{{ form.errors.full_name }}</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <Link :href="route('departments.index', { type: props.department.type })" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</Link>
                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-sm font-bold text-white rounded-lg shadow-sm hover:shadow-md disabled:opacity-50 transition-all bg-[#132639] hover:bg-[#0E4362]">
                    {{ form.processing ? 'Saving...' : 'Update Department' }}
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
