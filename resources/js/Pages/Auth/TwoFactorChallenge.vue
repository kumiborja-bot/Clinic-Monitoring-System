<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, nextTick, computed } from 'vue';

const form = useForm({
    code: '',
});

const digits = ref(['', '', '', '', '', '']);
const inputs = ref([]);
const resending = ref(false);

const page = usePage();
const resendStatus = computed(() => page.props.flash?.status);

const focusInput = (index) => {
    nextTick(() => {
        if (inputs.value[index]) {
            inputs.value[index].focus();
            inputs.value[index].select();
        }
    });
};

const handleInput = (index, event) => {
    const value = event.target.value;

    // Only allow digits
    if (!/^\d*$/.test(value)) {
        digits.value[index] = '';
        return;
    }

    // Handle paste of full code
    if (value.length > 1) {
        const chars = value.split('').filter(c => /\d/.test(c)).slice(0, 6);
        chars.forEach((char, i) => {
            if (i < 6) digits.value[i] = char;
        });
        focusInput(Math.min(chars.length, 5));
        return;
    }

    digits.value[index] = value;

    // Auto-advance to next input
    if (value && index < 5) {
        focusInput(index + 1);
    }
};

const handleKeydown = (index, event) => {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        focusInput(index - 1);
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    pastedData.split('').forEach((char, i) => {
        if (i < 6) digits.value[i] = char;
    });
    focusInput(Math.min(pastedData.length, 5));
};

const submit = () => {
    form.code = digits.value.join('');
    form.post(route('2fa.store'), {
        onError: () => {
            digits.value = ['', '', '', '', '', ''];
            focusInput(0);
        },
    });
};

const resendCode = () => {
    resending.value = true;
    router.post(route('2fa.resend'), {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            resending.value = false;
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Two-Factor Verification" />

        <div class="text-center mb-6">
            <!-- Mail Icon -->
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#132639]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                     stroke="#d4a017" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>

            <h2 class="text-xl font-bold" style="color: #0f1b35;">Verify Your Identity</h2>
            <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                We've sent a 6-digit verification code to your email address. Enter the code below to continue.
            </p>
        </div>

        <!-- Success message -->
        <div v-if="resendStatus" class="mb-4 rounded-lg border px-4 py-3 text-sm font-medium"
             style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
            {{ resendStatus }}
        </div>

        <form @submit.prevent="submit">
            <!-- OTP Digit Inputs -->
            <div class="flex justify-center gap-2 sm:gap-3 mb-2">
                <input
                    v-for="(digit, index) in digits"
                    :key="index"
                    :ref="el => inputs[index] = el"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    :value="digits[index]"
                    @input="handleInput(index, $event)"
                    @keydown="handleKeydown(index, $event)"
                    @paste="handlePaste"
                    @focus="$event.target.select()"
                    class="h-14 w-11 sm:w-12 rounded-lg border-2 text-center text-2xl font-bold transition-all duration-200 focus:outline-none"
                    :class="{
                        'border-gray-300 focus:border-blue-900 focus:ring-2 focus:ring-blue-900/20': !form.errors.code,
                        'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20': form.errors.code,
                    }"
                    :style="{ color: '#0f1b35' }"
                    :autofocus="index === 0"
                />
            </div>

            <InputError class="mt-2 text-center" :message="form.errors.code" />

            <!-- Submit Button -->
            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center py-3 text-base bg-[#132639] hover:bg-[#0E4362]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing || digits.join('').length < 6"
                    style="border: none;"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Verifying...
                    </span>
                    <span v-else>Verify & Login</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Resend Code -->
        <div class="mt-5 text-center">
            <p class="text-sm text-gray-500">Didn't receive the code?</p>
            <button
                @click="resendCode"
                :disabled="resending"
                class="mt-1 text-sm font-semibold transition-colors duration-200 hover:underline focus:outline-none disabled:opacity-50"
                style="color: #d4a017;"
            >
                <span v-if="resending">Sending...</span>
                <span v-else>Resend Code</span>
            </button>
        </div>

        <!-- Timer info -->
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-400">
                Code expires in 10 minutes
            </p>
        </div>
    </GuestLayout>
</template>
