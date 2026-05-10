<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// Modern Lucide-style icons
const ArrowLeftIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
</svg>`;



const EnvelopeIcon = () => `
<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
</svg>`;

const UserIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
</svg>`;

const AtSymbolIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 10-2.636 6.364M16.5 12V8.25" />
</svg>`;

const PhoneIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
</svg>`;

const ChatBubbleIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
</svg>`;

const CalendarIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
</svg>`;

const ClockIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>`;

const ChevronLeftIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5" />
</svg>`;

const ChevronRightIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
</svg>`;

interface ContactMessage {
    id: number;
    nama: string;
    email: string;
    nomor_telepon?: string;
    pesan: string;
    created_at: string;
    updated_at: string;
    created_at_formatted: string;
    updated_at_formatted: string;
    time_ago: string;
}

interface MessageNavigation {
    id: number;
    nama: string;
    created_at_formatted: string;
}

interface Props {
    contactMessage: ContactMessage;
    previousMessage: MessageNavigation | null;
    nextMessage: MessageNavigation | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pesan Kontak', href: '/admin/contact-messages' },
    { title: 'Detail Pesan', href: `/admin/contact-messages/${props.contactMessage?.id || 'show'}` },
];
</script>

<template>
    <Head :title="`Detail Pesan - ${contactMessage?.nama || 'Loading...'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <!-- Header Section -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Detail Pesan Kontak
                        </h1>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            Pesan dari {{ contactMessage.nama }} • {{ contactMessage.time_ago }}
                        </p>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div v-if="previousMessage || nextMessage" class="flex justify-between items-center">
                    <Link v-if="previousMessage" :href="`/admin/contact-messages/${previousMessage.id}`"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <span v-html="ChevronLeftIcon()"></span>
                        <div class="text-left">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Pesan Sebelumnya</div>
                            <div class="font-medium">{{ previousMessage.nama }}</div>
                        </div>
                    </Link>
                    <div v-else></div>

                    <Link v-if="nextMessage" :href="`/admin/contact-messages/${nextMessage.id}`"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <div class="text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Pesan Berikutnya</div>
                            <div class="font-medium">{{ nextMessage.nama }}</div>
                        </div>
                        <span v-html="ChevronRightIcon()"></span>
                    </Link>
                    <div v-else></div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- Left Column - Main Content -->
                    <div class="xl:col-span-2 space-y-8">

                        <!-- Contact Information Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="UserIcon()"></span>
                                    Informasi Pengirim
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Detail data pengirim pesan
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nama -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span v-html="UserIcon()"></span>
                                            Nama Lengkap
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-base font-medium text-gray-900 dark:text-white">{{ contactMessage.nama }}</span>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span v-html="AtSymbolIcon()"></span>
                                            Email
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <a :href="`mailto:${contactMessage.email}`"
                                               class="text-base font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ contactMessage.email }}
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Nomor Telepon -->
                                    <div class="space-y-2 md:col-span-2" v-if="contactMessage.nomor_telepon">
                                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span v-html="PhoneIcon()"></span>
                                            Nomor Telepon
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <a :href="`tel:${contactMessage.nomor_telepon}`"
                                               class="text-base font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ contactMessage.nomor_telepon }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message Content Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="ChatBubbleIcon()"></span>
                                    Isi Pesan
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Pesan lengkap dari pengirim
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                    <p class="text-base text-gray-900 dark:text-white whitespace-pre-wrap leading-relaxed">{{ contactMessage.pesan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Meta Info -->
                    <div class="xl:col-span-1 space-y-8">

                        <!-- System Information Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="CalendarIcon()"></span>
                                    Informasi Waktu
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Detail waktu pesan
                                </p>
                            </div>

                            <div class="p-6 space-y-4">
                                <!-- Diterima -->
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <span v-html="ClockIcon()"></span>
                                        Diterima
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ contactMessage.created_at_formatted }}</span>
                                    </div>
                                </div>

                                <!-- Time Ago -->
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
                                    <div class="flex items-center gap-2">
                                        <span v-html="ClockIcon()" class="text-blue-600 dark:text-blue-400"></span>
                                        <span class="text-sm font-medium text-blue-900 dark:text-blue-300">
                                            {{ contactMessage.time_ago }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Notice -->
                        <div class="rounded-xl bg-amber-50 p-4 border border-amber-200 dark:bg-amber-900/10 dark:border-amber-800">
                            <div class="flex items-start gap-3">
                                <span v-html="EnvelopeIcon().replace('w-8 h-8', 'w-5 h-5')" class="text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5"></span>
                                <div>
                                    <h5 class="text-sm font-medium text-amber-900 dark:text-amber-300">Catatan</h5>
                                    <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                                        Pastikan untuk membalas pesan ini sesegera mungkin untuk memberikan pelayanan terbaik kepada pengirim.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-start">
                    <Link href="/admin/contact-messages"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar Pesan
                    </Link>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
