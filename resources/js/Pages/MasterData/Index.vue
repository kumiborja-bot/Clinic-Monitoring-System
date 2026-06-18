<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Master Data" />
    <AuthenticatedLayout>
        <template #header>Master Data Management</template>

        <div class="max-w-6xl mx-auto py-4">
            <div class="mb-8">
                <p class="text-sm text-gray-500">
                    Welcome to the system master data control center. Configure, import, and manage dictionary tables and clinical rules.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Student Departments Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col group hover:-translate-y-1">
                    <div class="p-6 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                            <!-- Academic/Student Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.9c4.756 0 8.773-2.28 10.731-5.705a60.48 60.48 0 00-.49-6.347m-18 3.417l4-1.875a48.007 48.007 0 0110 0l4 1.875M2.25 8.25l10.5-5.25 10.5 5.25-10.5 5.25-10.5-5.25zM12 14.25v-3" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800 mb-2 group-hover:text-emerald-700 transition-colors">Student Departments</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Manage academic colleges, degree programs, and department lists for students. Link courses and edit department codes.
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between mt-auto">
                        <span class="text-xs text-gray-400 font-medium">Academic Lists</span>
                        <Link :href="route('departments.index', { type: 'student' })" class="inline-flex items-center gap-1.5 text-xs font-bold text-navy-800 hover:text-[#0E4362] transition-colors">
                            Manage
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </div>

                <!-- Personnel Departments Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col group hover:-translate-y-1">
                    <div class="p-6 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                            <!-- Administrative/Office Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800 mb-2 group-hover:text-blue-700 transition-colors">Personnel Departments</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Manage offices, services, administrative divisions, and department lists for personnel. Edit codes and administrative descriptions.
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between mt-auto">
                        <span class="text-xs text-gray-400 font-medium">Administrative Lists</span>
                        <Link :href="route('departments.index', { type: 'personnel' })" class="inline-flex items-center gap-1.5 text-xs font-bold text-navy-800 hover:text-[#0E4362] transition-colors">
                            Manage
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </div>

                <!-- DSS Data Master Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col group hover:-translate-y-1">
                    <div class="p-6 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-mustard-50 text-mustard-700 flex items-center justify-center mb-4 group-hover:bg-[#F0A500] group-hover:text-white transition-colors duration-300">
                            <!-- Brain/Decisions Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800 mb-2 group-hover:text-[#F0A500] transition-colors">DSS Data Master</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Configure Clinical Decision Support System (DSS) rules. Set up diagnosis guidelines, severity thresholds, health assessment checklists, and medical recommendations.
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between mt-auto">
                        <span class="text-xs text-gray-400 font-medium">Manage decision logic</span>
                        <Link :href="route('dss.index')" class="inline-flex items-center gap-1.5 text-xs font-bold text-navy-800 hover:text-[#0E4362] transition-colors">
                            Manage DSS Rules
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
