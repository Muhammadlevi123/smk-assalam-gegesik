<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const UserIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const ClockIcon     = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" /></svg>`;
const TagIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6Z" /></svg>`;
const BuildingIcon  = () => `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4" /></svg>`;
const AlignLeftIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>`;

interface Organisasi {
    id:              number;
    nama:            string;
    jenis:           string;
    deskripsi?:      string;
    pembina?:        string;
    jadwal_latihan?: string;
    logo?:           string;
    created_at:      string;
    updated_at:      string;
}

const props = defineProps<{ organisasi: Organisasi }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',       href: '/admin/dashboard' },
    { title: 'Data Organisasi', href: '/admin/organisasi' },
    { title: 'Detail Organisasi', href: `/admin/organisasi/${props.organisasi?.id}` },
];

const logoUrl = computed(() => props.organisasi?.logo ? `/storage/${props.organisasi.logo}` : null);

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Detail Organisasi - ${organisasi.nama}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                            {{ organisasi.nama }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Detail informasi organisasi</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                <span v-html="TagIcon()"></span>
                                {{ organisasi.jenis }}
                            </span>
                            <span v-if="organisasi.pembina" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                <span v-html="UserIcon()"></span>
                                {{ organisasi.pembina }}
                            </span>
                            <span v-if="organisasi.jadwal_latihan" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <span v-html="ClockIcon()"></span>
                                {{ organisasi.jadwal_latihan }}
                            </span>
                        </div>
                    </div>
                    <Link :href="route('admin.organisasi.edit', organisasi.id)"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Organisasi
                    </Link>
                </div>

                <!-- Baris 1: Logo + Data Organisasi -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Logo -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Logo Organisasi</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Logo resmi organisasi</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <img v-if="logoUrl" :src="logoUrl" :alt="organisasi.nama"
                                    class="h-44 w-44 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                <div v-else class="flex h-44 w-44 items-center justify-center rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                                    <span v-html="BuildingIcon()" class="text-gray-400 dark:text-gray-600"></span>
                                </div>
                                <div class="text-center">
                                    <p class="text-base font-bold text-gray-900 dark:text-white">{{ organisasi.nama }}</p>
                                    <span class="mt-1.5 inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ organisasi.jenis }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Organisasi -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="TagIcon()"></span>
                                    Informasi Organisasi
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Detail lengkap tentang organisasi ini</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                    <!-- Nama -->
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Organisasi</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ organisasi.nama }}</p>
                                    </div>

                                    <!-- Jenis -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis</p>
                                        <span class="mt-1 inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ organisasi.jenis }}
                                        </span>
                                    </div>

                                    <!-- Pembina -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pembina</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="UserIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ organisasi.pembina || 'Belum diisi' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Jadwal Latihan -->
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jadwal Latihan</p>
                                        <div class="mt-1 flex items-start gap-1.5">
                                            <span v-html="ClockIcon()" class="text-gray-400 flex-shrink-0 mt-0.5"></span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white leading-relaxed">
                                                {{ organisasi.jadwal_latihan || 'Belum diisi' }}
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris 2: Deskripsi + Info Sistem -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Informasi Sistem (kiri) -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="CalendarIcon()" class="text-gray-500 dark:text-gray-400"></span>
                                    Informasi Sistem
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Audit trail</p>
                            </div>
                            <div class="flex flex-col gap-4 p-6">
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Dibuat</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(organisasi.created_at) }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(organisasi.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi (kanan, 2 kolom) -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                    <span v-html="AlignLeftIcon()"></span>
                                    Deskripsi
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Keterangan lengkap organisasi</p>
                            </div>
                            <div class="p-6">
                                <p v-if="organisasi.deskripsi"
                                    class="text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ organisasi.deskripsi }}
                                </p>
                                <div v-else class="flex flex-col items-center justify-center py-10">
                                    <span v-html="AlignLeftIcon()" class="text-gray-300 dark:text-gray-600 w-8 h-8"></span>
                                    <p class="mt-3 text-sm text-gray-400 dark:text-gray-500 italic">Deskripsi belum tersedia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="pb-4">
                    <Link :href="route('admin.organisasi.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
