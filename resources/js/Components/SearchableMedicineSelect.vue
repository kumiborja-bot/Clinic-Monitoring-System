<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    medicines: { type: Array, default: () => [] },
    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: 'Search medicine in stock...' },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const categoryFilter = ref('');
const containerRef = ref(null);
const searchInputRef = ref(null);

// Extract unique categories from medicines in stock
const categories = computed(() => {
    const set = new Set();
    props.medicines.forEach(m => {
        if (m.category && Number(m.stock_quantity) > 0) {
            set.add(m.category);
        }
    });
    return Array.from(set).sort();
});

// Find currently selected medicine for display
const selectedMedicine = computed(() => {
    if (!props.modelValue) return null;
    return props.medicines.find(m => m.id == props.modelValue);
});

// Display text for trigger button
const selectedDisplayText = computed(() => {
    if (!selectedMedicine.value) return '';
    const m = selectedMedicine.value;
    return `${m.name} (${m.stock_quantity} ${m.unit || 'pcs'} in stock)${m.category ? ' - ' + m.category : ''}`;
});

// Filter medicines strictly by stock > 0, category, and search query
const filteredMedicines = computed(() => {
    // Exclude out of stock medicines
    let list = props.medicines.filter(m => Number(m.stock_quantity) > 0);

    if (categoryFilter.value) {
        list = list.filter(m => m.category === categoryFilter.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(m => {
            const name = (m.name || '').toLowerCase();
            const cat = (m.category || '').toLowerCase();
            const desc = (m.description || '').toLowerCase();
            return name.includes(q) || cat.includes(q) || desc.includes(q);
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

function selectMedicine(medicine) {
    emit('update:modelValue', medicine.id);
    closeDropdown();
}

function clearSelection() {
    emit('update:modelValue', '');
    categoryFilter.value = '';
    searchQuery.value = '';
}

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        closeDropdown();
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="containerRef" class="searchable-medicine-select">
        <!-- Trigger / Display Box -->
        <div
            class="select-trigger"
            :class="{ 'is-open': isOpen, 'has-value': !!modelValue }"
            @click="openDropdown"
        >
            <div v-if="selectedMedicine" class="flex items-center justify-between w-full">
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
            <svg v-if="!selectedMedicine" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
        </div>

        <!-- Dropdown Panel -->
        <div v-if="isOpen" class="dropdown-panel">
            <!-- Search & Category Header -->
            <div class="search-wrapper flex flex-col gap-2">
                <div class="relative flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        class="search-input"
                        placeholder="Search medicine name or category..."
                        @keydown.esc="closeDropdown"
                    />
                </div>

                <div v-if="categories.length > 0" class="flex items-center gap-1.5 pt-1">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase">Category:</span>
                    <select
                        v-model="categoryFilter"
                        class="text-[11px] py-0.5 px-2 rounded border-gray-200 bg-gray-50 focus:ring-[#095D86]"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>
            </div>

            <!-- Options List -->
            <ul class="options-list">
                <li
                    v-for="m in filteredMedicines"
                    :key="m.id"
                    class="option-item"
                    :class="{ 'is-selected': m.id == modelValue }"
                    @click="selectMedicine(m)"
                >
                    <div class="option-main">
                        <span class="option-name">{{ m.name }}</span>
                        <span class="option-stock text-emerald-700 font-semibold">
                            Stock: {{ m.stock_quantity }} {{ m.unit || 'pcs' }}
                        </span>
                    </div>
                    <span v-if="m.category" class="option-cat">{{ m.category }}</span>
                </li>
                <li v-if="filteredMedicines.length === 0" class="no-results">
                    No medicine with available stock found
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.searchable-medicine-select {
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
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s ease;
}

.select-trigger:hover {
    border-color: #9ca3af;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.select-trigger.is-open {
    border-color: #095d86;
    box-shadow: 0 0 0 3px rgba(9,93,134,0.12);
}

.select-trigger.has-value {
    border-color: #095d86;
    background: #f0f9ff;
}

.selected-text {
    font-weight: 500;
    color: #0f172a;
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
    border: 1px solid #e2e8f0;
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
    padding: 0.625rem 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fafafa;
}

.search-icon {
    width: 1rem;
    height: 1rem;
    color: #9ca3af;
    margin-right: 0.5rem;
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.8125rem;
    color: #334155;
    padding: 0;
}

.search-input:focus {
    box-shadow: none;
    outline: none;
}

.options-list {
    max-height: 200px;
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
    background: #f0f9ff;
}

.option-item.is-selected {
    background: #e0f2fe;
}

.option-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.option-name {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.option-stock {
    font-size: 0.6875rem;
    margin-top: 1px;
}

.option-cat {
    font-size: 0.6875rem;
    font-weight: 500;
    color: #0369a1;
    background: #e0f2fe;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    white-space: nowrap;
    flex-shrink: 0;
    margin-left: 0.5rem;
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

.options-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
</style>
