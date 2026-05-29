<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center px-4 bg-[#132639]">
        <!-- Logo and School Name -->
        <div class="mb-6 text-center">
            <Link href="/">
                <img src="/images/logo.jpg" alt="MMACI Logo" class="mx-auto h-24 w-24 rounded-full object-cover shadow-lg ring-4 ring-white/20" />
            </Link>
            <h1 class="mt-4 text-lg font-bold uppercase tracking-wider" style="color: #d4a017;">
                Merchant Marine Academy of Caraga, Inc.
            </h1>
            <p class="mt-1 text-sm text-gray-300">Clinic Health Monitoring System</p>
        </div>

        <!-- Form Card -->
        <div class="w-full overflow-hidden bg-white px-6 py-6 shadow-2xl sm:max-w-md sm:rounded-xl">
            <slot />
        </div>

        <!-- Footer -->
        <p class="mt-6 text-xs text-gray-400">&copy; {{ new Date().getFullYear() }} MMACI. All rights reserved.</p>
    </div>
</template>

