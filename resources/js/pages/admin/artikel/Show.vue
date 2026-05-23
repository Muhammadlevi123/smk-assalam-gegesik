<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon    = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const CalendarIcon     = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>`;
const ImageIcon        = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
const ArticleIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>`;
const AlignLeftIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>`;
const TagIcon          = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6Z" /></svg>`;
const LinkIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>`;
const UserIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const GalleryIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" /></svg>`;
const XMarkIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
const ChevronLeftIcon  = () => `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>`;
const ChevronRightIcon = () => `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>`;

interface Artikel {
    id:                  number;
    judul:               string;
    slug:                string;
    isi:                 string;
    kategori:            string;
    penulis:             string;
    foto?:               string;
    images?:             string[];
    status:              'draft' | 'publish';
    tanggal_publikasi?:  string;
    tanggal_formatted?:  string;
    created_at:          string;
    updated_at:          string;
}

interface Props { artikel: Artikel; }
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',      href: '/admin/dashboard' },
    { title: 'Data Artikel',   href: '/admin/artikel' },
    { title: 'Detail Artikel', href: `/admin/artikel/${props.artikel?.id}` },
];

const getFotoUrl    = (foto?: string) => foto ? `/storage/${foto}` : null;
const formatDate    = (d: string) => { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }); };
const formatDateTime = (d: string) => { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }); };
const getStatusColor = (s: string) => s === 'publish' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300';
const getStatusDot   = (s: string) => s === 'publish' ? 'bg-green-500' : 'bg-orange-400';

const fotoUrl     = computed(() => getFotoUrl(props.artikel.foto));
const extraImages = computed(() => (props.artikel.images ?? []).map(path => `/storage/${path}`));
const allImages   = computed(() => { const list: string[] = []; if (fotoUrl.value) list.push(fotoUrl.value); list.push(...extraImages.value); return list; });

const formattedContent = computed(() => {
    if (!props.artikel.isi) return '';
    // Jika isi sudah HTML dari Tiptap, render langsung
    if (props.artikel.isi.startsWith('<')) return props.artikel.isi;
    return props.artikel.isi.split(/\n\s*\n/).map(p => p.trim()).filter(p => p.length > 0)
        .map(p => `<p class="mb-4 leading-relaxed">${p.replace(/\n/g, '<br>')}</p>`).join('');
});

const wordCount   = computed(() => { if (!props.artikel.isi) return 0; return props.artikel.isi.replace(/<[^>]*>/g, '').trim().split(/\s+/).length; });
const readingTime = computed(() => Math.max(1, Math.ceil(wordCount.value / 200)));

// ── Lightbox ──────────────────────────────────────────────────────
const lightboxOpen  = ref(false);
const lightboxIndex = ref(0);
const openLightbox  = (index: number) => { lightboxIndex.value = index; lightboxOpen.value = true; document.body.style.overflow = 'hidden'; };
const closeLightbox = () => { lightboxOpen.value = false; document.body.style.overflow = ''; };
const prevImage     = () => { lightboxIndex.value = (lightboxIndex.value - 1 + allImages.value.length) % allImages.value.length; };
const nextImage     = () => { lightboxIndex.value = (lightboxIndex.value + 1) % allImages.value.length; };
const onKeydown     = (e: KeyboardEvent) => { if (!lightboxOpen.value) return; if (e.key === 'Escape') closeLightbox(); if (e.key === 'ArrowLeft') prevImage(); if (e.key === 'ArrowRight') nextImage(); };
onMounted(()  => window.addEventListener('keydown', onKeydown));
onUnmounted(() => { window.removeEventListener('keydown', onKeydown); document.body.style.overflow = ''; });
</script>

