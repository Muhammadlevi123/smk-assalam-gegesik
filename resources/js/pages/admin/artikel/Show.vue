<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// ── Icons ─────────────────────────────────────────────────────────
const ArrowLeftIcon  = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>`;
const ImageIcon      = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
const ArticleIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>`;
const AlignLeftIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>`;
const TagIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6Z" /></svg>`;
const LinkIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>`;
const UserIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;

// ── Types ─────────────────────────────────────────────────────────
interface Artikel {
    id:                  number;
    judul:               string;
    slug:                string;
    isi:                 string;
    kategori:            string;
    penulis:             string;
    foto?:               string;
    status:              'draft' | 'publish';
    tanggal_publikasi?:  string;
    tanggal_formatted?:  string;
    created_at:          string;
    updated_at:          string;
}

interface Props {
    artikel: Artikel;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',     href: '/admin/dashboard' },
    { title: 'Data Artikel',  href: '/admin/artikel' },
    { title: 'Detail Artikel', href: `/admin/artikel/${props.artikel?.id}` },
];

// ── Helpers ───────────────────────────────────────────────────────
const getFotoUrl = (foto?: string) => foto ? `/storage/${foto}` : null;

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

const getStatusColor = (status: string) => {
    switch (status) {
        case 'publish': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'draft':   return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300';
        default:        return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const getStatusDot = (status: string) => {
    switch (status) {
        case 'publish': return 'bg-green-500';
        case 'draft':   return 'bg-orange-400';
        default:        return 'bg-gray-400';
    }
};

// ── Computed ──────────────────────────────────────────────────────
const fotoUrl = computed(() => getFotoUrl(props.artikel.foto));

// Format isi artikel menjadi paragraf HTML
const formattedContent = computed(() => {
    if (!props.artikel.isi) return '';
    return props.artikel.isi
        .split(/\n\s*\n/)
        .map(p => p.trim())
        .filter(p => p.length > 0)
        .map(p => `<p class="mb-4 leading-relaxed text-gray-900 dark:text-white">${p.replace(/\n/g, '<br>')}</p>`)
        .join('');
});

// Hitung kata
const wordCount = computed(() => {
    if (!props.artikel.isi) return 0;
    return props.artikel.isi.trim().split(/\s+/).length;
});

// Estimasi menit baca (200 kata/menit)
const readingTime = computed(() => Math.max(1, Math.ceil(wordCount.value / 200)));
</script>

<template>
    <Head :title="`Detail Artikel - ${artikel.judul}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- ── Header ─────────────────────────────────────── -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                            {{ artikel.judul }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">
                            Detail informasi artikel dan konten
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <!-- Status badge -->
                            <span :class="getStatusColor(artikel.status)"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium">
                                <span :class="getStatusDot(artikel.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                {{ artikel.status === 'publish' ? 'Publish' : 'Draft' }}
                            </span>
                            <!-- Kategori -->
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                <span v-html="TagIcon()"></span>
                                {{ artikel.kategori }}
                            </span>
                            <!-- Penulis -->
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                <span v-html="UserIcon()"></span>
                                {{ artikel.penulis }}
                            </span>
                            <span v-if="artikel.tanggal_formatted || artikel.tanggal_publikasi"
                                class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <span v-html="CalendarIcon()"></span>
                                {{ artikel.tanggal_formatted ?? formatDate(artikel.tanggal_publikasi!) }}
                            </span>
                        </div>
                    </div>
                    <Link :href="route('admin.artikel.edit', artikel.id)"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Artikel
                    </Link>
                </div>

                <!-- ── BARIS 1: Foto + Info Dasar ─────────────────── -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

                    <!-- Foto Artikel -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    Foto Artikel
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Gambar ilustrasi artikel</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <div v-if="fotoUrl" class="w-full">
                                    <img :src="fotoUrl" :alt="artikel.judul"
                                        class="w-full max-h-64 rounded-xl object-cover border border-gray-200 dark:border-gray-700 ring-1 ring-black/5 dark:ring-white/10" />
                                    <div class="mt-3 text-center">
                                        <a :href="fotoUrl" target="_blank"
                                            class="inline-flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                            <span v-html="LinkIcon()"></span>
                                            Lihat foto asli
                                        </a>
                                    </div>
                                </div>
                                <div v-else
                                    class="w-full h-48 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex flex-col items-center justify-center gap-3">
                                    <span v-html="ImageIcon()" class="text-gray-300 dark:text-gray-600"></span>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada foto</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Artikel -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="ArticleIcon()"></span>
                                    Informasi Artikel
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Detail lengkap tentang artikel ini</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                    <!-- Judul -->
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Judul Artikel</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white leading-snug">{{ artikel.judul }}</p>
                                    </div>

                                    <!-- Kategori -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Kategori</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="TagIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ artikel.kategori }}</p>
                                        </div>
                                    </div>

                                    <!-- Penulis -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Penulis</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="UserIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ artikel.penulis }}</p>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
                                        <span :class="getStatusColor(artikel.status)"
                                            class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                            <span :class="getStatusDot(artikel.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                            {{ artikel.status === 'publish' ? 'Publish' : 'Draft' }}
                                        </span>
                                    </div>

                                    <!-- Tanggal Publikasi -->
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Publikasi</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="CalendarIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ artikel.tanggal_formatted ?? (artikel.tanggal_publikasi ? formatDate(artikel.tanggal_publikasi) : 'Belum dijadwalkan') }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Slug -->
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Slug URL</p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span v-html="LinkIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <p class="text-sm font-mono text-gray-700 dark:text-gray-300 truncate" :title="artikel.slug">{{ artikel.slug }}</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── BARIS 2: Info Sistem + Isi Artikel ─────────── -->
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
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(artikel.created_at) }}</p>
                                </div>

                                <!-- Diperbarui -->
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(artikel.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Isi Artikel -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="AlignLeftIcon()"></span>
                                    Isi Artikel
                                </h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Konten lengkap artikel</p>
                            </div>
                            <div class="p-6">
                                <div v-if="artikel.isi"
                                    v-html="formattedContent"
                                    class="text-sm leading-relaxed text-gray-900 dark:text-white break-words"
                                    style="word-wrap: break-word; overflow-wrap: break-word;">
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <span v-html="AlignLeftIcon()" class="text-gray-300 dark:text-gray-600 w-8 h-8"></span>
                                    <p class="mt-3 text-sm text-gray-400 dark:text-gray-500 italic">Tidak ada isi artikel</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Actions ────────────────────────────────────── -->
                <div class="pb-4">
                    <Link :href="route('admin.artikel.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar Artikel
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
