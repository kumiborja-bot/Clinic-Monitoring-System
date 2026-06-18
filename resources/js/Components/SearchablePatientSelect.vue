<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    patients: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    modelValue: { type: [String, Number], default: '' },
    patientType: { type: String, default: 'student' },
    placeholder: { type: String, default: 'Search and select patient...' },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const departmentFilter = ref('');
const containerRef = ref(null);
const searchInputRef = ref(null);

// Find the currently selected patient for display
const selectedPatient = computed(() => {
    if (!props.modelValue) return null;
    return props.patients.find(p => p.id == props.modelValue);
});

// Display text for the selected patient
const selectedDisplayText = computed(() => {
    if (!selectedPatient.value) return '';
    const p = selectedPatient.value;
    const idLabel = p.student_id_number || p.employee_id || '';
    const dept = p.department?.name || '';
    return `${p.name} (${idLabel})${dept ? ' — ' + dept : ''}`;
});

// Filter departments by patient type
const filteredDepartments = computed(() => {
    return props.departments.filter(d => !d.type || d.type === props.patientType);
});

// Filter patients by search query and department
const filteredPatients = computed(() => {
    let list = props.patients;

    if (departmentFilter.value) {
        list = list.filter(p => p.department_id == departmentFilter.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(p => {
            const name = (p.name || '').toLowerCase();
            const idNum = (p.student_id_number || p.employee_id || '').toLowerCase();
            const dept = (p.department?.name || '').toLowerCase();
            return name.includes(q) || idNum.includes(q) || dept.includes(q);
        });
    }

    return list;
});

function openDropdown() {
    isOpen.value = true;
    searchQuery.value = '';
    setTimeout(() => searchInputRef.value?.focus(), 50);
}

function closeDropdown() {
    isOpen.value = false;
    searchQuery.value = '';
}

function selectPatient(patient) {
    emit('update:modelValue', patient.id);
    closeDropdown();
}

function clearSelection() {
    emit('update:modelValue', '');
    departmentFilter.value = '';
    searchQuery.value = '';
}

// Close dropdown when clicking outside
function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        closeDropdown();
    }
}

// Reset department filter when patient type changes
watch(() => props.patientType, () => {
    departmentFilter.value = '';
    searchQuery.value = '';
});

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="containerRef" class="searchable-patient-select">
        <!-- Department Filter Row -->
        <div class="flex items-center gap-2 mb-2">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-3.5 h-3.5 mr-0.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Filter by Department
                </label>
                <select
                    v-model="departmentFilter"
                    class="w-full text-xs rounded-lg border-gray-200 focus:ring-mustard-500 focus:border-mustard-500 bg-gray-50"
                >
                    <option value="">All Departments</option>
                    <option v-for="dept in filteredDepartments" :key="dept.id" :value="dept.id">
                        {{ dept.name }} ({{ dept.code }})
                    </option>
                </select>
            </div>
        </div>

        <!-- Trigger / Display -->
        <div
            class="select-trigger"
            :class="{ 'is-open': isOpen, 'has-value': !!modelValue }"
            @click="openDropdown"
        >
            <div v-if="selectedPatient" class="flex items-center justify-between w-full">
                <span class="selected-text truncate">{{ selectedDisplayText }}</span>
                <button
                    type="button"
                    class="clear-btn"
                    @click.stop="clearSelection"
                    title="Clear selection"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <span v-else class="placeholder-text">{{ placeholder }}</span>
            <svg v-if="!selectedPatient" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
        </div>

        <!-- Dropdown -->
        <div v-if="isOpen" class="dropdown-panel">
            <!-- Search Input -->
            <div class="search-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input
                    ref="searchInputRef"
                    v-model="searchQuery"
                    type="text"
                    class="search-input"
                    :placeholder="'Search by name, ID, or department...'"
                    @keydown.esc="closeDropdown"
                />
            </div>

            <!-- Options List -->
            <ul class="options-list">
                <li
                    v-for="p in filteredPatients"
                    :key="p.id"
                    class="option-item"
                    :class="{ 'is-selected': p.id == modelValue }"
                    @click="selectPatient(p)"
                >
                    <div class="option-main">
                        <span class="option-name">{{ p.name }}</span>
                        <span class="option-id">{{ p.student_id_number || p.employee_id }}</span>
                    </div>
                    <span v-if="p.department" class="option-dept">{{ p.department.name }}</span>
                </li>
                <li v-if="filteredPatients.length === 0" class="no-results">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    No patients found
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.searchable-patient-select {
    position: relative;
}

.select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-height: 38px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s ease;
}

.select-trigger:hover {
    border-color: #d1d5db;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.select-trigger.is-open {
    border-color: #d4a017;
    box-shadow: 0 0 0 3px rgba(212,160,23,0.12);
}

.select-trigger.has-value {
    border-color: #d4a017;
    background: #fffdf5;
}

.selected-text {
    font-weight: 500;
    color: #1f2937;
}

.placeholder-text {
    color: #9ca3af;
}

.chevron-icon {
    width: 1rem;
    height: 1rem;
    color: #9ca3af;
    flex-shrink: 0;
}

.clear-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 9999px;
    color: #9ca3af;
    transition: all 0.15s;
    flex-shrink: 0;
}

.clear-btn:hover {
    background: #fee2e2;
    color: #ef4444;
}

.dropdown-panel {
    position: absolute;
    z-index: 50;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    overflow: hidden;
    animation: dropdownSlide 0.15s ease-out;
}

@keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.75rem;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.search-icon {
    width: 1rem;
    height: 1rem;
    color: #9ca3af;
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.8125rem;
    color: #374151;
    padding: 0;
}

.search-input::placeholder {
    color: #d1d5db;
}

.search-input:focus {
    box-shadow: none;
    outline: none;
}

.options-list {
    max-height: 220px;
    overflow-y: auto;
    padding: 0.25rem 0;
    list-style: none;
    margin: 0;
}

.option-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background 0.1s;
}

.option-item:hover {
    background: #fffbeb;
}

.option-item.is-selected {
    background: #fef3c7;
}

.option-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.option-name {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.option-id {
    font-size: 0.6875rem;
    color: #6b7280;
    margin-top: 1px;
}

.option-dept {
    font-size: 0.6875rem;
    font-weight: 500;
    color: #92400e;
    background: #fef3c7;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    white-space: nowrap;
    flex-shrink: 0;
    margin-left: 0.5rem;
}

.option-item.is-selected .option-dept {
    background: #fde68a;
}

.no-results {
    padding: 1.25rem 0.75rem;
    text-align: center;
    font-size: 0.75rem;
    color: #9ca3af;
}

.options-list::-webkit-scrollbar {
    width: 6px;
}

.options-list::-webkit-scrollbar-track {
    background: transparent;
}

.options-list::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 3px;
}

.options-list::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
