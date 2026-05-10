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

const UserIcon = () => `
<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
</svg>`;

const CalendarIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
</svg>`;

const BriefcaseIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
</svg>`;

const LocationMarkerIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
</svg>`;

const PhoneIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
</svg>`;

const EnvelopeIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
</svg>`;

const AcademicCapIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
</svg>`;

const BadgeCheckIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
</svg>`;

const GraduationCapIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443a55.381 55.381 0 0 1 5.25 2.882V15" />
</svg>`;

interface TahunAjaranStatus {
    id: number;
    tahun_ajaran_id: number;
    status: string;
    tahun_ajaran?: {
        id: number;
        tahun: string;
    };
}

interface Siswa {
    id: number;
    nama: string;
    nis: string;
    angkatan: string;
    jenis_kelamin: string;
    alamat?: string;
    tahun_ajaran_status?: TahunAjaranStatus[];
}

interface Alumni {
    id: number;
    siswa_id: number;
    pekerjaan: string;
    foto?: string;
    alamat_sekarang?: string;
    no_telepon?: string;
    email?: string;
    tahun_lulus?: number | string;
    // Accessor attributes from model
    nama?: string;
    nis?: string;
    angkatan?: string;
    jenis_kelamin?: string;
    alamat_asal?: string;
    created_at: string;
    updated_at: string;
    siswa?: Siswa;
}

interface Props {
    alumni: Alumni;
}

const props = defineProps<Props>();

// Add loading check
const isLoading = computed(() => !props.alumni);

// Helper function to get alumni name
const getAlumniName = (alumni: Alumni | null): string => {
    if (!alumni) return 'Alumni';
    return alumni.nama || alumni.siswa?.nama || 'Alumni';
};

// Helper function to get alumni NIS
const getAlumniNis = (alumni: Alumni | null): string => {
    if (!alumni) return 'NIS';
    return alumni.nis || alumni.siswa?.nis || 'NIS';
};

// Helper function to get alumni angkatan
const getAlumniAngkatan = (alumni: Alumni | null): string => {
    if (!alumni) return 'Angkatan';
    return alumni.angkatan || alumni.siswa?.angkatan || 'Angkatan';
};

// Helper function to get alumni jenis kelamin
const getAlumniJenisKelamin = (alumni: Alumni | null): string => {
    if (!alumni) return 'Tidak diketahui';
    return alumni.jenis_kelamin || alumni.siswa?.jenis_kelamin || 'Tidak diketahui';
};

// Helper function to get alamat asal
const getAlamatAsal = (alumni: Alumni | null): string => {
    if (!alumni) return 'Alamat tidak tersedia';
    return alumni.alamat_asal || alumni.siswa?.alamat || 'Alamat tidak tersedia';
};

// Helper function to get tahun lulus
const getTahunLulus = (alumni: Alumni | null): string => {
    if (!alumni) return '-';

    if (alumni.tahun_lulus) {
        return String(alumni.tahun_lulus);
    }

    // Try to get from siswa tahun ajaran status if available
    if (alumni.siswa?.tahun_ajaran_status) {
        const lulusStatus = alumni.siswa.tahun_ajaran_status
            .find(status => status.status === 'Lulus');
        if (lulusStatus?.tahun_ajaran?.tahun) {
            return lulusStatus.tahun_ajaran.tahun;
        }
    }

    return '-';
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Alumni', href: '/admin/alumni' },
    { title: 'Detail Alumni', href: `/admin/alumni/${props.alumni?.id || 'show'}` },
];

// Get current image URL
const getCurrentImageUrl = () => {
    if (!props.alumni?.foto) {
        return '/images/default-avatar.png';
    }
    return `/storage/${props.alumni.foto}`;
};

// Format date
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Get status lulus information
const getStatusLulusInfo = (alumni: Alumni | null) => {
    if (!alumni?.siswa?.tahun_ajaran_status) return null;

    const lulusStatus = alumni.siswa.tahun_ajaran_status
        .find(status => status.status === 'Lulus');

    if (lulusStatus?.tahun_ajaran) {
        return {
            tahun: lulusStatus.tahun_ajaran.tahun,
            status: lulusStatus.status
        };
    }

    return null;
};
</script>

