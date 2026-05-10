<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const BookOpenIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`;

interface Siswa {
    id:            number;
    nis:           string;
    nama:          string;
    jenis_kelamin: string;
    alamat?:       string;
    angkatan:      string;
    foto?:         string;
    kelas: Array<{
        id:         number;
        nama_kelas: string;
        jurusan?:   string;
        tingkat?:   string;
        pivot: { siswa_id: number; kelas_id: number; tahun_ajaran_id: number };
    }>;
    kelas_detail?: Array<{
        id:              number;
        nama_kelas:      string;
        jurusan?:        string;
        tingkat?:        string;
        tahun_ajaran_id: number;
        tahun_ajaran:    string;
        status:          string;
        kelulusan:       string | null;
    }>;
    status_detail?: Array<{
        tahun_ajaran_id: number;
        tahun:           string;
        status:          string;
        kelulusan:       string | null;
    }>;
    tahunAjaranStatus: Array<{
        id:    number;
        tahun: string;
        pivot: { siswa_id: number; tahun_ajaran_id: number; status: string; kelulusan?: string | null };
    }>;
    created_at: string;
    updated_at: string;
}

interface Props {
    siswa: Siswa;
}

const props = defineProps<Props>();

const isLoading = computed(() => !props.siswa);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Siswa', href: '/admin/siswa' },
    { title: 'Detail Siswa', href: `/admin/siswa/${props.siswa?.id || 'show'}` },
];

const getCurrentImageUrl = () => {
    if (!props.siswa?.foto) return '/images/default-avatar.png';
    return `/storage/${props.siswa.foto}`;
};

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
        case 'Pindah':   return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
        default:         return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const getKelulusanColor = (kelulusan?: string | null) => {
    if (kelulusan === 'Lulus')       return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
    if (kelulusan === 'Tidak Lulus') return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
    return 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400';
};

const getStatusDot = (status?: string) => {
    switch (status) {
        case 'Aktif':    return 'bg-green-500';
        case 'Nonaktif': return 'bg-gray-400';
        case 'Pindah':   return 'bg-yellow-500';
        default:         return 'bg-gray-400';
    }
};

// Ambil status terkini — prioritaskan kelulusan jika ada
const statusTerkini = computed(() => {
    if (!props.siswa?.tahunAjaranStatus || props.siswa.tahunAjaranStatus.length === 0)
        return { label: 'Aktif', isKelulusan: false };

    const sorted = [...props.siswa.tahunAjaranStatus]
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]));

    const denganKelulusan = sorted.find(ta => ta.pivot.kelulusan && ta.pivot.kelulusan !== '');
    if (denganKelulusan?.pivot.kelulusan) {
        return { label: denganKelulusan.pivot.kelulusan as string, isKelulusan: true };
    }

    return { label: sorted[0].pivot.status, isKelulusan: false };
});

const akademikData = computed(() => {
    // Gunakan status_detail dari controller untuk riwayat lengkap per tahun ajaran
    if (props.siswa?.status_detail && props.siswa.status_detail.length > 0) {
        return [...props.siswa.status_detail]
            .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]))
            .map(sd => {
                // Cari kelas yang sesuai dengan tahun ajaran ini
                const kelasPadaTahunIni = props.siswa.kelas_detail?.find(
                    k => k.tahun_ajaran_id === sd.tahun_ajaran_id
                );
                return {
                    tahun_ajaran:    sd.tahun,
                    tahun_ajaran_id: sd.tahun_ajaran_id,
                    nama_kelas:      kelasPadaTahunIni?.nama_kelas || '-',
                    jurusan:         kelasPadaTahunIni?.jurusan || '-',
                    tingkat:         kelasPadaTahunIni?.tingkat || '-',
                    status:          sd.status,
                    kelulusan:       sd.kelulusan,
                };
            });
    }

    // Fallback ke kelas_detail
    if (props.siswa?.kelas_detail && props.siswa.kelas_detail.length > 0) {
        return [...props.siswa.kelas_detail]
            .sort((a, b) => parseInt(b.tahun_ajaran.split('/')[0]) - parseInt(a.tahun_ajaran.split('/')[0]))
            .map(k => ({
                tahun_ajaran:    k.tahun_ajaran,
                tahun_ajaran_id: k.tahun_ajaran_id,
                nama_kelas:      k.nama_kelas,
                jurusan:         k.jurusan || '-',
                tingkat:         k.tingkat || '-',
                status:          k.status,
                kelulusan:       k.kelulusan,
            }));
    }

    return [];
});
</script>

<template>
    <Head :title="`Detail Siswa - ${siswa?.nama || 'Loading...'}`" />

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

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Detail Siswa</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Informasi lengkap untuk siswa "{{ siswa.nama }}"</p>
                    </div>
                    <Link :href="`/admin/siswa/${siswa.id}/edit`"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Siswa
                    </Link>
                </div>

                <!-- Baris 1: Foto + Data Pribadi -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Foto -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Foto Siswa</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Foto profil siswa</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <img :src="getCurrentImageUrl()" :alt="siswa.nama"
                                    class="h-44 w-44 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ siswa.nama }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">NIS: {{ siswa.nis }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pribadi -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Pribadi</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informasi personal siswa</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">NIS</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ siswa.nis }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ siswa.nama }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis Kelamin</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ siswa.jenis_kelamin }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Angkatan</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ siswa.angkatan }}</p>
                                    </div>
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Alamat</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white leading-relaxed">
                                            {{ siswa.alamat || 'Alamat tidak tersedia' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris 2: Informasi Sistem + Riwayat Akademik -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Informasi Sistem -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="CalendarIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                    Informasi Sistem
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Audit trail dan waktu pencatatan</p>
                            </div>
                            <div class="flex flex-col gap-4 p-6">
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Didaftarkan</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(siswa.created_at) }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(siswa.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Akademik -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="BookOpenIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                    Riwayat Akademik
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Data kelas, status, dan kelulusan per tahun ajaran</p>
                            </div>
                            <div class="p-6">

                                <!-- Ada data -->
                                <div v-if="akademikData.length > 0">

                                    <!-- Desktop: tabel -->
                                    <div class="hidden sm:block overflow-x-auto">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kelas</th>
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jurusan</th>
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Kelulusan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                <tr v-for="(item, i) in akademikData" :key="i"
                                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                    <td class="py-3.5 pr-4 text-sm font-medium text-gray-900 dark:text-white">{{ item.tahun_ajaran }}</td>
                                                    <td class="py-3.5 pr-4 text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama_kelas }}</td>
                                                    <td class="py-3.5 pr-4 text-sm text-gray-600 dark:text-gray-400">{{ item.jurusan }}</td>
                                                    <td class="py-3.5 pr-4">
                                                        <span :class="getStatusColor(item.status)"
                                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium">
                                                            <span :class="getStatusDot(item.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                                            {{ item.status }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3.5">
                                                        <span v-if="item.kelulusan"
                                                            :class="getKelulusanColor(item.kelulusan)"
                                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold">
                                                            {{ item.kelulusan }}
                                                        </span>
                                                        <span v-else class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Mobile: card stack -->
                                    <div class="sm:hidden space-y-3">
                                        <div v-for="(item, i) in akademikData" :key="i"
                                            class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.tahun_ajaran }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ item.nama_kelas }} · {{ item.jurusan }}</p>
                                                </div>
                                                <div class="flex flex-col items-end gap-1">
                                                    <span :class="getStatusColor(item.status)"
                                                        class="inline-flex items-center gap-1 flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-medium">
                                                        <span :class="getStatusDot(item.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                                        {{ item.status }}
                                                    </span>
                                                    <span v-if="item.kelulusan"
                                                        :class="getKelulusanColor(item.kelulusan)"
                                                        class="inline-flex items-center flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold">
                                                        {{ item.kelulusan }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kosong -->
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada data akademik</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pb-4">
                    <Link href="/admin/siswa"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar
                    </Link>
                </div>

            </div>
        </div>

    </AppLayout>
</template>
