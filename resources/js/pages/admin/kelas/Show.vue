<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const UsersIcon       = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>`;
const UserIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const CalendarIcon    = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const BookOpenIcon    = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`;
const BadgeCheckIcon  = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>`;
const AcademicCapIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>`;
const ChevronRightIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>`;
const XIcon           = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;

interface TahunAjaran { id: number; tahun: string; }
interface WaliKelasDetail { id: number; nama: string; tahun_ajaran_id: number; tahun_ajaran: string; }
interface SiswaDetail {
    id: number; nis: string; nama: string; jenis_kelamin: string; foto?: string;
    tahun_ajaran_id: number; tahun_ajaran: string; status_terkini: string;
}
interface Statistik { total_siswa: number; total_wali_kelas: number; siswa_aktif: number; siswa_nonaktif: number; }
interface Kelas {
    id: number; nama_kelas: string; jurusan: string; tingkat: string;
    wali_kelas_detail: WaliKelasDetail[];
    siswa_detail: SiswaDetail[];
    statistik: Statistik;
    tahun_ajaran_terkait: TahunAjaran[];
    created_at: string; updated_at: string;
}

const props = defineProps<{ kelas: Kelas }>();

const isLoading = computed(() => !props.kelas);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Kelas', href: '/admin/kelas' },
    { title: 'Detail Kelas', href: `/admin/kelas/${props.kelas?.id || 'show'}` },
];

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const getTingkatColor = (tingkat: string) => ({
    'X':   'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    'XI':  'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'XII': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
}[tingkat] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300');

const getStatusColor = (status: string) => ({
    'Aktif':    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    'Nonaktif': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
    'Lulus':    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'Pindah':   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
}[status] ?? 'bg-gray-100 text-gray-800');

const getSiswaImageUrl = (foto?: string) => foto ? `/storage/${foto}` : null;

const getProgressPercentage = (value: number, total: number) =>
    total === 0 ? 0 : Math.round((value / total) * 100);

// Tahun terurut descending
const tahunAjaranSorted = computed(() =>
    [...(props.kelas?.tahun_ajaran_terkait ?? [])].sort((a, b) => b.tahun.localeCompare(a.tahun))
);

// Group siswa & wali per tahun
const siswaByTahunAjaran = computed(() => {
    if (!props.kelas?.siswa_detail) return [];
    const grouped = props.kelas.siswa_detail.reduce((acc, s) => {
        if (!acc[s.tahun_ajaran]) acc[s.tahun_ajaran] = [];
        acc[s.tahun_ajaran].push(s);
        return acc;
    }, {} as Record<string, SiswaDetail[]>);
    return Object.entries(grouped)
        .map(([tahun, siswa]) => ({ tahun_ajaran: tahun, siswa: siswa.sort((a, b) => a.nama.localeCompare(b.nama)), count: siswa.length }))
        .sort((a, b) => b.tahun_ajaran.localeCompare(a.tahun_ajaran));
});

const waliKelasByTahunAjaran = computed(() => {
    if (!props.kelas?.wali_kelas_detail) return [];
    const grouped = props.kelas.wali_kelas_detail.reduce((acc, w) => {
        if (!acc[w.tahun_ajaran]) acc[w.tahun_ajaran] = [];
        acc[w.tahun_ajaran].push(w);
        return acc;
    }, {} as Record<string, WaliKelasDetail[]>);
    return Object.entries(grouped)
        .map(([tahun, wali]) => ({ tahun_ajaran: tahun, wali_kelas: wali.sort((a, b) => a.nama.localeCompare(b.nama)), count: wali.length }))
        .sort((a, b) => b.tahun_ajaran.localeCompare(a.tahun_ajaran));
});

// ── MODAL ──────────────────────────────────────────────────────────
const modalOpen       = ref(false);
const modalTahun      = ref<TahunAjaran | null>(null);

const modalSiswa = computed(() =>
    modalTahun.value
        ? (siswaByTahunAjaran.value.find(g => g.tahun_ajaran === modalTahun.value!.tahun)?.siswa ?? [])
        : []
);
const modalWali = computed(() =>
    modalTahun.value
        ? (waliKelasByTahunAjaran.value.find(g => g.tahun_ajaran === modalTahun.value!.tahun)?.wali_kelas ?? [])
        : []
);

const openModal = (tahun: TahunAjaran) => {
    modalTahun.value = tahun;
    modalOpen.value  = true;
};
const closeModal = () => {
    modalOpen.value  = false;
    modalTahun.value = null;
};
</script>

<template>
    <Head :title="`Detail Kelas - ${kelas?.nama_kelas || 'Loading...'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Loading -->
        <div v-if="isLoading" class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                <div class="animate-pulse space-y-6">
                    <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-1/3"></div>
                    <div class="h-48 bg-gray-200 rounded-2xl dark:bg-gray-700"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <!-- HEADER -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Detail Kelas {{ kelas.nama_kelas }}
                        </h1>
                        <p class="text-base text-gray-600 dark:text-gray-400">
                            Informasi lengkap tentang kelas {{ kelas.jurusan }} tingkat {{ kelas.tingkat }}
                        </p>
                    </div>
                    <Link :href="`/admin/kelas/${kelas.id}/edit`"
                        class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 self-start">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Kelas
                    </Link>
                </div>

                <!-- MAIN GRID -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- ══ KIRI (2/3) ══ -->
                    <div class="xl:col-span-2 space-y-8">

                        <!-- Informasi Kelas -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="BookOpenIcon()"></span>
                                    Informasi Kelas
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Detail data kelas dan informasi umum</p>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- Grid 3 field: Nama Kelas, Jurusan, Tingkat -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kelas</label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kelas.nama_kelas }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan</label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kelas.jurusan }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat</label>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kelas.tingkat }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tahun Ajaran Terkait — full width di bawah grid -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran Terkait</label>
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="tahun in tahunAjaranSorted" :key="tahun.id"
                                                class="inline-flex items-center rounded-full bg-white border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                                {{ tahun.tahun }}
                                            </span>
                                            <span v-if="tahunAjaranSorted.length === 0" class="text-sm text-gray-400 italic">Belum ada</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistik -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="BadgeCheckIcon()"></span>
                                    Statistik
                                </h3>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- 3 stat card -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center p-6 bg-green-50 rounded-lg border border-green-100 dark:bg-green-900/20 dark:border-green-800">
                                        <div class="text-3xl font-bold text-green-700 dark:text-green-300">{{ kelas.statistik.total_siswa }}</div>
                                        <div class="text-sm text-green-600 dark:text-green-400 mt-2">Total Siswa</div>
                                    </div>
                                    <div class="text-center p-6 bg-blue-50 rounded-lg border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800">
                                        <div class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ kelas.statistik.total_wali_kelas }}</div>
                                        <div class="text-sm text-blue-600 dark:text-blue-400 mt-2">Total Wali Kelas</div>
                                    </div>
                                    <div class="text-center p-6 bg-purple-50 rounded-lg border border-purple-100 dark:bg-purple-900/20 dark:border-purple-800">
                                        <div class="text-3xl font-bold text-purple-700 dark:text-purple-300">{{ kelas.tahun_ajaran_terkait.length }}</div>
                                        <div class="text-sm text-purple-600 dark:text-purple-400 mt-2">Tahun Ajaran</div>
                                    </div>
                                </div>

                                <!-- Detail Tahun Ajaran — bisa diklik -->
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                                        <h4 class="text-base font-medium text-gray-900 dark:text-white">Detail Tahun Ajaran</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Klik tahun ajaran untuk melihat detail siswa & wali kelas</p>
                                    </div>
                                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <button
                                            v-for="tahun in tahunAjaranSorted" :key="tahun.id"
                                            @click="openModal(tahun)"
                                            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left group">
                                            <div class="flex items-center gap-3">
                                                <span v-html="CalendarIcon()" class="text-blue-500 dark:text-blue-400 flex-shrink-0"></span>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                    {{ tahun.tahun }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    Siswa: {{ siswaByTahunAjaran.find(g => g.tahun_ajaran === tahun.tahun)?.count || 0 }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    Wali: {{ waliKelasByTahunAjaran.find(g => g.tahun_ajaran === tahun.tahun)?.count || 0 }}
                                                </span>
                                                <span v-html="ChevronRightIcon()" class="text-gray-400 group-hover:text-blue-500 transition-colors ml-1"></span>
                                            </div>
                                        </button>

                                        <div v-if="tahunAjaranSorted.length === 0"
                                            class="p-6 text-center text-sm text-gray-400 dark:text-gray-600">
                                            Belum ada tahun ajaran terkait
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ KANAN (1/3) ══ -->
                    <div class="xl:col-span-1 space-y-8">

                        <!-- Ringkasan -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="BadgeCheckIcon()"></span>
                                    Ringkasan
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Overview kelas secara keseluruhan</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Siswa</span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kelas.statistik.total_siswa }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Wali Kelas</span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ kelas.statistik.total_wali_kelas }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Siswa Aktif</span>
                                    <span class="text-lg font-semibold text-green-600 dark:text-green-400">{{ kelas.statistik.siswa_aktif }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Siswa Nonaktif</span>
                                    <span class="text-lg font-semibold text-gray-500 dark:text-gray-400">{{ kelas.statistik.siswa_nonaktif }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Tahun Ajaran Terkait</span>
                                    <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ kelas.tahun_ajaran_terkait.length }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Sistem -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="CalendarIcon()"></span>
                                    Informasi Sistem
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Data sistem dan audit trail</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dibuat</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(kelas.created_at) }}</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Terakhir Diubah</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(kelas.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-start">
                    <Link href="/admin/kelas"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar Kelas
                    </Link>
                </div>
            </div>
        </div>

        <!-- ══ MODAL DETAIL TAHUN AJARAN ══════════════════════════════════ -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">

            <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal Panel -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4">

                    <div v-if="modalOpen"
                        class="relative w-full max-w-2xl max-h-[85vh] flex flex-col rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">

                        <!-- Modal Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <span v-html="CalendarIcon().replace('w-5 h-5','w-4 h-4')" class="text-blue-600 dark:text-blue-400"></span>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                        Tahun Ajaran {{ modalTahun?.tahun }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ modalWali.length }} wali kelas · {{ modalSiswa.length }} siswa
                                    </p>
                                </div>
                            </div>
                            <button @click="closeModal" type="button"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <span v-html="XIcon()"></span>
                            </button>
                        </div>

                        <!-- Modal Body (scrollable) -->
                        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

                            <!-- Wali Kelas -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                    <span v-html="AcademicCapIcon().replace('w-5 h-5','w-4 h-4')" class="text-green-500"></span>
                                    Wali Kelas
                                </h4>
                                <div v-if="modalWali.length > 0" class="space-y-2">
                                    <div v-for="wali in modalWali" :key="wali.id"
                                        class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="h-9 w-9 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-bold text-green-700 dark:text-green-300">{{ wali.nama.charAt(0) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ wali.nama }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Wali Kelas {{ kelas.nama_kelas }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 py-4 text-center">
                                    <p class="text-sm text-gray-400 dark:text-gray-600 italic">Belum ada wali kelas</p>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-gray-100 dark:border-gray-800"></div>

                            <!-- Daftar Siswa -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                    <span v-html="UsersIcon().replace('w-5 h-5','w-4 h-4')" class="text-purple-500"></span>
                                    Daftar Siswa
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ modalSiswa.length }}
                                    </span>
                                </h4>
                                <div v-if="modalSiswa.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div v-for="siswa in modalSiswa" :key="siswa.id"
                                        class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                                        <!-- Avatar -->
                                        <img v-if="getSiswaImageUrl(siswa.foto)"
                                            :src="getSiswaImageUrl(siswa.foto)!" :alt="siswa.nama"
                                            class="h-10 w-10 rounded-xl border border-gray-200 bg-gray-100 object-cover flex-shrink-0 dark:border-gray-600" />
                                        <div v-else
                                            class="h-10 w-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex-shrink-0 flex items-center justify-center border border-purple-200 dark:border-purple-800">
                                            <span class="text-sm font-bold text-purple-700 dark:text-purple-300">{{ siswa.nama.charAt(0) }}</span>
                                        </div>
                                        <!-- Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ siswa.nama }}</p>
                                                <span :class="getStatusColor(siswa.status_terkini)"
                                                    class="inline-flex items-center rounded-full px-1.5 py-0.5 text-xs font-medium flex-shrink-0">
                                                    {{ siswa.status_terkini }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ siswa.nis }} · {{ siswa.jenis_kelamin === 'L' ? 'L' : 'P' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 py-6 text-center">
                                    <span v-html="UsersIcon()" class="text-gray-300 dark:text-gray-700 mx-auto mb-2"></span>
                                    <p class="text-sm text-gray-400 dark:text-gray-600 italic">Belum ada siswa di tahun ajaran ini</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex-shrink-0 border-t border-gray-100 dark:border-gray-800 px-6 py-4">
                            <button @click="closeModal" type="button"
                                class="w-full rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition-colors dark:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Tutup
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

    </AppLayout>
</template>
