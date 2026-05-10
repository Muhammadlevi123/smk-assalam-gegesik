<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const StatusIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;

// ✅ Interface disesuaikan dengan struktur data dari controller
// Controller mengirim status_tahun_ajaran sebagai flat array:
// [{ id, tahun, status }, ...]
// Bukan lagi pakai relasi tahunAjaran dengan pivot
interface StatusTahunAjaran {
    id: number;
    tahun: string;
    status: string;
}

interface TenagaKependidikan {
    id: number;
    nama: string;
    jenis_kelamin: string;
    jabatan: string;
    alamat?: string;
    foto?: string;
    status_tahun_ajaran: StatusTahunAjaran[];
    created_at: string;
    updated_at: string;
}

interface Props {
    tenagaKependidikan: TenagaKependidikan;
}

const props = defineProps<Props>();

const isLoading = computed(() => !props.tenagaKependidikan);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Tenaga Kependidikan', href: '/admin/tenaga-kependidikan' },
    { title: 'Detail Tenaga Kependidikan', href: `/admin/tenaga-kependidikan/${props.tenagaKependidikan?.id || 'show'}` },
];

const getCurrentImageUrl = () => {
    if (!props.tenagaKependidikan?.foto) return '/images/default-avatar.png';
    return `/storage/${props.tenagaKependidikan.foto}`;
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
        default:         return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const getStatusDot = (status?: string) => {
    switch (status) {
        case 'Aktif':    return 'bg-green-500';
        case 'Nonaktif': return 'bg-gray-400';
        default:         return 'bg-gray-400';
    }
};

// ✅ Computed pakai status_tahun_ajaran (flat array, bukan pivot)
const statusTerkini = computed(() => {
    if (!props.tenagaKependidikan?.status_tahun_ajaran?.length) return 'Aktif';
    return [...props.tenagaKependidikan.status_tahun_ajaran]
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]))[0]
        .status;
});

const statusPerTahunAjaran = computed(() => {
    if (!props.tenagaKependidikan?.status_tahun_ajaran?.length) return [];
    return [...props.tenagaKependidikan.status_tahun_ajaran]
        .sort((a, b) => parseInt(b.tahun.split('/')[0]) - parseInt(a.tahun.split('/')[0]));
});
</script>

<template>
    <Head :title="`Detail Tenaga Kependidikan - ${tenagaKependidikan?.nama || 'Loading...'}`" />

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
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Detail Tenaga Kependidikan</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Informasi lengkap untuk "{{ tenagaKependidikan.nama }}"</p>
                    </div>
                    <Link :href="`/admin/tenaga-kependidikan/${tenagaKependidikan.id}/edit`"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Tenaga Kependidikan
                    </Link>
                </div>

                <!-- ══ BARIS 1: Foto (1 col) + Data Pribadi (2 col) ══ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Foto -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Foto Tenaga Kependidikan</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Foto profil tenaga kependidikan</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <img :src="getCurrentImageUrl()" :alt="tenagaKependidikan.nama"
                                    class="h-44 w-44 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ tenagaKependidikan.nama }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ tenagaKependidikan.jabatan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pribadi -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Pribadi</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informasi personal tenaga kependidikan</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ tenagaKependidikan.nama }}</p>
                                    </div>

                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis Kelamin</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ tenagaKependidikan.jenis_kelamin }}</p>
                                    </div>

                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jabatan</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ tenagaKependidikan.jabatan }}</p>
                                    </div>

                                    <!-- ✅ Status Terkini -->
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
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white leading-relaxed">
                                            {{ tenagaKependidikan.alamat || 'Alamat tidak tersedia' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ BARIS 2: Info Sistem (1 col) + Status Tahun Ajaran (2 col) ══ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Informasi Sistem -->
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
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(tenagaKependidikan.created_at) }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(tenagaKependidikan.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Per Tahun Ajaran -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="StatusIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                    Status Per Tahun Ajaran
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Status tenaga kependidikan pada setiap tahun ajaran</p>
                            </div>
                            <div class="p-6">

                                <!-- Ada data -->
                                <div v-if="statusPerTahunAjaran.length > 0">

                                    <!-- Desktop: tabel -->
                                    <div class="hidden sm:block overflow-x-auto">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                                                    <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                <!-- ✅ item.status langsung, tidak perlu item.pivot.status -->
                                                <tr v-for="item in statusPerTahunAjaran" :key="item.id"
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

                                    <!-- Mobile: card stack -->
                                    <div class="sm:hidden space-y-3">
                                        <div v-for="item in statusPerTahunAjaran" :key="item.id"
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

                                <!-- Kosong -->
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada data status tahun ajaran</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Actions ── -->
                <div class="pb-4">
                    <Link href="/admin/tenaga-kependidikan"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar
                    </Link>
                </div>

            </div>
        </div>

    </AppLayout>
</template>
