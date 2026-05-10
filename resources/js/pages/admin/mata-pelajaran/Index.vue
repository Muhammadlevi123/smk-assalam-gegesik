<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// ── Icons ─────────────────────────────────────────────────────────
const SearchIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const PlusIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const TrashIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const EyeIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const BookIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>`;
const UserGroupIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>`;
const ExclamationIcon = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`;
const XMarkIcon       = () => `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
const CheckIcon       = () => `<svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`;

// ── Types ─────────────────────────────────────────────────────────
interface Pengajar {
    guru_id: number;
    guru_nama: string;
    guru_foto?: string;
    tahun_ajaran: string;
    tahun_ajaran_id: number;
    tahun_sort: number;
}

// Tipe setelah di-group: 1 guru → banyak tahun ajaran
interface PengajarGrouped {
    guru_id: number;
    guru_nama: string;
    guru_foto?: string;
    tahun_ajaran_list: string[]; // semua tahun ajaran guru ini, sudah unik & diurutkan
}

interface MataPelajaran {
    id: number;
    nama: string;
    pengajar: Pengajar[];
    jumlah_pengajar: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    mataPelajaran: {
        data: MataPelajaran[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?: string;
        tahun_ajaran?: string;
        guru?: string;
    };
    tahunAjaranList: Array<{ id: number; tahun: string }>;
}

// ── Setup ─────────────────────────────────────────────────────────
const props = defineProps<Props>();
const page  = usePage();

const showDeleteModal        = ref(false);
const showSuccessCreatePopup = ref(false);
const showSuccessDeletePopup = ref(false);
const showSuccessUpdatePopup = ref(false);
const showErrorDeletePopup   = ref(false);
const showPengajarModal      = ref(false);
const selectedMapel          = ref<MataPelajaran | null>(null);
const selectedMapelPengajar  = ref<MataPelajaran | null>(null);
const deleteErrorMessage     = ref('');
let countdown: number | null = null;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Mata Pelajaran', href: '/admin/mata-pelajaran' },
];

// ── Search & Filter ───────────────────────────────────────────────
const searchForm = useForm({
    search:       props.filters?.search       || '',
    tahun_ajaran: props.filters?.tahun_ajaran || '',
    guru:         props.filters?.guru         || '',
});

const deleteForm = useForm({});

const debounce = (func: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => { clearTimeout(timeoutId); timeoutId = setTimeout(() => func.apply(null, args), delay); };
};

const debouncedSearch = debounce(() => {
    searchForm.get('/admin/mata-pelajaran', { preserveState: true, preserveScroll: true });
}, 300);

watch(
    [() => searchForm.search, () => searchForm.tahun_ajaran, () => searchForm.guru],
    () => { debouncedSearch(); }
);

const clearAllFilters = () => {
    searchForm.search = ''; searchForm.tahun_ajaran = ''; searchForm.guru = '';
    searchForm.get('/admin/mata-pelajaran', { preserveState: true, preserveScroll: true });
};

// ── Delete ────────────────────────────────────────────────────────
const deleteMapel = (mapel: MataPelajaran) => { selectedMapel.value = mapel; showDeleteModal.value = true; };

