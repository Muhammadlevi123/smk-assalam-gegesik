<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// Modern Lucide-style icons
const ArrowLeftIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
</svg>`;

const EditIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
</svg>`;

const CalendarIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
</svg>`;

const BookOpenIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
</svg>`;

const BadgeCheckIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
</svg>`;

const ClockIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none" />
</svg>`;

// Interface untuk data dari controller
interface TahunAjaran {
    id: number;
    tahun: string;
}

interface KalenderAkademik {
    id: number;
    judul: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    tahun_ajaran_id: number;
    tahun_ajaran: TahunAjaran | null; // ← Tambahkan null possibility
    created_at: string;
    updated_at: string;
}

interface Props {
    kalenderAkademik: KalenderAkademik;
}

const props = defineProps<Props>();

// Debug data
console.log('KalenderAkademik Props:', props.kalenderAkademik);
console.log('TahunAjaran Data:', props.kalenderAkademik?.tahun_ajaran);

// Add loading check
const isLoading = computed(() => !props.kalenderAkademik);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Kalender Akademik', href: '/admin/kalender-akademik' },
    { title: 'Detail Kalender', href: `/admin/kalender-akademik/${props.kalenderAkademik?.id || 'show'}` },
];

// Format date helper
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDateShort = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Calculate duration helper
const calculateDuration = (startDate: string, endDate: string) => {
    if (!startDate || !endDate) return '-';

    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end.getTime() - start.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
        return '1 hari (hari yang sama)';
    } else if (diffDays === 1) {
        return '1 hari';
    } else {
        return `${diffDays} hari`;
    }
};

// Check if event is ongoing, upcoming, or past
const getEventStatus = (startDate: string, endDate: string) => {
    const now = new Date();
    const start = new Date(startDate);
    const end = new Date(endDate);

    if (now < start) {
        return { status: 'upcoming', label: 'Akan Datang', color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' };
    } else if (now >= start && now <= end) {
        return { status: 'ongoing', label: 'Sedang Berlangsung', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' };
    } else {
        return { status: 'past', label: 'Telah Selesai', color: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300' };
    }
};
</script>

<template>
    <Head :title="`Detail Kalender Akademik - ${kalenderAkademik?.judul || 'Loading...'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Loading State -->
        <div v-if="isLoading" class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                <div class="animate-pulse space-y-6">
                    <div class="h-8 bg-gray-200 rounded dark:bg-gray-700"></div>
                    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <div class="p-4 sm:p-6 space-y-4">
                            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700"></div>
                            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-1/2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <!-- Header Section -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ kalenderAkademik.judul }}
                            </h1>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                            </span>
                        </div>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            Tahun Ajaran {{ kalenderAkademik.tahun_ajaran?.tahun || '-' }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <Link :href="`/admin/kalender-akademik/${kalenderAkademik.id}/edit`"
                            class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                            Edit Kalender
                        </Link>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- Left Column - Main Info -->
                    <div class="xl:col-span-2 space-y-8">

                        <!-- Basic Information Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="BookOpenIcon()"></span>
                                    Informasi Kegiatan
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Detail informasi kegiatan akademik
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <!-- Judul Kegiatan -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Judul Kegiatan
                                        </label>
                                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kalenderAkademik.judul }}</span>
                                        </div>
                                    </div>

                                    <!-- Tahun Ajaran -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tahun Ajaran
                                        </label>
                                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kalenderAkademik.tahun_ajaran?.tahun || '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Information Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="CalendarIcon()"></span>
                                    Jadwal Kegiatan
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Informasi waktu pelaksanaan kegiatan
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Tanggal Mulai -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal Mulai
                                        </label>
                                        <div class="p-4 bg-green-50 rounded-lg border border-green-200 dark:bg-green-900/20 dark:border-green-800">
                                            <div class="flex items-center gap-2">
                                                <span v-html="CalendarIcon()" class="text-green-600 dark:text-green-400"></span>
                                                <span class="text-lg font-semibold text-green-700 dark:text-green-300">
                                                    {{ formatDateShort(kalenderAkademik.tanggal_mulai) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tanggal Selesai -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tanggal Selesai
                                        </label>
                                        <div class="p-4 bg-red-50 rounded-lg border border-red-200 dark:bg-red-900/20 dark:border-red-800">
                                            <div class="flex items-center gap-2">
                                                <span v-html="CalendarIcon()" class="text-red-600 dark:text-red-400"></span>
                                                <span class="text-lg font-semibold text-red-700 dark:text-red-300">
                                                    {{ formatDateShort(kalenderAkademik.tanggal_selesai) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Durasi -->
                                    <div class="space-y-2 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Durasi Kegiatan
                                        </label>
                                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
                                            <div class="flex items-center gap-2">
                                                <span v-html="ClockIcon()" class="text-blue-600 dark:text-blue-400"></span>
                                                <span class="text-lg font-semibold text-blue-700 dark:text-blue-300">
                                                    {{ calculateDuration(kalenderAkademik.tanggal_mulai, kalenderAkademik.tanggal_selesai) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Status & Meta Info -->
                    <div class="xl:col-span-1 space-y-8">
                        <!-- Event Status Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="BadgeCheckIcon()"></span>
                                    Status Kegiatan
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Informasi status kegiatan saat ini
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4"
                                        :class="getEventStatus(kalenderAkademik.tanggal_mulai, kalenderAkademik.tanggal_selesai).color">
                                        <span v-html="CalendarIcon().replace('w-5 h-5', 'w-8 h-8')"></span>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                        {{ getEventStatus(kalenderAkademik.tanggal_mulai, kalenderAkademik.tanggal_selesai).label }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Mulai: {{ formatDateShort(kalenderAkademik.tanggal_mulai) }}
                                        <br>
                                        Selesai: {{ formatDateShort(kalenderAkademik.tanggal_selesai) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- System Information Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="ClockIcon()"></span>
                                    Informasi Sistem
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Data sistem dan audit trail
                                </p>
                            </div>

                            <div class="p-6 space-y-4">
                                <!-- Created At -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Dibuat
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(kalenderAkademik.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- Updated At -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Terakhir Diubah
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(kalenderAkademik.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Info -->
                        <div class="rounded-xl bg-blue-50 p-4 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                            <div class="flex items-start gap-3">
                                <span v-html="BadgeCheckIcon()" class="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></span>
                                <div>
                                    <h5 class="text-sm font-medium text-blue-900 dark:text-blue-300">Informasi Kegiatan</h5>
                                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                                        Data kegiatan ini telah terverifikasi dan tersimpan dalam sistem kalender akademik.
                                        Untuk melakukan perubahan, klik tombol "Edit Kalender" di bagian atas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-start">
                    <Link :href="'/admin/kalender-akademik'"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Kalender Akademik
                    </Link>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
