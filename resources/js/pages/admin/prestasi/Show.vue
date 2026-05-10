<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// ── Icons ─────────────────────────────────────────────────────────
const ArrowLeftIcon  = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const TrophyIcon     = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3-3h.75m-12.75 3a3 3 0 0 0-3-3h-.75m15 0v-6.75a3.75 3.75 0 0 0-3.75-3.75h-9a3.75 3.75 0 0 0-3.75 3.75v6.75m12 0v2.25a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-2.25" /></svg>`;
const UsersIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>`;
const XMarkIcon      = () => `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
const ImageIcon      = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
const BookOpenIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`;
const UserIcon       = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const BuildingIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l7-3 7 3z" /></svg>`;
const AlignLeftIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>`;

// ── Types ─────────────────────────────────────────────────────────
interface KelasDetail {
    nama_kelas:   string;
    jurusan?:     string;
    tingkat?:     string;
    tahun_ajaran: string;
    status:       string;
}

interface SiswaPrestasi {
    siswa_id:      number;
    siswa_nama:    string;
    siswa_nis:     string;
    angkatan:      number;
    jenis_kelamin: string;
    alamat?:       string;
    foto?:         string;
    status:        string;
    kelas_detail:  KelasDetail[];
}

interface Prestasi {
    id:               number;
    nama_lomba:       string;
    tingkat:          string;
    juara:            string;
    penyelenggara?:   string;
    tanggal:          string;
    tanggal_formatted: string;
    tahun:            number;
    foto?:            string;
    deskripsi?:       string;
    jumlah_siswa:     number;
    siswa_prestasi:   SiswaPrestasi[];
    angkatan_terkait: number[];
    created_at:       string;
    updated_at:       string;
}

interface Props {
    prestasi: Prestasi;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',     href: '/admin/dashboard' },
    { title: 'Data Prestasi', href: '/admin/prestasi' },
    { title: 'Detail Prestasi', href: `/admin/prestasi/${props.prestasi?.id}` },
];

// ── Modal siswa ───────────────────────────────────────────────────
const showSiswaModal   = ref(false);
const selectedSiswa    = ref<SiswaPrestasi | null>(null);

const openSiswaModal = (siswa: SiswaPrestasi) => {
    selectedSiswa.value  = siswa;
    showSiswaModal.value = true;
};

const closeSiswaModal = () => {
    showSiswaModal.value = false;
    selectedSiswa.value  = null;
};

// ── Helpers ───────────────────────────────────────────────────────
const getFotoUrl       = (foto?: string) => foto ? `/storage/${foto}` : null;
const getDefaultAvatar = () => '/images/default-avatar.png';

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
};

const getTingkatColor = (tingkat: string) => {
    switch (tingkat.toLowerCase()) {
        case 'kabupaten':     return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        case 'provinsi':      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'nasional':      return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
        case 'internasional': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
        default:              return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const getJuaraColor = (juara: string) => {
    if (juara.includes('1'))                     return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
    if (juara.includes('2'))                     return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    if (juara.includes('3'))                     return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300';
    if (juara.toLowerCase().includes('harapan')) return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'Aktif':    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'Nonaktif': return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
        case 'Lulus':    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        case 'Pindah':   return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
        default:         return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const getStatusDot = (status: string) => {
    switch (status) {
        case 'Aktif':    return 'bg-green-500';
        case 'Nonaktif': return 'bg-gray-400';
        case 'Lulus':    return 'bg-blue-500';
        case 'Pindah':   return 'bg-yellow-500';
        default:         return 'bg-gray-400';
    }
};

const capitalize = (str: string) => str.charAt(0).toUpperCase() + str.slice(1);

// ── Computed ───────────────────────────────────────────────────────
// Group siswa by angkatan
const siswaByAngkatan = computed(() => {
    const grouped = props.prestasi.siswa_prestasi.reduce((acc, siswa) => {
        const ang = siswa.angkatan;
        if (!acc[ang]) acc[ang] = [];
        acc[ang].push(siswa);
        return acc;
    }, {} as Record<number, SiswaPrestasi[]>);

    return Object.entries(grouped)
        .map(([angkatan, list]) => ({
            angkatan: parseInt(angkatan),
            siswa:    list.sort((a, b) => a.siswa_nama.localeCompare(b.siswa_nama)),
            count:    list.length,
        }))
        .sort((a, b) => b.angkatan - a.angkatan);
});

const totalUniqueAngkatan = computed(() => props.prestasi.angkatan_terkait.length);
</script>

<template>
    <Head :title="`Detail Prestasi - ${prestasi.nama_lomba}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- ── Header ─────────────────────────────────────── -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                            {{ prestasi.nama_lomba }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">
                            Detail informasi prestasi dan siswa yang meraih pencapaian ini
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span :class="getTingkatColor(prestasi.tingkat)"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ capitalize(prestasi.tingkat) }}
                            </span>
                            <span :class="getJuaraColor(prestasi.juara)"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ prestasi.juara }}
                            </span>
                            <span class="text-gray-400 text-xs">·</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <span v-html="CalendarIcon()"></span>
                                {{ prestasi.tanggal_formatted }}
                            </span>
                        </div>
                    </div>
                    <Link :href="route('admin.prestasi.edit', prestasi.id)"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Prestasi
                    </Link>
                </div>

                <!-- ── BARIS 1: Foto + Info Dasar ─────────────────── -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Foto dokumentasi -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="ImageIcon().replace('w-8 h-8', 'w-4 h-4')"></span>
                                    Foto Dokumentasi
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Foto kegiatan prestasi</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <img v-if="getFotoUrl(prestasi.foto)"
                                    :src="getFotoUrl(prestasi.foto)!"
                                    :alt="prestasi.nama_lomba"
                                    class="w-full max-h-64 rounded-xl object-cover border border-gray-200 dark:border-gray-700 ring-1 ring-black/5 dark:ring-white/10" />
                                <div v-else
                                    class="w-full h-48 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex flex-col items-center justify-center gap-3">
                                    <span v-html="ImageIcon()" class="text-gray-300 dark:text-gray-600"></span>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada foto</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Prestasi -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="TrophyIcon()"></span>
                                    Informasi Prestasi
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Detail lengkap tentang prestasi dan kompetisi</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                    <!-- Nama Lomba -->
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Lomba</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ prestasi.nama_lomba }}</p>
                                    </div>

                                    <!-- Tingkat -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tingkat</p>
                                        <span
                                            class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ capitalize(prestasi.tingkat) }}
                                        </span>
                                    </div>

                                    <!-- Juara -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Juara</p>
                                        <span
                                            class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ prestasi.juara }}
                                        </span>
                                    </div>

                                    <!-- Penyelenggara -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Penyelenggara</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="BuildingIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm text-gray-900 dark:text-white">
                                                {{ prestasi.penyelenggara || 'Tidak disebutkan' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Tanggal -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Lomba</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="CalendarIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ prestasi.tanggal_formatted }}</p>
                                        </div>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div v-if="prestasi.deskripsi" class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                            <span v-html="AlignLeftIcon()"></span>Deskripsi
                                        </p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white leading-relaxed whitespace-pre-wrap">{{ prestasi.deskripsi }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── BARIS 2: Info Sistem + Siswa Berprestasi ────── -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Informasi Sistem -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="CalendarIcon()"></span>
                                    Informasi Sistem
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Audit trail</p>
                            </div>
                            <div class="p-6 space-y-4">

                                <!-- Dibuat -->
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Dibuat</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(prestasi.created_at) }}</p>
                                </div>

                                <!-- Diperbarui -->
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(prestasi.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Siswa Berprestasi -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="UsersIcon()"></span>
                                    Siswa Berprestasi
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ prestasi.jumlah_siswa }}
                                    </span>
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Klik nama siswa untuk melihat detail</p>
                            </div>
                            <div class="p-6">

                                <!-- Ada siswa -->
                                <div v-if="siswaByAngkatan.length > 0" class="space-y-5">
                                    <div v-for="group in siswaByAngkatan" :key="group.angkatan">
                                        <!-- Header angkatan -->
                                        <div class="flex items-center gap-3 mb-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                Angkatan {{ group.angkatan }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ group.count }} siswa</span>
                                            <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                                        </div>

                                        <!-- List siswa — bisa diklik -->
                                        <div class="space-y-2">
                                            <button v-for="siswa in group.siswa" :key="siswa.siswa_id"
                                                @click="openSiswaModal(siswa)"
                                                class="w-full flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-indigo-50 hover:border-indigo-200 dark:hover:bg-indigo-900/10 dark:hover:border-indigo-800 transition-all text-left group">

                                                <!-- Avatar siswa -->
                                                <img v-if="getFotoUrl(siswa.foto)"
                                                    :src="getFotoUrl(siswa.foto)!"
                                                    :alt="siswa.siswa_nama"
                                                    class="h-10 w-10 rounded-full object-cover flex-shrink-0 border border-gray-200 dark:border-gray-600 ring-1 ring-black/5 dark:ring-white/10" />
                                                <div v-else
                                                    class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex-shrink-0 flex items-center justify-center border border-indigo-200 dark:border-indigo-800">
                                                    <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                                        {{ siswa.siswa_nama.charAt(0) }}
                                                    </span>
                                                </div>

                                                <!-- Info siswa -->
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                                        {{ siswa.siswa_nama }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        NIS: {{ siswa.siswa_nis }} · {{ siswa.jenis_kelamin }}
                                                    </p>
                                                </div>

                                                <!-- Status badge -->
                                                <span :class="getStatusColor(siswa.status)"
                                                    class="inline-flex items-center gap-1 flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-medium">
                                                    <span :class="getStatusDot(siswa.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                                    {{ siswa.status }}
                                                </span>

                                                <!-- Arrow hint -->
                                                <svg class="h-4 w-4 text-gray-300 group-hover:text-indigo-400 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kosong -->
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <span v-html="UsersIcon()" class="text-gray-400 w-6 h-6"></span>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada siswa terdaftar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Actions ────────────────────────────────────── -->
                <div class="pb-4">
                    <Link :href="route('admin.prestasi.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar Prestasi
                    </Link>
                </div>

            </div>
        </div>

        <!-- ══ Modal Detail Siswa ══════════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showSiswaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeSiswaModal">

                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="closeSiswaModal"></div>

                <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 translate-y-4">

                    <div v-if="showSiswaModal && selectedSiswa"
                        class="relative w-full max-w-md max-h-[85vh] flex flex-col rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">

                        <!-- Modal Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <img v-if="getFotoUrl(selectedSiswa.foto)"
                                    :src="getFotoUrl(selectedSiswa.foto)!"
                                    :alt="selectedSiswa.siswa_nama"
                                    class="h-11 w-11 rounded-full object-cover border border-gray-200 dark:border-gray-600 ring-1 ring-black/5 dark:ring-white/10 flex-shrink-0" />
                                <div v-else
                                    class="h-11 w-11 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex-shrink-0 flex items-center justify-center border border-indigo-200 dark:border-indigo-800">
                                    <span class="text-base font-bold text-indigo-700 dark:text-indigo-300">
                                        {{ selectedSiswa.siswa_nama.charAt(0) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedSiswa.siswa_nama }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">NIS: {{ selectedSiswa.siswa_nis }}</p>
                                </div>
                            </div>
                            <button @click="closeSiswaModal" type="button"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                            <!-- Data Pribadi -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 px-3 py-2.5">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Jenis Kelamin</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ selectedSiswa.jenis_kelamin }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 px-3 py-2.5">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Angkatan</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ selectedSiswa.angkatan }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 px-3 py-2.5">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Status Terkini</p>
                                    <span :class="getStatusColor(selectedSiswa.status)"
                                        class="mt-0.5 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
                                        <span :class="getStatusDot(selectedSiswa.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                        {{ selectedSiswa.status }}
                                    </span>
                                </div>
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 px-3 py-2.5 col-span-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Alamat</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5 truncate" :title="selectedSiswa.alamat">
                                        {{ selectedSiswa.alamat || '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Riwayat Kelas -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                    <span v-html="BookOpenIcon()"></span>
                                    Riwayat Akademik
                                </h4>

                                <div v-if="selectedSiswa.kelas_detail && selectedSiswa.kelas_detail.length > 0" class="space-y-2">
                                    <div v-for="(kelas, idx) in selectedSiswa.kelas_detail" :key="idx"
                                        class="rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ kelas.nama_kelas }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ kelas.jurusan || '-' }}
                                                    <span v-if="kelas.tingkat"> · {{ kelas.tingkat }}</span>
                                                    · {{ kelas.tahun_ajaran }}
                                                </p>
                                            </div>
                                            <span :class="getStatusColor(kelas.status)"
                                                class="inline-flex items-center gap-1 flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-medium">
                                                <span :class="getStatusDot(kelas.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                                {{ kelas.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 py-6 text-center">
                                    <p class="text-sm text-gray-400 dark:text-gray-600 italic">Belum ada data kelas</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex-shrink-0 border-t border-gray-100 dark:border-gray-800 px-6 py-4 flex gap-3">
                            <Link :href="route('admin.siswa.show', selectedSiswa.siswa_id)"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <span v-html="UserIcon().replace('w-5 h-5', 'w-4 h-4')"></span>
                                Lihat Profil Lengkap
                            </Link>
                            <button @click="closeSiswaModal" type="button"
                                class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Tutup
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

    </AppLayout>
</template>