<template>
    <Head :title="`Detail Artikel - ${artikel.judul}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">{{ artikel.judul }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Detail informasi artikel dan konten</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span :class="getStatusColor(artikel.status)" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium">
                                <span :class="getStatusDot(artikel.status)" class="h-1.5 w-1.5 rounded-full"></span>
                                {{ artikel.status === 'publish' ? 'Publish' : 'Draft' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"><span v-html="TagIcon()"></span>{{ artikel.kategori }}</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300"><span v-html="UserIcon()"></span>{{ artikel.penulis }}</span>
                            <span v-if="artikel.tanggal_formatted || artikel.tanggal_publikasi" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1"><span v-html="CalendarIcon()"></span>{{ artikel.tanggal_formatted ?? formatDate(artikel.tanggal_publikasi!) }}</span>
                            <span v-if="extraImages.length > 0" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"><span v-html="GalleryIcon()"></span>{{ extraImages.length }} foto tambahan</span>
                        </div>
                    </div>
                    <Link :href="route('admin.artikel.edit', artikel.id)" class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>Edit Artikel
                    </Link>
                </div>

                <!-- Baris 1: Foto + Info -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <!-- Foto Utama -->
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>Foto Utama</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Cover / thumbnail artikel</p>
                            </div>
                            <div class="flex flex-col items-center gap-4 p-6">
                                <div v-if="fotoUrl" class="w-full">
                                    <img :src="fotoUrl" :alt="artikel.judul" class="w-full max-h-64 rounded-xl object-cover border border-gray-200 dark:border-gray-700 ring-1 ring-black/5 dark:ring-white/10 cursor-pointer hover:opacity-90 transition-opacity" @click="openLightbox(0)" />
                                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 text-center">Klik untuk perbesar</p>
                                </div>
                                <div v-else class="w-full h-48 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex flex-col items-center justify-center gap-3">
                                    <span v-html="ImageIcon()" class="text-gray-300 dark:text-gray-600"></span>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada foto utama</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Artikel -->
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2"><span v-html="ArticleIcon()"></span>Informasi Artikel</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Detail lengkap tentang artikel ini</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Judul Artikel</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white leading-snug">{{ artikel.judul }}</p>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Kategori</p>
                                        <div class="mt-1 flex items-center gap-1.5"><span v-html="TagIcon()" class="text-gray-400 flex-shrink-0"></span><p class="text-sm font-semibold text-gray-900 dark:text-white">{{ artikel.kategori }}</p></div>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Penulis</p>
                                        <div class="mt-1 flex items-center gap-1.5"><span v-html="UserIcon()" class="text-gray-400 flex-shrink-0"></span><p class="text-sm font-semibold text-gray-900 dark:text-white">{{ artikel.penulis }}</p></div>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
                                        <span :class="getStatusColor(artikel.status)" class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"><span :class="getStatusDot(artikel.status)" class="h-1.5 w-1.5 rounded-full"></span>{{ artikel.status === 'publish' ? 'Publish' : 'Draft' }}</span>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal Publikasi</p>
                                        <div class="mt-1 flex items-center gap-1.5"><span v-html="CalendarIcon()" class="text-gray-400 flex-shrink-0"></span><p class="text-sm font-medium text-gray-900 dark:text-white">{{ artikel.tanggal_formatted ?? (artikel.tanggal_publikasi ? formatDate(artikel.tanggal_publikasi) : 'Belum dijadwalkan') }}</p></div>
                                    </div>
                                    <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Foto Tambahan</p>
                                        <div class="mt-1 flex items-center gap-1.5"><span v-html="GalleryIcon()" class="text-gray-400 flex-shrink-0"></span><p class="text-sm font-medium text-gray-900 dark:text-white">{{ extraImages.length > 0 ? `${extraImages.length} foto` : 'Tidak ada' }}</p></div>
                                    </div>
                                    <div class="sm:col-span-2 rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Slug URL</p>
                                        <div class="mt-1 flex items-center gap-1.5"><span v-html="LinkIcon()" class="text-gray-400 flex-shrink-0"></span><p class="text-sm font-mono text-gray-700 dark:text-gray-300 truncate" :title="artikel.slug">{{ artikel.slug }}</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Galeri Foto Tambahan -->
                <div v-if="extraImages.length > 0" class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2"><span v-html="GalleryIcon()"></span>Galeri Foto Tambahan</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ extraImages.length }} foto · Klik untuk memperbesar</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                            <div v-for="(url, index) in extraImages" :key="index"
                                class="group relative rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 aspect-square cursor-pointer"
                                @click="openLightbox(fotoUrl ? index + 1 : index)">
                                <img :src="url" :alt="`Foto ${index + 1}`" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris 2: Info Sistem + Isi -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div class="xl:col-span-1">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2"><span v-html="CalendarIcon()"></span>Informasi Sistem</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Audit trail</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Dibuat</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(artikel.created_at) }}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Terakhir Diperbarui</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(artikel.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-2">
                        <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2"><span v-html="AlignLeftIcon()"></span>Isi Artikel</h3>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Konten lengkap artikel</p>
                            </div>
                            <div class="p-6">
                                <div v-if="artikel.isi" v-html="formattedContent"
                                    class="text-sm leading-relaxed text-gray-900 dark:text-white break-words
                                    [&_p]:mb-3 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-5 [&_h2]:mb-2
                                    [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:mt-4 [&_h3]:mb-2
                                    [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-3 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:mb-3
                                    [&_li]:mb-1 [&_strong]:font-bold [&_em]:italic [&_u]:underline [&_s]:line-through
                                    [&_blockquote]:border-l-4 [&_blockquote]:border-blue-400 [&_blockquote]:bg-blue-50 [&_blockquote]:dark:bg-blue-900/10 [&_blockquote]:pl-4 [&_blockquote]:py-2 [&_blockquote]:italic [&_blockquote]:text-gray-600 [&_blockquote]:dark:text-gray-400 [&_blockquote]:my-3 [&_blockquote]:rounded-r-lg
                                    [&_hr]:border-gray-200 [&_hr]:dark:border-gray-700 [&_hr]:my-4
                                    [&_a]:text-blue-600 [&_a]:underline [&_img]:rounded-lg [&_img]:max-w-full [&_img]:my-3"
                                    style="word-wrap:break-word;overflow-wrap:break-word;">
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <span v-html="AlignLeftIcon()" class="text-gray-300 dark:text-gray-600 w-8 h-8"></span>
                                    <p class="mt-3 text-sm text-gray-400 dark:text-gray-500 italic">Tidak ada isi artikel</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pb-4">
                    <Link :href="route('admin.artikel.index')" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        <span v-html="ArrowLeftIcon()"></span>Kembali ke Daftar Artikel
                    </Link>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div v-if="lightboxOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm" @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-4 right-4 z-10 rounded-full bg-white/10 hover:bg-white/20 p-2 text-white transition-colors"><span v-html="XMarkIcon()"></span></button>
                <div class="absolute top-4 left-1/2 -translate-x-1/2 z-10 bg-black/50 text-white text-sm font-medium px-3 py-1 rounded-full">{{ lightboxIndex + 1 }} / {{ allImages.length }}</div>
                <button v-if="allImages.length > 1" @click="prevImage" class="absolute left-4 z-10 rounded-full bg-white/10 hover:bg-white/20 p-2 text-white transition-colors"><span v-html="ChevronLeftIcon()"></span></button>
                <img :src="allImages[lightboxIndex]" :alt="`Foto ${lightboxIndex + 1}`" class="max-h-[85vh] max-w-[90vw] rounded-xl object-contain shadow-2xl select-none" />
                <button v-if="allImages.length > 1" @click="nextImage" class="absolute right-4 z-10 rounded-full bg-white/10 hover:bg-white/20 p-2 text-white transition-colors"><span v-html="ChevronRightIcon()"></span></button>
                <div v-if="allImages.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 px-4 overflow-x-auto max-w-[90vw]">
                    <button v-for="(url, i) in allImages" :key="i" @click="lightboxIndex = i" :class="i === lightboxIndex ? 'ring-2 ring-white opacity-100' : 'opacity-50 hover:opacity-80'" class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden transition-all"><img :src="url" :alt="`Thumb ${i + 1}`" class="w-full h-full object-cover" /></button>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
