<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// ── Icons ─────────────────────────────────────────────────────────
const EyeIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
const SearchIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const PlusIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const TrashIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const CalendarIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>`;
const ImageIcon       = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
const UserIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const ExclamationIcon = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`;
const XMarkIcon       = () => `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
const CheckIcon       = () => `<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`;

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
    tahun?:              number;
    created_at:          string;
    updated_at:          string;
}

interface Props {
    artikel: {
        data:         Artikel[];
        current_page: number;
        last_page:    number;
        per_page:     number;
        total:        number;
        links:        Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?:   string;
        kategori?: string;
        status?:   string;
        penulis?:  string;
        tahun?:    string;
    };
    kategoriList: Array<{ value: string; label: string }>;
    penulisList:  Array<{ value: string; label: string }>;
    tahunList:    Array<{ value: number; label: number }>;
    statusList:   Array<{ value: string; label: string }>;
}

// ── Setup ─────────────────────────────────────────────────────────
const props = defineProps<Props>();
const page  = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',    href: '/admin/dashboard' },
    { title: 'Data Artikel', href: '/admin/artikel' },
];

// ── State ─────────────────────────────────────────────────────────
const showDeleteModal        = ref(false);
const showSuccessCreatePopup = ref(false);
const showSuccessDeletePopup = ref(false);
const showSuccessUpdatePopup = ref(false);
const showErrorDeletePopup   = ref(false);
const selectedArtikel        = ref<Artikel | null>(null);
const deleteErrorMessage     = ref('');
let countdown: number | null  = null;

// ── Search & Filter ───────────────────────────────────────────────
const searchForm = useForm({
    search:   props.filters?.search   || '',
    kategori: props.filters?.kategori || '',
    status:   props.filters?.status   || '',
    penulis:  props.filters?.penulis  || '',
    tahun:    props.filters?.tahun    || '',
});

const deleteForm = useForm({});

const debounce = (func: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => { clearTimeout(timeoutId); timeoutId = setTimeout(() => func.apply(null, args), delay); };
};

const debouncedSearch = debounce(() => {
    searchForm.get('/admin/artikel', { preserveState: true, preserveScroll: true });
}, 300);

watch(
    [() => searchForm.search, () => searchForm.kategori, () => searchForm.status,
     () => searchForm.penulis, () => searchForm.tahun],
    () => { debouncedSearch(); }
);

const clearAllFilters = () => {
    searchForm.search   = '';
    searchForm.kategori = '';
    searchForm.status   = '';
    searchForm.penulis  = '';
    searchForm.tahun    = '';
    searchForm.get('/admin/artikel', { preserveState: true, preserveScroll: true });
};

// ── Delete ────────────────────────────────────────────────────────
const deleteArtikel = (artikel: Artikel) => { selectedArtikel.value = artikel; showDeleteModal.value = true; };

const confirmDelete = () => {
    if (!selectedArtikel.value) return;
    deleteForm.delete(`/admin/artikel/${selectedArtikel.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value        = false;
            showSuccessDeletePopup.value = true;
            selectedArtikel.value        = null;
            startAutoClose(closeSuccessDeletePopup);
        },
        onError: (errors: Record<string, string | string[]>) => {
            showDeleteModal.value    = false;
            deleteErrorMessage.value = errors.delete_error
                ? (Array.isArray(errors.delete_error) ? errors.delete_error[0] : errors.delete_error)
                : 'Terjadi kesalahan saat menghapus artikel.';
            showErrorDeletePopup.value = true;
            startAutoClose(closeErrorDeletePopup, 4000);
            selectedArtikel.value = null;
        },
        onFinish: () => { deleteForm.clearErrors(); },
    });
};

// ── Helpers ───────────────────────────────────────────────────────
const getFotoUrl = (foto?: string) => foto ? `/storage/${foto}` : null;

const getStatusColor = (status: string) => {
    switch (status) {
        case 'publish': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'draft':   return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300';
        default:        return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const truncateText = (text: string, max = 100) =>
    text.length <= max ? text : text.substring(0, max) + '...';

// ── Computed ──────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
    !!(props.filters?.search || props.filters?.kategori || props.filters?.status ||
       props.filters?.penulis || props.filters?.tahun)
);

const countPublish = computed(() => props.artikel.data.filter(a => a.status === 'publish').length);
const countDraft   = computed(() => props.artikel.data.filter(a => a.status === 'draft').length);

// ── Popups ────────────────────────────────────────────────────────
const clearCountdown = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose = (fn: () => void, delay = 1500) => { countdown = setTimeout(fn, delay); };

const closeSuccessCreatePopup = () => { showSuccessCreatePopup.value = false; clearCountdown(); };
const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };
const closeErrorDeletePopup   = () => { showErrorDeletePopup.value = false; deleteErrorMessage.value = ''; clearCountdown(); };