<template>
    <Head :title="`Detail Alumni - ${getAlumniName(alumni)}`" />

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
                            Detail Alumni
                        </h1>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            Informasi lengkap untuk alumni "{{ getAlumniName(alumni) }}"
                        </p>
                        <div class="mt-3">
                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                                <div class="flex items-center gap-2">
                                    <span v-html="GraduationCapIcon()" class="text-green-500"></span>
                                    <span>Alumni Terdaftar</span>
                                </div>
                                <div v-if="getStatusLulusInfo(alumni)" class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                    <span>Lulus Tahun {{ getStatusLulusInfo(alumni)?.tahun }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                                    <span>Angkatan {{ getAlumniAngkatan(alumni) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3">
                        <Link :href="`/admin/alumni/${alumni.id}/edit`"
                            class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:px-6 sm:py-3">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        <span>Edit Alumni</span>
                        </Link>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- Left Column - Photo & Quick Info -->
                    <div class="xl:col-span-1">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 h-fit">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Foto Alumni</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Foto profil alumni
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="space-y-6">
                                    <!-- Photo -->
                                    <div class="flex justify-center">
                                        <img
                                            :src="getCurrentImageUrl()"
                                            :alt="`${getAlumniName(alumni)} photo`"
                                            class="h-48 w-48 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10"
                                        />
                                    </div>

                                    <!-- Quick Info -->
                                    <div class="space-y-4">
                                        <div class="text-center">
                                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ getAlumniName(alumni) }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">NIS: {{ getAlumniNis(alumni) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Detailed Information -->
                    <div class="xl:col-span-2">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Alumni</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Detail lengkap data alumni
                                </p>
                            </div>

                            <div class="p-6">
                                <div class="space-y-8">
                                    <!-- Personal Information -->
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <span v-html="UserIcon().replace('w-8 h-8', 'w-5 h-5')"></span>
                                            Data Pribadi
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- NIS -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    NIS (Nomor Induk Siswa)
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getAlumniNis(alumni) }}</span>
                                                </div>
                                            </div>

                                            <!-- Nama -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Nama Lengkap
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getAlumniName(alumni) }}</span>
                                                </div>
                                            </div>

                                            <!-- Jenis Kelamin -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Jenis Kelamin
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getAlumniJenisKelamin(alumni) }}</span>
                                                </div>
                                            </div>

                                            <!-- Angkatan -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Angkatan
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getAlumniAngkatan(alumni) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Alamat Asal - Full Width -->
                                        <div class="mt-6 space-y-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <span v-html="LocationMarkerIcon()"></span>
                                                Alamat Asal (Sewaktu Sekolah)
                                            </label>
                                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                <span class="text-sm text-gray-900 dark:text-white">{{ getAlamatAsal(alumni) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Alumni Information -->
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <span v-html="BriefcaseIcon()"></span>
                                            Data Alumni
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Pekerjaan -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Pekerjaan Saat Ini
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ alumni.pekerjaan }}</span>
                                                </div>
                                            </div>

                                            <!-- Tahun Lulus -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Tahun Lulus
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getTahunLulus(alumni) }}</span>
                                                </div>
                                            </div>

                                            <!-- No Telepon -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                    <span v-html="PhoneIcon()"></span>
                                                    Nomor Telepon
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ alumni.no_telepon || 'Tidak tersedia' }}</span>
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                    <span v-html="EnvelopeIcon()"></span>
                                                    Email
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ alumni.email || 'Tidak tersedia' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Alamat Sekarang - Full Width -->
                                        <div class="mt-6 space-y-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <span v-html="LocationMarkerIcon()"></span>
                                                Alamat Saat Ini
                                            </label>
                                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                <span class="text-sm text-gray-900 dark:text-white">{{ alumni.alamat_sekarang || 'Alamat tidak tersedia' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Academic History (if available) -->
                                    <div v-if="getStatusLulusInfo(alumni)">
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <span v-html="GraduationCapIcon()"></span>
                                            Riwayat Akademik
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Status Kelulusan -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Status Kelulusan
                                                </label>
                                                <div class="p-3 bg-green-50 rounded-lg border border-green-200 dark:bg-green-900/10 dark:border-green-800">
                                                    <div class="flex items-center gap-2">
                                                        <span v-html="BadgeCheckIcon().replace('w-5 h-5', 'w-4 h-4')" class="text-green-600 dark:text-green-400"></span>
                                                        <span class="text-sm font-medium text-green-900 dark:text-green-100">{{ getStatusLulusInfo(alumni)?.status }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tahun Ajaran Lulus -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Tahun Ajaran Lulus
                                                </label>
                                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ getStatusLulusInfo(alumni)?.tahun }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- System Information -->
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                            <span v-html="CalendarIcon()"></span>
                                            Informasi Sistem
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Created At -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Tanggal Terdaftar Alumni
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(alumni.created_at) }}</span>
                                                </div>
                                            </div>

                                            <!-- Updated At -->
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Terakhir Diperbarui
                                                </label>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(alumni.updated_at) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Info -->
                                    <div class="rounded-xl bg-blue-50 p-4 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                                        <div class="flex items-start gap-3">
                                            <span v-html="BadgeCheckIcon()" class="text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></span>
                                            <div>
                                                <h5 class="text-sm font-medium text-blue-900 dark:text-blue-300">Informasi Alumni</h5>
                                                <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                                                    Data alumni ini telah terverifikasi dan tersimpan dalam sistem.
                                                    Alumni ini adalah lulusan sekolah yang telah menyelesaikan pendidikannya
                                                    <span v-if="getStatusLulusInfo(alumni)">pada tahun ajaran {{ getStatusLulusInfo(alumni)?.tahun }}</span>.
                                                    Untuk melakukan perubahan data, klik tombol "Edit Alumni" di bagian atas.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                                    <Link :href="'/admin/alumni'"
                                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                        <span v-html="ArrowLeftIcon()"></span>
                                        Kembali ke Daftar
                                    </Link>

                                    <Link :href="`/admin/alumni/${alumni.id}/edit`"
                                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                        <span v-html="EditIcon()"></span>
                                        Edit Data
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