const confirmDelete = () => {
    if (!selectedMapel.value) return;
    deleteForm.delete(`/admin/mata-pelajaran/${selectedMapel.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false; showSuccessDeletePopup.value = true;
            selectedMapel.value = null; startAutoClose(closeSuccessDeletePopup);
        },
        onError: (errors: Record<string, string | string[]>) => {
            showDeleteModal.value = false;
            deleteErrorMessage.value = errors.delete_error
                ? (Array.isArray(errors.delete_error) ? errors.delete_error[0] : errors.delete_error)
                : 'Mata pelajaran tidak dapat dihapus karena masih memiliki pengajaran aktif.';
            showErrorDeletePopup.value = true;
            startAutoClose(closeErrorDeletePopup, 4000);
            selectedMapel.value = null;
        },
        onFinish: () => { deleteForm.clearErrors(); },
    });
};

// ── Modal Pengajar ────────────────────────────────────────────────
const openPengajarModal  = (mapel: MataPelajaran) => { selectedMapelPengajar.value = mapel; showPengajarModal.value = true; };
const closePengajarModal = () => { showPengajarModal.value = false; selectedMapelPengajar.value = null; };

// ── Grouping pengajar per guru ────────────────────────────────────
// Dari flat list [guru A - 2025/2026, guru A - 2026/2027, guru B - 2025/2026]
// Menjadi grouped [guru A: [2025/2026, 2026/2027], guru B: [2025/2026]]
const pengajarGrouped = computed((): PengajarGrouped[] => {
    if (!selectedMapelPengajar.value?.pengajar?.length) return [];

    const map = new Map<number, PengajarGrouped>();

    for (const p of selectedMapelPengajar.value.pengajar) {
        if (!map.has(p.guru_id)) {
            map.set(p.guru_id, {
                guru_id:          p.guru_id,
                guru_nama:        p.guru_nama,
                guru_foto:        p.guru_foto,
                tahun_ajaran_list: [],
            });
        }
        const entry = map.get(p.guru_id)!;
        if (!entry.tahun_ajaran_list.includes(p.tahun_ajaran)) {
            entry.tahun_ajaran_list.push(p.tahun_ajaran);
        }
    }

    // Urutkan tahun ajaran dalam setiap guru: terbaru dulu
    for (const entry of map.values()) {
        entry.tahun_ajaran_list.sort((a, b) => {
            const aYear = parseInt(a.split('/')[0]);
            const bYear = parseInt(b.split('/')[0]);
            return bYear - aYear;
        });
    }

    return Array.from(map.values());
});

// Jumlah guru unik (bukan jumlah kombinasi)
const jumlahGuruUnik = computed(() => pengajarGrouped.value.length);

// ── Computed ──────────────────────────────────────────────────────
const hasActiveFilters     = computed(() => !!(props.filters?.search || props.filters?.tahun_ajaran || props.filters?.guru));
const mapelWithPengajar    = computed(() => props.mataPelajaran.data.filter(m => m.jumlah_pengajar > 0).length);
const mapelWithoutPengajar = computed(() => props.mataPelajaran.data.filter(m => m.jumlah_pengajar === 0).length);

// ── Popups ────────────────────────────────────────────────────────
const clearCountdown = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose = (fn: () => void, delay = 1500) => { countdown = setTimeout(fn, delay); };

const closeSuccessCreatePopup = () => { showSuccessCreatePopup.value = false; clearCountdown(); };
const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };
const closeErrorDeletePopup   = () => { showErrorDeletePopup.value = false; deleteErrorMessage.value = ''; clearCountdown(); };

watch(
    () => (page.props as any).flash,
    (flash) => {
        if (!flash?.success) return;
        if (flash.success === 'created') { showSuccessCreatePopup.value = true; startAutoClose(closeSuccessCreatePopup); }
        else if (flash.success === 'updated') { showSuccessUpdatePopup.value = true; startAutoClose(closeSuccessUpdatePopup); }
    },
    { immediate: true, deep: true }
);

onUnmounted(() => { clearCountdown(); });
</script>

<template>
    <Head title="Data Mata Pelajaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Data Mata Pelajaran</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Kelola data mata pelajaran dan pengajaran sekolah</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-green-500"></div><span>{{ mataPelajaran.total }} Total Mata Pelajaran</span></div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span>{{ mapelWithPengajar }} Dengan Pengajar</span></div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-orange-500"></div><span>{{ mapelWithoutPengajar }} Tanpa Pengajar</span></div>
                            <div v-if="hasActiveFilters" class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-red-500"></div><span>{{ mataPelajaran.data.length }} Terfilter</span></div>
                        </div>
                    </div>
                    <Link :href="route('admin.mata-pelajaran.create')"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto sm:px-6 sm:py-3">
                        <span v-html="PlusIcon()" class="transition-transform group-hover:scale-110"></span>
                        <span>Tambah Mata Pelajaran</span>
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
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:gap-6">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Mata Pelajaran</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4"><span v-html="SearchIcon()" class="text-gray-400"></span></div>
                                    <input v-model="searchForm.search" type="text" placeholder="Cari berdasarkan nama mata pelajaran..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-12 sm:rounded-xl" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tahun Ajaran</label>
                                <div class="relative">
                                    <select v-model="searchForm.tahun_ajaran"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Tahun</option>
                                        <option v-for="t in tahunAjaranList" :key="t.id" :value="t.tahun">{{ t.tahun }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Guru Pengajar</label>
                                <input v-model="searchForm.guru" type="text" placeholder="Cari nama guru..."
                                    class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:rounded-xl" />
                            </div>
                            <div v-if="hasActiveFilters" class="flex items-end">
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
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menampilkan {{ mataPelajaran.data.length }} dari {{ mataPelajaran.total }} mata pelajaran</p>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Halaman {{ mataPelajaran.current_page }} dari {{ mataPelajaran.last_page }}</div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="mataPelajaran.data.length > 0" class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Nama Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pengajar</th>
                                    <th class="relative px-6 py-4"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="mapel in mataPelajaran.data" :key="mapel.id"
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <span v-html="BookIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ mapel.nama }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button v-if="mapel.jumlah_pengajar > 0"
                                            @click="openPengajarModal(mapel)"
                                            class="group/pengajar inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 shadow-sm transition-all hover:bg-indigo-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/40 dark:focus:ring-offset-gray-900">
                                            <span v-html="UserGroupIcon()" class="transition-transform group-hover/pengajar:scale-110"></span>
                                            {{ mapel.jumlah_pengajar }} Pengajar
                                        </button>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- <Link :href="route('admin.mata-pelajaran.show', mapel.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900" title="Lihat Detail">
                                                <span v-html="EyeIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link> -->
                                            <Link :href="route('admin.mata-pelajaran.edit', mapel.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 dark:focus:ring-offset-gray-900" title="Edit">
                                                <span v-html="EditIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link>
                                            <button @click="deleteMapel(mapel)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-all hover:bg-red-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 dark:focus:ring-offset-gray-900" title="Hapus">
                                                <span v-html="TrashIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div v-if="mataPelajaran.data.length > 0" class="lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="mapel in mataPelajaran.data" :key="mapel.id" class="p-4">
                            <div class="flex items-start gap-3">
                                <span v-html="BookIcon()" class="text-gray-400 mt-1 flex-shrink-0"></span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{ mapel.nama }}</h3>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pengajar</p>
                                    <button v-if="mapel.jumlah_pengajar > 0" @click="openPengajarModal(mapel)"
                                        class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400">
                                        <span v-html="UserGroupIcon()"></span>{{ mapel.jumlah_pengajar }} Pengajar
                                    </button>
                                    <span v-else class="text-sm font-medium text-gray-900 dark:text-white">-</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <!-- <Link :href="route('admin.mata-pelajaran.show', mapel.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    <span v-html="EyeIcon()"></span>Lihat
                                </Link> -->
                                <Link :href="route('admin.mata-pelajaran.edit', mapel.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 py-2 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                                    <span v-html="EditIcon()"></span>Edit
                                </Link>
                                <button @click="deleteMapel(mapel)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 py-2 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
                                    <span v-html="TrashIcon()"></span>Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center sm:p-16">
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 sm:h-20 sm:w-20"><span class="text-2xl sm:text-3xl">📚</span></div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white sm:mt-6">Belum ada data mata pelajaran</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ hasActiveFilters ? 'Coba sesuaikan kriteria pencarian atau bersihkan filter.' : 'Mulai dengan menambahkan mata pelajaran pertama.' }}</p>
                            <div class="mt-6 sm:mt-8">
                                <Link :href="route('admin.mata-pelajaran.create')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:px-6 sm:py-3">
                                    <span v-html="PlusIcon()"></span>
                                    {{ hasActiveFilters ? 'Tambah Mata Pelajaran Baru' : 'Tambah Mata Pelajaran Pertama' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="mataPelajaran.last_page > 1"
                    class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:p-6 sm:rounded-2xl">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan
                        <span class="font-medium text-gray-900 dark:text-white">{{ ((mataPelajaran.current_page - 1) * mataPelajaran.per_page) + 1 }}</span>
                        sampai
                        <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(mataPelajaran.current_page * mataPelajaran.per_page, mataPelajaran.total) }}</span>
                        dari
                        <span class="font-medium text-gray-900 dark:text-white">{{ mataPelajaran.total }}</span>
                        hasil
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-1 sm:justify-start" aria-label="Navigasi halaman">
                        <template v-for="link in mataPelajaran.links" :key="link.label">
                            <Link v-if="link.url !== null" :href="link.url as string" v-html="link.label"
                                :class="['inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-all min-w-[2.5rem]',
                                    link.active ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white']" />
                            <span v-else v-html="link.label"
                                class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg min-w-[2.5rem] bg-gray-50 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600" />
                        </template>
                    </nav>
                </div>

            </div>
        </div>

        <!-- ══ Modal Pengajar — grouped per guru dengan badge tahun ajaran ══ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showPengajarModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="closePengajarModal">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

                <Transition enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                    appear>
                    <div class="relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Guru Pengajar</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ selectedMapelPengajar?.nama }}</p>
                            </div>
                            <button @click="closePengajarModal"
                                class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>

                        <!-- Body — 1 baris per guru, tahun ajaran sebagai badge -->
                        <div class="max-h-[60vh] overflow-y-auto p-6">

                            <div v-if="pengajarGrouped.length > 0" class="space-y-0 divide-y divide-gray-100 dark:divide-gray-800">
                                <div v-for="g in pengajarGrouped" :key="g.guru_id"
                                    class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">

                                    <!-- Foto guru -->
                                    <img v-if="g.guru_foto" :src="`/storage/${g.guru_foto}`" :alt="g.guru_nama"
                                        class="h-10 w-10 rounded-lg object-cover border border-gray-200 bg-gray-100 flex-shrink-0 dark:border-gray-600 dark:bg-gray-700" />
                                    <div v-else
                                        class="h-10 w-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center flex-shrink-0">
                                        <span v-html="UserGroupIcon()" class="text-indigo-500 dark:text-indigo-400" style="width:16px;height:16px;"></span>
                                    </div>

                                    <!-- Nama + badge tahun ajaran -->
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ g.guru_nama }}</p>
                                        <!-- Badge tahun ajaran — semua tahun dalam 1 baris, terbaru dulu -->
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span v-for="tahun in g.tahun_ajaran_list" :key="tahun"
                                                class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ tahun }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kosong -->
                            <div v-else class="flex flex-col items-center justify-center py-12">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <span v-html="UserGroupIcon()" class="text-gray-400" style="width:22px;height:22px;"></span>
                                </div>
                                <p class="mt-3 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada pengajar untuk mata pelajaran ini</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/80 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ jumlahGuruUnik }} guru</span>
                            <button @click="closePengajarModal"
                                class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
                                Tutup
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- ══ Delete Modal ══════════════════════════════════════════ -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            <div class="relative mx-4 w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 transition-all dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600 dark:text-red-400"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Mata Pelajaran</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">Apakah Anda yakin ingin menghapus mata pelajaran <span class="font-semibold">"{{ selectedMapel?.nama }}"</span>?</p>
                    <div class="mt-2 rounded-lg bg-yellow-50 border border-yellow-200 p-3 dark:bg-yellow-900/20 dark:border-yellow-800">
                        <p class="text-xs text-yellow-800 dark:text-yellow-300"><strong>Peringatan:</strong> Mata pelajaran tidak dapat dihapus jika masih memiliki pengajaran aktif.</p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">Batal</button>
                    <button @click="confirmDelete" :disabled="deleteForm.processing" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-900">
                        <span v-if="deleteForm.processing">Menghapus...</span><span v-else>Hapus</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ Error Delete Popup ════════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showErrorDeletePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeErrorDeletePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4"><button @click="closeErrorDeletePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"><span v-html="XMarkIcon()"></span></button></div>
                        <div class="flex items-center justify-center"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20"><span v-html="ExclamationIcon()" class="text-red-600 dark:text-red-400"></span></div></div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gagal Menghapus Mata Pelajaran!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ deleteErrorMessage }}</p>
                            <div class="mt-3 rounded-lg bg-yellow-50 border border-yellow-200 p-3 dark:bg-yellow-900/20 dark:border-yellow-800"><p class="text-xs text-yellow-800 dark:text-yellow-300"><strong>Saran:</strong> Hapus semua pengajaran dari mata pelajaran ini terlebih dahulu.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Success Delete Popup ══════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessDeletePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessDeletePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4"><button @click="closeSuccessDeletePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"><span v-html="XMarkIcon()"></span></button></div>
                        <div class="flex items-center justify-center"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20"><span v-html="CheckIcon()" class="text-green-600 dark:text-green-400"></span></div></div>
                        <div class="mt-4 text-center"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mata Pelajaran Berhasil Dihapus!</h3><p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data mata pelajaran telah dihapus secara permanen dari sistem.</p></div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Success Create Popup ══════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessCreatePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessCreatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4"><button @click="closeSuccessCreatePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"><span v-html="XMarkIcon()"></span></button></div>
                        <div class="flex items-center justify-center"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20"><span v-html="CheckIcon()" class="text-green-600 dark:text-green-400"></span></div></div>
                        <div class="mt-4 text-center"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mata Pelajaran Berhasil Ditambahkan!</h3><p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data mata pelajaran baru telah berhasil ditambahkan ke sistem.</p></div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Success Update Popup ══════════════════════════════════ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessUpdatePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessUpdatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm w-full">
                        <div class="absolute right-4 top-4"><button @click="closeSuccessUpdatePopup" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"><span v-html="XMarkIcon()"></span></button></div>
                        <div class="flex items-center justify-center"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20"><span v-html="CheckIcon()" class="text-green-600 dark:text-green-400"></span></div></div>
                        <div class="mt-4 text-center"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Mata Pelajaran Berhasil Diperbarui!</h3><p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Perubahan data mata pelajaran telah berhasil disimpan.</p></div>
                    </div>
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>
