<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const BookOpenIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`;
const AcademicCapIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>`;

interface TahunAjaran {
    id: number;
    tahun: string;
    pivot: { guru_id: number; tahun_ajaran_id: number; status: string };
}

// ✅ Hapus tingkat & jurusan — model MataPelajaran hanya punya nama
interface MataPelajaran {
    id: number;
    nama: string;
    pivot: { guru_id: number; mata_pelajaran_id: number; tahun_ajaran_id: number };
}

interface KelasAsWali {
    id: number;
    nama_kelas: string;
    jurusan?: string;
    tingkat?: string;
    pivot: { guru_id: number; kelas_id: number; tahun_ajaran_id: number };
}

interface Guru {
    id: number;
    nama: string;
    jenis_kelamin: string;
    alamat?: string;
    foto?: string;
    tahunAjaran: TahunAjaran[];
    mataPelajaran: MataPelajaran[];
    kelasAsWali: KelasAsWali[];
    created_at: string;
    updated_at: string;
}

interface Props { guru: Guru; }

const props = defineProps<Props>();
const isLoading = computed(() => !props.guru);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Guru', href: '/admin/guru' },
    { title: 'Detail Guru', href: `/admin/guru/${props.guru?.id || 'show'}` },
];

const getCurrentImageUrl = () => props.guru?.foto ? `/storage/${props.guru.foto}` : '/images/default-avatar.png';

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
};

const getStatusColor = (status?: string) => {
    switch (status) {
        case 'Aktif':    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'Nonaktif': return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
        case 'Pensiun':  return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        case 'Cuti':     return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
        default:         return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const getStatusDot = (status?: string) => {
    switch (status) {
        case 'Aktif':    return 'bg-green-500';
        case 'Nonaktif': return 'bg-gray-400';
        case 'Pensiun':  return 'bg-blue-500';
        case 'Cuti':     return 'bg-yellow-500';
        default:         return 'bg-gray-400';
    }
};

const statusTerkini = computed(() => {
    if (!props.guru?.tahunAjaran?.length) return 'Aktif';
    return [...props.guru.tahunAjaran]
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]))[0]
        .pivot.status;
});

const statusTahunAjaranData = computed(() => {
    if (!props.guru?.tahunAjaran) return [];
    return [...props.guru.tahunAjaran]
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]))
        .map(t => ({ tahun: t.tahun, status: t.pivot.status }));
});

// ✅ mataPelajaranFlat — hanya nama + tahun ajaran (hapus tingkat & jurusan)
const mataPelajaranFlat = computed(() => {
    if (!props.guru?.mataPelajaran?.length) return [];
    return [...props.guru.mataPelajaran]
        .map(m => ({
            id:              m.id,
            nama:            m.nama,
            tahun:           props.guru.tahunAjaran?.find(t => t.id === m.pivot.tahun_ajaran_id)?.tahun ?? '-',
            tahun_ajaran_id: m.pivot.tahun_ajaran_id,
        }))
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]));
});

// Wali kelas — tetap ada tingkat & jurusan karena dari model Kelas (bukan MataPelajaran)
const waliKelasFlat = computed(() => {
    if (!props.guru?.kelasAsWali?.length) return [];
    return [...props.guru.kelasAsWali]
        .map(k => ({
            id:      k.id,
            kelas:   k.nama_kelas,
            tingkat: k.tingkat ?? '-',
            jurusan: k.jurusan ?? '-',
            tahun:   props.guru.tahunAjaran?.find(t => t.id === k.pivot.tahun_ajaran_id)?.tahun ?? '-',
        }))
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]));
});
</script>

