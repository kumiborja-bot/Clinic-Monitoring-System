<script setup>
import { useOfflineQueue } from '@/utils/offlineQueue';

const { isOnline, pendingCount, isSyncing, lastSyncResult, syncQueue } = useOfflineQueue();
</script>

<template>
    <!-- Offline Banner -->
    <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="!isOnline" class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200/60 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728" />
                    <line x1="4" y1="4" x2="20" y2="20" stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-red-700">You are offline</p>
                <p class="text-xs text-red-500/80">Transactions will be queued and synced when you reconnect.</p>
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/15 text-red-600 ring-1 ring-red-200/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>
                    Offline
                </span>
            </div>
        </div>
    </transition>

    <!-- Pending Sync Banner -->
    <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="isOnline && pendingCount > 0" class="mb-4 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200/60 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-amber-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-amber-700">
                    {{ pendingCount }} pending {{ pendingCount === 1 ? 'transaction' : 'transactions' }}
                </p>
                <p class="text-xs text-amber-500/80">Queued offline — ready to sync to server.</p>
            </div>
            <button @click="syncQueue" :disabled="isSyncing"
                class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-60 bg-[#F0A500] hover:bg-[#F4B842] text-[#132639]">
                <svg v-if="isSyncing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ isSyncing ? 'Syncing...' : 'Sync Now' }}
            </button>
        </div>
    </transition>

    <!-- Success Toast -->
    <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="lastSyncResult" class="mb-4 px-4 py-3 rounded-xl backdrop-blur-sm flex items-center gap-3 shadow-sm"
            :class="lastSyncResult.success
                ? 'bg-emerald-50 border border-emerald-200/60'
                : 'bg-red-50 border border-red-200/60'">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                :class="lastSyncResult.success ? 'bg-emerald-500/15' : 'bg-red-500/15'">
                <svg v-if="lastSyncResult.success" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium" :class="lastSyncResult.success ? 'text-emerald-700' : 'text-red-700'">
                {{ lastSyncResult.message }}
            </p>
        </div>
    </transition>
</template>
