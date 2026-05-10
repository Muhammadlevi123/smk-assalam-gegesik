<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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

const UsersIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
</svg>`;

const UserIcon = () => `
<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
</svg>`;

const CalendarIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
</svg>`;

const BookOpenIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
</svg>`;

const UserCheckIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4" />
</svg>`;

const BadgeCheckIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
</svg>`;

const ChartBarIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
</svg>`;

const AcademicCapIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
</svg>`;

// Interface sesuai dengan struktur data dari controller
interface GuruPengajarDetail {
    guru_id: number;
    guru_nama: string;
    tahun_ajaran: string;
    tahun_ajaran_id: number;
    created_at: string;
    updated_at: string;
}

interface GuruPengajarTerkini {
    nama: string;
    tahun: string;
    tahun_ajaran_id: number;
    jumlah_guru: number;
}

interface MataPelajaran {
    id: number;
    nama: string;
    jurusan: string | null;
    tingkat: string | null;
    // Data yang ditransform dari controller
    guru_pengajar_terkini: GuruPengajarTerkini | null;
    guru_pengajar_history: GuruPengajarDetail[];
    total_guru_history: number;
    jumlah_pengajaran: number;
    tahun_ajaran_terkait: string[];
    created_at: string;
    updated_at: string;
}

interface Props {
    mataPelajaran: MataPelajaran;
}

const props = defineProps<Props>();

// State for active tab
const activeTab = ref<'pengajaran' | 'history' | 'statistik'>('pengajaran');

// Add loading check
const isLoading = computed(() => !props.mataPelajaran);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Mata Pelajaran', href: '/admin/mata-pelajaran' },
    { title: 'Detail Mata Pelajaran', href: `/admin/mata-pelajaran/${props.mataPelajaran?.id || 'show'}` },
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
    return new Date(dateString).toLocaleDateString('id-ID');
};

// Get tingkat badge color
const getTingkatColor = (tingkat: string) => {
    switch (tingkat) {
        case 'X':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'XI':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        case 'XII':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

// Get jurusan badge color
const getJurusanColor = (jurusan: string) => {
    const colors = [
        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300',
    ];
    const hash = jurusan.split('').reduce((a, b) => a + b.charCodeAt(0), 0);
    return colors[hash % colors.length];
};

// Group pengajaran by tahun ajaran
const pengajaranByTahunAjaran = computed(() => {
    if (!props.mataPelajaran?.guru_pengajar_history) return [];

    const grouped = props.mataPelajaran.guru_pengajar_history.reduce((acc, pengajaran) => {
        const tahunAjaran = pengajaran.tahun_ajaran;
        if (!acc[tahunAjaran]) {
            acc[tahunAjaran] = [];
        }
        acc[tahunAjaran].push(pengajaran);
        return acc;
    }, {} as Record<string, GuruPengajarDetail[]>);

    return Object.entries(grouped)
        .map(([tahun, pengajaran]) => ({
            tahun_ajaran: tahun,
            pengajaran: pengajaran.sort((a, b) => a.guru_nama.localeCompare(b.guru_nama)),
            count: pengajaran.length
        }))
        .sort((a, b) => b.tahun_ajaran.localeCompare(a.tahun_ajaran));
});

// Get current year pengajaran
const currentYearPengajaran = computed(() => {
    if (!props.mataPelajaran?.guru_pengajar_terkini) return null;

    const terkini = props.mataPelajaran.guru_pengajar_terkini;
    if (terkini.jumlah_guru === 1) {
        return props.mataPelajaran.guru_pengajar_history.filter(p =>
            p.tahun_ajaran === terkini.tahun
        );
    } else {
        // Multiple guru in current year
        return props.mataPelajaran.guru_pengajar_history.filter(p =>
            p.tahun_ajaran === terkini.tahun
        );
    }
});

// Progress bar percentage helper
const getProgressPercentage = (value: number, total: number) => {
    if (total === 0) return 0;
    return Math.round((value / total) * 100);
};

// Statistics computed
const totalUniqueGuru = computed(() => {
    if (!props.mataPelajaran?.guru_pengajar_history) return 0;
    const uniqueGuru = new Set(props.mataPelajaran.guru_pengajar_history.map(p => p.guru_id));
    return uniqueGuru.size;
});

const totalTahunAjaran = computed(() => {
    return props.mataPelajaran?.tahun_ajaran_terkait?.length || 0;
});
</script>

<template>
    <Head :title="`Detail Mata Pelajaran - ${mataPelajaran?.nama || 'Loading...'}`" />

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
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Detail Mata Pelajaran {{ mataPelajaran.nama }}
                        </h1>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            Informasi lengkap tentang mata pelajaran
                            <span v-if="mataPelajaran.jurusan">{{ mataPelajaran.jurusan }}</span>
                            <span v-if="mataPelajaran.tingkat">tingkat {{ mataPelajaran.tingkat }}</span>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <Link :href="`/admin/mata-pelajaran/${mataPelajaran.id}/edit`"
                            class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                            Edit Mata Pelajaran
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
                                    Informasi Mata Pelajaran
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Detail data mata pelajaran dan informasi umum
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nama Mata Pelajaran -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nama Mata Pelajaran
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ mataPelajaran.nama }}</span>
                                        </div>
                                    </div>

                                    <!-- Jurusan -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Jurusan
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span v-if="mataPelajaran.jurusan" class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ mataPelajaran.jurusan }}
                                            </span>
                                            <span v-else class="text-gray-500 dark:text-gray-400 italic">
                                                Tidak ditentukan
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Tingkat -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tingkat
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <div class="flex items-center gap-2">
                                                <span v-if="mataPelajaran.tingkat" class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ mataPelajaran.tingkat }}
                                                </span>
                                                <span v-else class="text-gray-500 dark:text-gray-400 italic">
                                                    Tidak ditentukan
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pengajar Terkini -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Pengajar Terkini
                                        </label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <div v-if="mataPelajaran.guru_pengajar_terkini" class="space-y-1">
                                                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ mataPelajaran.guru_pengajar_terkini.nama }}
                                                </span>
                                            </div>
                                            <span v-else class="text-gray-500 dark:text-gray-400 italic">
                                                Belum ada pengajar
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-200 dark:border-gray-800">
                                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                                    <button @click="activeTab = 'pengajaran'"
                                        :class="[
                                            activeTab === 'pengajaran'
                                                ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                            'flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors'
                                        ]">
                                        <span v-html="UsersIcon()"></span>
                                        Pengajaran Terkini
                                        <span v-if="currentYearPengajaran" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            {{ currentYearPengajaran.length }}
                                        </span>
                                    </button>

                                    <button @click="activeTab = 'history'"
                                        :class="[
                                            activeTab === 'history'
                                                ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                            'flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors'
                                        ]">
                                        <span v-html="AcademicCapIcon()"></span>
                                        Riwayat Pengajaran
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            {{ mataPelajaran.total_guru_history }}
                                        </span>
                                    </button>

                                    <button @click="activeTab = 'statistik'"
                                        :class="[
                                            activeTab === 'statistik'
                                                ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                            'flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors'
                                        ]">
                                        <span v-html="ChartBarIcon()"></span>
                                        Statistik
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab Content -->
                            <div class="p-6">
                                <!-- Pengajaran Terkini Tab -->
                                <div v-if="activeTab === 'pengajaran'">
                                    <div v-if="currentYearPengajaran && currentYearPengajaran.length > 0" class="space-y-4">
                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">
                                                Tahun Ajaran {{ mataPelajaran.guru_pengajar_terkini?.tahun }}
                                            </h4>
                                            <div class="space-y-3">
                                                <div v-for="pengajaran in currentYearPengajaran" :key="pengajaran.guru_id"
                                                    class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-blue-100 dark:border-gray-700">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                        <span v-html="UserIcon().replace('w-8 h-8', 'w-5 h-5')" class="text-blue-600 dark:text-blue-400"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ pengajaran.guru_nama }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            Mengajar sejak {{ formatDateShort(pengajaran.created_at) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span v-html="UsersIcon()" class="text-gray-400"></span>
                                        </div>
                                        <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada pengajar aktif</h4>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Mata pelajaran ini belum memiliki guru pengajar untuk tahun ajaran terkini.
                                        </p>
                                    </div>
                                </div>

                                <!-- History Tab -->
                                <div v-if="activeTab === 'history'">
                                    <div v-if="pengajaranByTahunAjaran.length > 0" class="space-y-6">
                                        <div v-for="group in pengajaranByTahunAjaran" :key="group.tahun_ajaran"
                                            class="border border-gray-200 rounded-lg dark:border-gray-700">
                                            <!-- Tahun Ajaran Header -->
                                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-t-lg">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                        Tahun Ajaran {{ group.tahun_ajaran }}
                                                    </h4>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ group.count }} guru pengajar
                                                    </span>
                                                </div>
                                            </div>
                                            <!-- Pengajaran List -->
                                            <div class="p-4">
                                                <div class="space-y-3">
                                                    <div v-for="pengajaran in group.pengajaran" :key="pengajaran.guru_id"
                                                        class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                            <span v-html="UserIcon().replace('w-8 h-8', 'w-5 h-5')" class="text-blue-600 dark:text-blue-400"></span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ pengajaran.guru_nama }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                Mengajar dari {{ formatDateShort(pengajaran.created_at) }}
                                                                <span v-if="pengajaran.updated_at !== pengajaran.created_at">
                                                                    • Diperbarui {{ formatDateShort(pengajaran.updated_at) }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span v-html="UserCheckIcon()" class="text-gray-400"></span>
                                        </div>
                                        <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada riwayat pengajaran</h4>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Mata pelajaran ini belum memiliki riwayat guru pengajar.
                                        </p>
                                    </div>
                                </div>

                                <!-- Statistik Tab -->
                                <div v-if="activeTab === 'statistik'">
                                    <div class="space-y-6">
                                        <!-- Overview Stats -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div class="text-center p-6 bg-green-50 rounded-lg border border-green-100 dark:bg-green-900/20 dark:border-green-800">
                                                <div class="text-3xl font-bold text-green-700 dark:text-green-300">
                                                    {{ totalUniqueGuru }}
                                                </div>
                                                <div class="text-sm text-green-600 dark:text-green-400 mt-2">
                                                    Total Guru Unik
                                                </div>
                                            </div>
                                            <div class="text-center p-6 bg-blue-50 rounded-lg border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800">
                                                <div class="text-3xl font-bold text-blue-700 dark:text-blue-300">
                                                    {{ mataPelajaran.total_guru_history }}
                                                </div>
                                                <div class="text-sm text-blue-600 dark:text-blue-400 mt-2">
                                                    Total Pengajaran
                                                </div>
                                            </div>
                                            <div class="text-center p-6 bg-purple-50 rounded-lg border border-purple-100 dark:bg-purple-900/20 dark:border-purple-800">
                                                <div class="text-3xl font-bold text-purple-700 dark:text-purple-300">
                                                    {{ totalTahunAjaran }}
                                                </div>
                                                <div class="text-sm text-purple-600 dark:text-purple-400 mt-2">
                                                    Tahun Ajaran
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Quick Stats & Meta Info -->
                    <div class="xl:col-span-1 space-y-8">
                        <!-- Quick Statistics Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="BadgeCheckIcon()"></span>
                                    Ringkasan
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Overview mata pelajaran secara keseluruhan
                                </p>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Guru </span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ totalUniqueGuru }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Pengajaran</span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ mataPelajaran.total_guru_history }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Tahun Ajaran Terkait</span>
                                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                        {{ totalTahunAjaran }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- System Information Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="CalendarIcon()"></span>
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
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(mataPelajaran.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- Updated At -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Terakhir Diubah
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(mataPelajaran.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Info -->
                        <div class="rounded-xl bg-blue-50 p-4 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                            <div class="flex items-start gap-3">
                                <span v-html="BadgeCheckIcon()" class="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></span>
                                <div>
                                    <h5 class="text-sm font-medium text-blue-900 dark:text-blue-300">Informasi Mata Pelajaran</h5>
                                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                                        Data mata pelajaran ini telah terverifikasi dan tersimpan dalam sistem.
                                        Untuk melakukan perubahan, klik tombol "Edit Mata Pelajaran" di bagian atas.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-start">
                    <Link :href="'/admin/mata-pelajaran'"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar Mata Pelajaran
                    </Link>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