<template>
    <Head :title="`Detail Guru - ${guru?.nama || 'Loading...'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Loading -->
        <div v-if="isLoading" class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="animate-pulse space-y-6">
                    <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-1/3"></div>
                    <div class="h-64 bg-gray-200 rounded-2xl dark:bg-gray-700"></div>
                    <div class="h-48 bg-gray-200 rounded-2xl dark:bg-gray-700"></div>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div v-else class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- ── Header ── -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Detail Guru</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Informasi lengkap untuk guru "{{ guru.nama }}"</p>
                    </div>
                    <Link :href="`/admin/guru/${guru.id}/edit`"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Guru
                    </Link>
                </div>

                <!-- ══ BARIS 1: Foto (1 col) + Data Pribadi (2 col) ══ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Foto Guru</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Foto profil guru</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <img :src="getCurrentImageUrl()" :alt="guru.nama"
                                    class="h-44 w-44 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ guru.nama }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Pribadi</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informasi personal guru</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ guru.nama }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis Kelamin</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ guru.jenis_kelamin }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status Terkini</p>
                                        <span :class="getStatusColor(statusTerkini)"
                                            class="mt-1 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium">
                                            <span :class="getStatusDot(statusTerkini)" class="h-1.5 w-1.5 rounded-full"></span>
                                            {{ statusTerkini }}
                                        </span>
                                    </div>
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Alamat</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white leading-relaxed">{{ guru.alamat || 'Alamat tidak tersedia' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ BARIS 2: Info Sistem (1 col) + Status Tahun Ajaran (2 col) ══ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="CalendarIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                    Informasi Sistem
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Audit trail pencatatan</p>
                            </div>
                            <div class="flex flex-col gap-4 p-6">
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Didaftarkan</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(guru.created_at) }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(guru.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="CalendarIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                    Status Per Tahun Ajaran
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Status guru pada setiap tahun ajaran</p>
                            </div>
                            <div class="p-6">
                                <div v-if="statusTahunAjaranData.length > 0">
                                    <div class="hidden sm:block overflow-x-auto">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                <tr v-for="(item, i) in statusTahunAjaranData" :key="i"
                                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                    <td class="py-3.5 pr-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.tahun }}</td>
                                                    <td class="py-3.5">
                                                        <span :class="getStatusColor(item.status)"
                                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium">
                                                            <span :class="getStatusDot(item.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                                            {{ item.status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="sm:hidden space-y-3">
                                        <div v-for="(item, i) in statusTahunAjaranData" :key="i"
                                            class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.tahun }}</span>
                                            <span :class="getStatusColor(item.status)"
                                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
                                                <span :class="getStatusDot(item.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                                {{ item.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <span v-html="CalendarIcon()" class="text-gray-400" style="width:24px;height:24px;"></span>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada data status tahun ajaran</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ BARIS 3: Mata Pelajaran (kiri) + Wali Kelas (kanan) ══ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                    <!-- ── Mata Pelajaran ── -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                <span v-html="BookOpenIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                Mata Pelajaran Diampu
                            </h3>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Daftar mata pelajaran per tahun ajaran</p>
                        </div>
                        <div class="p-6">

                            <div v-if="mataPelajaranFlat.length > 0">
                                <!-- Desktop: tabel — ✅ hanya 2 kolom: Nama + Tahun Ajaran -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-for="item in mataPelajaranFlat" :key="`${item.id}-${item.tahun_ajaran_id}`"
                                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                <td class="py-3.5 pr-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.nama }}</td>
                                                <td class="py-3.5 text-sm text-gray-600 dark:text-gray-400">{{ item.tahun }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile: card stack -->
                                <div class="sm:hidden space-y-3">
                                    <div v-for="item in mataPelajaranFlat" :key="`${item.id}-${item.tahun_ajaran_id}`"
                                        class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama }}</p>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">{{ item.tahun }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center justify-center py-12">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <span v-html="BookOpenIcon()" class="text-gray-400" style="width:15px;height:15px;"></span>
                                </div>
                                <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada mata pelajaran yang diajarkan</p>
                            </div>

                        </div>
                    </div>

                    <!-- ── Wali Kelas ── tetap ada tingkat & jurusan karena dari model Kelas -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                <span v-html="AcademicCapIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                Wali Kelas
                            </h3>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Kelas yang diampu sebagai wali kelas</p>
                        </div>
                        <div class="p-6">

                            <div v-if="waliKelasFlat.length > 0">
                                <!-- Desktop: tabel -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kelas</th>
                                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tingkat</th>
                                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jurusan</th>
                                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-for="item in waliKelasFlat" :key="item.id"
                                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                <td class="py-3.5 pr-4 text-sm font-semibold text-gray-900 dark:text-white">{{ item.kelas }}</td>
                                                <td class="py-3.5 pr-4">
                                                    <span v-if="item.tingkat !== '-'"
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                        Kelas {{ item.tingkat }}
                                                    </span>
                                                    <span v-else class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                                </td>
                                                <td class="py-3.5 pr-4">
                                                    <span v-if="item.jurusan !== '-'"
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                        {{ item.jurusan }}
                                                    </span>
                                                    <span v-else class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                                </td>
                                                <td class="py-3.5 text-sm text-gray-600 dark:text-gray-400">{{ item.tahun }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile: card stack -->
                                <div class="sm:hidden space-y-3">
                                    <div v-for="item in waliKelasFlat" :key="item.id"
                                        class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.kelas }}</p>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">{{ item.tahun }}</span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <span v-if="item.tingkat !== '-'"
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                Kelas {{ item.tingkat }}
                                            </span>
                                            <span v-if="item.jurusan !== '-'"
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                {{ item.jurusan }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center justify-center py-12">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <span v-html="AcademicCapIcon()" class="text-gray-400" style="width:15px;height:15px;"></span>
                                </div>
                                <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Tidak menjadi wali kelas</p>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- ── Actions ── -->
                <div class="pb-4">
                    <Link href="/admin/guru"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar
                    </Link>
                </div>

            </div>
        </div>

    </AppLayout>
</template>
