<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const showDeleteModal        = ref(false);
const showSuccessCreatePopup = ref(false);
const showSuccessDeletePopup = ref(false);
const showSuccessUpdatePopup = ref(false);
const selectedAlumni         = ref<Alumni | null>(null);
let countdown: number | null = null;

// ── Icons ─────────────────────────────────────────────────────────
const SearchIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const PlusIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const TrashIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const EyeIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const BriefcaseIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" /></svg>`;
const GraduationIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443a55.381 55.381 0 0 1 5.25 2.882V15" /></svg>`;

// ── Types ─────────────────────────────────────────────────────────
interface Alumni {
    id:               number;
    siswa_id:         number;
    pekerjaan:        string;
    foto?:            string;
    alamat_sekarang?: string;
    no_telepon?:      string;
    email?:           string;
    tahun_lulus?:     string | number;
    // Accessor dari model
    nama?:            string;
    nis?:             string;
    angkatan?:        string;
    jenis_kelamin?:   string;
    created_at:       string;
    updated_at:       string;
}

interface Props {
    alumni: {
        data:         Alumni[];
        current_page: number;
        last_page:    number;
        per_page:     number;
        total:        number;
        links:        Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?:    string;
        angkatan?:  string;
        pekerjaan?: string;
    };
    angkatanList: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const page  = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',   href: '/admin/dashboard' },
    { title: 'Data Alumni', href: '/admin/alumni' },
];

// ── Search & Filter ───────────────────────────────────────────────
const searchForm = useForm({
    search:    props.filters?.search    || '',
    angkatan:  props.filters?.angkatan  || '',
    pekerjaan: props.filters?.pekerjaan || '',
});

const deleteForm = useForm({});

const debounce = (func: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(null, args), delay);
    };
};

const debouncedSearch = debounce(() => {
    searchForm.get('/admin/alumni', { preserveState: true, preserveScroll: true });
}, 300);

watch(
    [() => searchForm.search, () => searchForm.angkatan, () => searchForm.pekerjaan],
    () => { debouncedSearch(); }
);

const clearAllFilters = () => {
    searchForm.search    = '';
    searchForm.angkatan  = '';
    searchForm.pekerjaan = '';
    searchForm.get('/admin/alumni', { preserveState: true, preserveScroll: true });
};

