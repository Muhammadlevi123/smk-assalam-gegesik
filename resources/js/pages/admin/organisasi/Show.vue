<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
const BuildingIcon    = () => `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4" /></svg>`;

interface Organisasi {
    id: number;
    nama: string;
    jenis: string;
    deskripsi?: string;
    logo?: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{ organisasi: Organisasi }>();

const isLoading = computed(() => !props.organisasi);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Organisasi', href: '/admin/organisasi' },
    { title: 'Detail Organisasi', href: `/admin/organisasi/${props.organisasi?.id || 'show'}` },
];

const getLogoUrl = () =>
    props.organisasi?.logo ? `/storage/${props.organisasi.logo}` : null;

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Detail Organisasi - ${organisasi?.nama || 'Loading...'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Loading -->
        <div v-if="isLoading" class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="animate-pulse space-y-6">
                    <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-1/3"></div>
                    <div class="h-64 bg-gray-200 rounded-2xl dark:bg-gray-700"></div>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div v-else class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- ── Header ── -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Detail Organisasi</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Informasi lengkap untuk organisasi "{{ organisasi.nama }}"</p>
                    </div>
                    <Link :href="route('admin.organisasi.edit', organisasi.id)"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Organisasi
                    </Link>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     BARIS 1: Logo (1 kolom) + Data Organisasi (2 kolom)
                ══════════════════════════════════════════════════════════ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Logo -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Logo Organisasi</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Logo resmi organisasi</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <!-- Ada logo -->
                                <img v-if="getLogoUrl()" :src="getLogoUrl()!" :alt="organisasi.nama"
                                    class="h-44 w-44 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                <!-- Placeholder gedung jika tidak ada logo -->
                                <div v-else
                                    class="flex h-44 w-44 items-center justify-center rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                                    <span v-html="BuildingIcon()" class="text-gray-400 dark:text-gray-600"></span>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ organisasi.nama }}</p>
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
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Organisasi</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informasi detail organisasi</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Organisasi</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ organisasi.nama }}</p>
                                    </div>

                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis</p>
                                        <p class="mt-1">
                                            <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                {{ organisasi.jenis }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Deskripsi</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white leading-relaxed">
                                            {{ organisasi.deskripsi || 'Deskripsi belum tersedia' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     BARIS 2: Informasi Sistem (full)
                ══════════════════════════════════════════════════════════ -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Informasi Sistem (kiri, 1 kolom) -->
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
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(organisasi.created_at) }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(organisasi.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Back Button ── -->
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