// Flash via Inertia — sama persis pola Berita/Prestasi
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (!flash?.success) return;
        if (flash.success === 'created')      { showSuccessCreatePopup.value = true; startAutoClose(closeSuccessCreatePopup); }
        else if (flash.success === 'updated') { showSuccessUpdatePopup.value = true; startAutoClose(closeSuccessUpdatePopup); }
    },
    { immediate: true, deep: true }
);

onUnmounted(() => { clearCountdown(); });
</script>

<template>
    <Head title="Data Artikel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Data Artikel</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Kelola artikel dan konten edukasi sekolah</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                <span>{{ artikel.total }} Total Artikel</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-indigo-500"></div>
                                <span>{{ penulisList.length }} Penulis</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                <span>{{ countPublish }} Publish</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                                <span>{{ countDraft }} Draft</span>
                            </div>
                            <div v-if="hasActiveFilters" class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-red-500"></div>
                                <span>{{ artikel.data.length }} Terfilter</span>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('admin.artikel.create')"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto sm:px-6 sm:py-3">
                        <span v-html="PlusIcon()" class="transition-transform group-hover:scale-110"></span>
                        Tambah Artikel
                    </Link>
                </div>

                <!-- Filter Card -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex items-center gap-2">
                            <span v-html="FilterIcon()" class="text-gray-500 dark:text-gray-400"></span>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pencarian & Filter</h3>
                            <div v-if="hasActiveFilters" class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">Aktif</div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-12 xl:gap-6">

                            <!-- Search -->
                            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Artikel</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4">
                                        <span v-html="SearchIcon()" class="text-gray-400"></span>
                                    </div>
                                    <input v-model="searchForm.search" type="text"
                                        placeholder="Cari berdasarkan judul, isi, kategori, penulis..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-12 sm:rounded-xl" />
                                </div>
                            </div>

                            <!-- Kategori -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
                                <div class="relative">
                                    <select v-model="searchForm.kategori"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Kategori</option>
                                        <option v-for="k in kategoriList" :key="k.value" :value="k.value">{{ k.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Penulis -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Penulis</label>
                                <div class="relative">
                                    <select v-model="searchForm.penulis"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Penulis</option>
                                        <option v-for="p in penulisList" :key="p.value" :value="p.value">{{ p.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <div class="relative">
                                    <select v-model="searchForm.status"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Status</option>
                                        <option v-for="s in statusList" :key="s.value" :value="s.value">{{ s.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tahun -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tahun</label>
                                <div class="relative">
                                    <select v-model="searchForm.tahun"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Tahun</option>
                                        <option v-for="t in tahunList" :key="t.value" :value="t.value.toString()">{{ t.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Clear -->
                            <div v-if="hasActiveFilters" class="flex items-end xl:col-span-2">
                                <button @click="clearAllFilters"
                                    class="w-full rounded-lg bg-gray-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:rounded-xl sm:py-3">
                                    Bersihkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Menampilkan {{ artikel.data.length }} dari {{ artikel.total }} artikel
                            </p>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Halaman {{ artikel.current_page }} dari {{ artikel.last_page }}
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="artikel.data.length > 0" class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Foto</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Artikel</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Kategori</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Penulis</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tanggal Publikasi</th>
                                    <th class="relative px-6 py-4"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="item in artikel.data" :key="item.id"
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                                    <!-- Foto -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="h-14 w-14 flex-shrink-0">
                                            <img v-if="getFotoUrl(item.foto)" :src="getFotoUrl(item.foto)!" :alt="item.judul"
                                                class="h-14 w-14 rounded-lg object-cover border border-gray-200 dark:border-gray-700 ring-1 ring-black/5 dark:ring-white/10" />
                                            <div v-else class="h-14 w-14 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                                <span v-html="ImageIcon()" class="text-gray-300 dark:text-gray-600"></span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Judul & Isi -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.judul }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 max-w-xs truncate">{{ truncateText(item.isi, 80) }}</div>
                                    </td>

                                    <!-- Kategori -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ item.kategori }}
                                        </span>
                                    </td>

                                    <!-- Penulis -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span v-html="UserIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <span class="text-sm text-gray-900 dark:text-white">{{ item.penulis }}</span>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(item.status)"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ item.status === 'publish' ? 'Publish' : 'Draft' }}
                                        </span>
                                    </td>

                                    <!-- Tanggal Publikasi -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span v-html="CalendarIcon()" class="text-gray-400"></span>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ item.tanggal_formatted ?? '-' }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.artikel.show', item.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900"
                                                title="Lihat Detail">
                                                <span v-html="EyeIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link>
                                            <Link :href="route('admin.artikel.edit', item.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 dark:focus:ring-offset-gray-900"
                                                title="Edit">
                                                <span v-html="EditIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link>
                                            <button @click="deleteArtikel(item)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-all hover:bg-red-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 dark:focus:ring-offset-gray-900"
                                                title="Hapus">
                                                <span v-html="TrashIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div v-if="artikel.data.length > 0" class="lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="item in artikel.data" :key="item.id" class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <img v-if="getFotoUrl(item.foto)" :src="getFotoUrl(item.foto)!" :alt="item.judul"
                                        class="h-16 w-16 rounded-xl object-cover border border-gray-200 dark:border-gray-700 ring-1 ring-black/5 dark:ring-white/10" />
                                    <div v-else class="h-16 w-16 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                        <span v-html="ImageIcon()" class="text-gray-300 dark:text-gray-600"></span>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{ item.judul }}</h3>
                                    <div class="mt-1.5 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ item.kategori }}
                                        </span>
                                        <span :class="getStatusColor(item.status)"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                            {{ item.status === 'publish' ? 'Publish' : 'Draft' }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span v-html="UserIcon()" class="flex-shrink-0"></span>
                                        {{ item.penulis }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="item.isi" class="mt-2 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                {{ truncateText(item.isi, 120) }}
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Publikasi</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ item.tanggal_formatted ?? '-' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tahun</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ item.tahun ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <Link :href="route('admin.artikel.show', item.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    <span v-html="EyeIcon()"></span>Lihat
                                </Link>
                                <Link :href="route('admin.artikel.edit', item.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 py-2 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                                    <span v-html="EditIcon()"></span>Edit
                                </Link>
                                <button @click="deleteArtikel(item)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 py-2 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
                                    <span v-html="TrashIcon()"></span>Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center sm:p-16">
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 sm:h-20 sm:w-20">
                                <span class="text-2xl sm:text-3xl">📄</span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white sm:mt-6">Belum ada data artikel</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ hasActiveFilters ? 'Coba sesuaikan kriteria pencarian atau bersihkan filter.' : 'Mulai dengan menambahkan artikel pertama.' }}
                            </p>
                            <div class="mt-6 sm:mt-8">
                                <Link :href="route('admin.artikel.create')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:px-6 sm:py-3">
                                    <span v-html="PlusIcon()"></span>
                                    {{ hasActiveFilters ? 'Tambah Artikel Baru' : 'Tambah Artikel Pertama' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="artikel.last_page > 1"
                    class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:p-6 sm:rounded-2xl">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan
                        <span class="font-medium text-gray-900 dark:text-white">{{ ((artikel.current_page - 1) * artikel.per_page) + 1 }}</span>
                        sampai
                        <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(artikel.current_page * artikel.per_page, artikel.total) }}</span>
                        dari
                        <span class="font-medium text-gray-900 dark:text-white">{{ artikel.total }}</span>
                        hasil
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-1 sm:justify-start" aria-label="Navigasi halaman">
                        <template v-for="link in artikel.links" :key="link.label">
                            <Link v-if="link.url !== null" :href="link.url as string" v-html="link.label"
                                :class="['inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-all min-w-[2.5rem]',
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600'
                                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white']" />
                            <span v-else v-html="link.label"
                                class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg min-w-[2.5rem] bg-gray-50 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600" />
                        </template>
                    </nav>
                </div>

            </div>
        </div>

        <!-- ══ Delete Modal ══════════════════════════════════════════ -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            <div class="relative mx-4 w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 transition-all dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600 dark:text-red-400 scale-150"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Artikel</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Apakah Anda yakin ingin menghapus artikel
                        <span class="font-semibold">"{{ selectedArtikel?.judul }}"</span>
                        karya <span class="font-semibold">{{ selectedArtikel?.penulis }}</span>?
                    </p>
                    <div class="mt-2 rounded-lg bg-yellow-50 border border-yellow-200 p-3 dark:bg-yellow-900/20 dark:border-yellow-800">
                        <p class="text-xs text-yellow-800 dark:text-yellow-300">
                            <strong>Peringatan:</strong> Data artikel dan foto akan dihapus permanen dari sistem.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button @click="showDeleteModal = false"
                        class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        Batal
                    </button>
                    <button @click="confirmDelete" :disabled="deleteForm.processing"
                        class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-900">
                        <span v-if="deleteForm.processing">Menghapus...</span>
                        <span v-else>Hapus</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ Error Delete Popup ════════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showErrorDeletePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeErrorDeletePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4">
                            <button @click="closeErrorDeletePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                <span v-html="ExclamationIcon()" class="text-red-600 dark:text-red-400"></span>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gagal Menghapus Artikel!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ deleteErrorMessage }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Success Delete Popup ══════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessDeletePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessDeletePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessDeletePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <span v-html="CheckIcon()" class="text-green-600 dark:text-green-400"></span>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Artikel Berhasil Dihapus!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data artikel telah dihapus secara permanen dari sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Success Create Popup ══════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessCreatePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessCreatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessCreatePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <span v-html="CheckIcon()" class="text-green-600 dark:text-green-400"></span>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Artikel Berhasil Ditambahkan!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data artikel baru telah berhasil ditambahkan ke sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Success Update Popup ══════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessUpdatePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessUpdatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessUpdatePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <span v-html="CheckIcon()" class="text-green-600 dark:text-green-400"></span>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Artikel Berhasil Diperbarui!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Perubahan data artikel telah berhasil disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>