// ── Delete ────────────────────────────────────────────────────────
const deleteAlumni = (alumni: Alumni) => {
    selectedAlumni.value  = alumni;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!selectedAlumni.value) return;
    deleteForm.delete(`/admin/alumni/${selectedAlumni.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value        = false;
            showSuccessDeletePopup.value = true;
            selectedAlumni.value         = null;
            startAutoClose(closeSuccessDeletePopup);
        },
    });
};

// ── Helpers ───────────────────────────────────────────────────────
const getPhotoUrl    = (foto?: string) => foto ? `/storage/${foto}` : '/images/default-avatar.png';
const getAlumniName  = (a: Alumni | null) => a?.nama  || '-';
const getAlumniNis   = (a: Alumni | null) => a?.nis   || '-';
const getAlumniAngkatan    = (a: Alumni | null) => a?.angkatan     || '-';
const getAlumniJenisKelamin = (a: Alumni | null) => a?.jenis_kelamin || '-';
const getTahunLulus  = (a: Alumni) => a.tahun_lulus ? String(a.tahun_lulus) : '-';

// ── Computed ──────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
    !!(props.filters?.search || props.filters?.angkatan || props.filters?.pekerjaan)
);

const countLakiLaki  = computed(() => props.alumni.data.filter(a => getAlumniJenisKelamin(a) === 'Laki-laki').length);
const countPerempuan = computed(() => props.alumni.data.filter(a => getAlumniJenisKelamin(a) === 'Perempuan').length);

const countRecentGraduates = computed(() => {
    const currentYear = new Date().getFullYear();
    return props.alumni.data.filter(a => {
        const tl = getTahunLulus(a);
        if (tl === '-') return false;
        // Format bisa "2023/2024" atau "2023"
        const year = parseInt(tl.split('/')[0]);
        return !isNaN(year) && year >= currentYear - 3;
    }).length;
});

// ── Popups ────────────────────────────────────────────────────────
const clearCountdown = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose = (fn: () => void) => { countdown = setTimeout(fn, 1500); };

const closeSuccessCreatePopup = () => { showSuccessCreatePopup.value = false; clearCountdown(); };
const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };

// Sama persis dengan pola Siswa — pakai watch flash dari Inertia
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (!flash?.success) return;
        if (flash.success === 'created') {
            showSuccessCreatePopup.value = true;
            startAutoClose(closeSuccessCreatePopup);
        } else if (flash.success === 'updated') {
            showSuccessUpdatePopup.value = true;
            startAutoClose(closeSuccessUpdatePopup);
        }
    },
    { immediate: true, deep: true }
);

onUnmounted(() => { clearCountdown(); });
</script>

<template>
    <Head title="Data Alumni" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Data Alumni</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Kelola data alumni sekolah dan informasi karier mereka</p>
                        <div class="mt-3">
                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                                <div class="flex items-center gap-2">
                                    <span v-html="GraduationIcon()" class="text-green-500"></span>
                                    <span>{{ alumni.total }} Total Alumni</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                    <span>{{ countLakiLaki }} Laki-laki</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-pink-500"></div>
                                    <span>{{ countPerempuan }} Perempuan</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                                    <span>{{ countRecentGraduates }} Lulusan Terbaru</span>
                                </div>
                                <div v-if="hasActiveFilters" class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                                    <span>{{ alumni.data.length }} Terfilter</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Link :href="route('admin.alumni.create')"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto sm:px-6 sm:py-3">
                        <span v-html="PlusIcon()" class="transition-transform group-hover:scale-110"></span>
                        <span class="sm:inline">Tambah Alumni</span>
                    </Link>
                </div>

                <!-- Search & Filter -->
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
                            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Alumni</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4">
                                        <span v-html="SearchIcon()" class="text-gray-400"></span>
                                    </div>
                                    <input v-model="searchForm.search" type="text"
                                        placeholder="Cari berdasarkan nama, NIS, pekerjaan..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-12 sm:pr-4 sm:rounded-xl" />
                                </div>
                            </div>

                            <!-- Angkatan -->
                            <div class="xl:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Angkatan</label>
                                <div class="relative">
                                    <select v-model="searchForm.angkatan"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Angkatan</option>
                                        <option v-for="a in angkatanList" :key="a.value" :value="a.value">{{ a.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pekerjaan -->
                            <div class="xl:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pekerjaan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4">
                                        <span v-html="BriefcaseIcon()" class="text-gray-400"></span>
                                    </div>
                                    <input v-model="searchForm.pekerjaan" type="text"
                                        placeholder="Filter berdasarkan pekerjaan..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-12 sm:pr-4 sm:rounded-xl" />
                                </div>
                            </div>

                            <!-- Clear -->
                            <div v-if="hasActiveFilters" class="flex items-end xl:col-span-1">
                                <button @click="clearAllFilters"
                                    class="w-full rounded-lg bg-gray-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:rounded-xl sm:py-3">
                                    Bersihkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Menampilkan {{ alumni.data.length }} dari {{ alumni.total }} alumni
                            </p>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>Halaman {{ alumni.current_page }} dari {{ alumni.last_page }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="alumni.data.length > 0" class="hidden lg:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Alumni</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">NIS</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Angkatan</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pekerjaan</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tahun Lulus</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Kontak</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Alamat Sekarang</th>
                                        <th scope="col" class="relative px-6 py-4"><span class="sr-only">Aksi</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    <tr v-for="item in alumni.data" :key="item.id"
                                        class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                                        <!-- Alumni -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                <img :src="getPhotoUrl(item.foto)" :alt="getAlumniName(item)"
                                                    class="h-12 w-12 rounded-lg border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10 flex-shrink-0" />
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ getAlumniName(item) }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ getAlumniJenisKelamin(item) }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- NIS -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ getAlumniNis(item) }}</div>
                                        </td>

                                        <!-- Angkatan -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                {{ getAlumniAngkatan(item) }}
                                            </span>
                                        </td>

                                        <!-- Pekerjaan -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span v-html="BriefcaseIcon()" class="text-gray-400 flex-shrink-0"></span>
                                                <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate" :title="item.pekerjaan">{{ item.pekerjaan }}</div>
                                            </div>
                                        </td>

                                        <!-- Tahun Lulus -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ getTahunLulus(item) }}</div>
                                        </td>

                                        <!-- Kontak -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm space-y-1">
                                                <div v-if="item.no_telepon" class="text-gray-900 dark:text-white">{{ item.no_telepon }}</div>
                                                <div v-if="item.email" class="text-gray-600 dark:text-gray-400 text-xs truncate max-w-xs">{{ item.email }}</div>
                                                <div v-if="!item.no_telepon && !item.email" class="text-gray-500 dark:text-gray-400">-</div>
                                            </div>
                                        </td>

                                        <!-- Alamat -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate" :title="item.alamat_sekarang">
                                                {{ item.alamat_sekarang || '-' }}
                                            </div>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <Link :href="route('admin.alumni.show', item.id)"
                                                    class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900"
                                                    title="Lihat Detail">
                                                    <span v-html="EyeIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                                </Link>
                                                <Link :href="route('admin.alumni.edit', item.id)"
                                                    class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 dark:focus:ring-offset-gray-900"
                                                    title="Edit Alumni">
                                                    <span v-html="EditIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                                </Link>
                                                <button @click="deleteAlumni(item)"
                                                    class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-all hover:bg-red-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 dark:focus:ring-offset-gray-900"
                                                    title="Hapus Alumni">
                                                    <span v-html="TrashIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div v-if="alumni.data.length > 0" class="lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="item in alumni.data" :key="item.id" class="p-4">
                            <div class="flex items-start gap-3">
                                <img :src="getPhotoUrl(item.foto)" :alt="getAlumniName(item)"
                                    class="h-14 w-14 rounded-lg border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10 flex-shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{ getAlumniName(item) }}</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ getAlumniAngkatan(item) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NIS: {{ getAlumniNis(item) }} · {{ getAlumniJenisKelamin(item) }}</p>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tahun Lulus</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ getTahunLulus(item) }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">No. Telepon</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ item.no_telepon || '-' }}</p>
                                </div>
                                <div class="col-span-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Pekerjaan</p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span v-html="BriefcaseIcon()" class="text-gray-400 flex-shrink-0"></span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.pekerjaan }}</p>
                                    </div>
                                </div>
                                <div v-if="item.alamat_sekarang" class="col-span-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Alamat Sekarang</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ item.alamat_sekarang }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <Link :href="route('admin.alumni.show', item.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    <span v-html="EyeIcon()"></span>Lihat
                                </Link>
                                <Link :href="route('admin.alumni.edit', item.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 py-2 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                                    <span v-html="EditIcon()"></span>Ubah
                                </Link>
                                <button @click="deleteAlumni(item)"
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
                                <span class="text-2xl sm:text-3xl">🎓</span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white sm:mt-6">Belum ada data alumni</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ hasActiveFilters ? 'Coba sesuaikan kriteria pencarian atau bersihkan filter.' : 'Mulai dengan menambahkan alumni pertama.' }}
                            </p>
                            <div class="mt-6 sm:mt-8">
                                <Link :href="route('admin.alumni.create')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:px-6 sm:py-3">
                                    <span v-html="PlusIcon()"></span>
                                    {{ hasActiveFilters ? 'Tambah Alumni Baru' : 'Tambah Alumni Pertama' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="alumni.last_page > 1"
                    class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:p-6 sm:rounded-2xl">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan
                        <span class="font-medium text-gray-900 dark:text-white">{{ ((alumni.current_page - 1) * alumni.per_page) + 1 }}</span>
                        sampai
                        <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(alumni.current_page * alumni.per_page, alumni.total) }}</span>
                        dari
                        <span class="font-medium text-gray-900 dark:text-white">{{ alumni.total }}</span>
                        hasil
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-1 sm:justify-start" aria-label="Navigasi halaman">
                        <template v-for="link in alumni.links" :key="link.label">
                            <Link v-if="link.url !== null" :href="link.url as string" v-html="link.label"
                                :class="[
                                    'inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-all min-w-[2.5rem]',
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600'
                                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-white'
                                ]" />
                            <span v-else v-html="link.label"
                                class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg min-w-[2.5rem] bg-gray-50 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600" />
                        </template>
                    </nav>
                </div>

            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            <div class="relative mx-4 w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 transition-all dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600 dark:text-red-400"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Alumni</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Apakah Anda yakin ingin menghapus data alumni
                        <span class="font-semibold">"{{ getAlumniName(selectedAlumni) }}"</span> dengan NIS
                        <span class="font-semibold">{{ getAlumniNis(selectedAlumni) }}</span>?
                    </p>
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

        <!-- ── Success Popups ─────────────────────────────────────────────── -->

        <!-- Delete -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessDeletePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessDeletePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessDeletePopup" type="button"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Alumni Berhasil Dihapus!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data alumni telah dihapus secara permanen dari sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Create -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessCreatePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessCreatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessCreatePopup" type="button"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Alumni Berhasil Ditambahkan!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data alumni baru telah berhasil ditambahkan ke dalam sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Update -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessUpdatePopup" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessUpdatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessUpdatePopup" type="button"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Alumni Berhasil Diperbarui!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Perubahan data alumni telah berhasil disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>
