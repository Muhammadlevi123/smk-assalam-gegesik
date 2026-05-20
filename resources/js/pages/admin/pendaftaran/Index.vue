<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const showDeleteModal        = ref(false);
const showExportModal        = ref(false);
const exportType             = ref<'excel' | 'pdf'>('excel');
const exportForm             = ref({ bulan: '', tahun_daftar: '', jurusan: '', tahun_lulus: '' });
const showSuccessDeletePopup = ref(false);
const showSuccessUpdatePopup = ref(false);
const selectedItem           = ref<Pendaftaran | null>(null);
let countdown: number | null = null;

const SearchIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const EyeIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const TrashIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const ExcelIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>`;
const PdfIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;

interface Pendaftaran {
    id: number; nama_lengkap: string; jenis_kelamin: string;
    tempat_lahir: string; tanggal_lahir: string; nisn: string;
    nik: string; agama: string; anak_ke: number; no_kartu_keluarga: string;
    no_akte?: string; penerima_bantuan: string | string[]; nomor_kip?: string;
    no_hp: string; asal_sekolah: string; tahun_lulus: string; jurusan: string;
    nama_ayah: string; nik_ayah: string; pendidikan_ayah: string;
    tempat_lahir_ayah: string; tanggal_lahir_ayah?: string;
    pekerjaan_ayah: string; no_hp_ayah: string; nama_ibu: string;
    nik_ibu: string; pendidikan_ibu: string; tempat_lahir_ibu: string;
    tanggal_lahir_ibu?: string; pekerjaan_ibu: string; no_hp_ibu: string;
    jalan: string; dusun_blok: string; rt_rw: string; desa: string; kecamatan: string;
    created_at: string;
}

interface Props {
    pendaftaran: {
        data: Pendaftaran[]; current_page: number; last_page: number;
        per_page: number; total: number;
        links: Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?: string; jenis_kelamin?: string; jurusan?: string;
        tahun_lulus?: string; penerima_bantuan?: string;
        bulan?: string; tahun_daftar?: string;
    };
    tahunLulusList: string[];
    tahunDaftarList?: number[];
    bulanDaftarMap?: Record<number, number[]>;
    totalPendaftar: number;
}

const props = defineProps<Props>();
const page  = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Pendaftaran', href: '/admin/pendaftaran' },
];

const searchForm = useForm({
    search:           props.filters?.search           || '',
    jenis_kelamin:    props.filters?.jenis_kelamin    || '',
    jurusan:          props.filters?.jurusan          || '',
    tahun_lulus:      props.filters?.tahun_lulus      || '',
    penerima_bantuan: props.filters?.penerima_bantuan || '',
});

const deleteForm = useForm({});

const debounce = (func: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => { clearTimeout(timeoutId); timeoutId = setTimeout(() => func.apply(null, args), delay); };
};

const debouncedSearch = debounce(() => {
    searchForm.get('/admin/pendaftaran', { preserveState: true, preserveScroll: true });
}, 300);

watch([
    () => searchForm.search,
    () => searchForm.jenis_kelamin,
    () => searchForm.jurusan,
    () => searchForm.tahun_lulus,
    () => searchForm.penerima_bantuan,
], () => { debouncedSearch(); });

const clearAllFilters = () => {
    searchForm.search           = '';
    searchForm.jenis_kelamin    = '';
    searchForm.jurusan          = '';
    searchForm.tahun_lulus      = '';
    searchForm.penerima_bantuan = '';
    searchForm.get('/admin/pendaftaran', { preserveState: true, preserveScroll: true });
};

const hasActiveFilters = computed(() =>
    !!(props.filters?.search || props.filters?.jenis_kelamin || props.filters?.jurusan ||
       props.filters?.tahun_lulus || props.filters?.penerima_bantuan)
);

const openDelete = (item: Pendaftaran) => { selectedItem.value = item; showDeleteModal.value = true; };

const confirmDelete = () => {
    if (!selectedItem.value) return;
    deleteForm.delete(`/admin/pendaftaran/${selectedItem.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            showSuccessDeletePopup.value = true;
            selectedItem.value = null;
            startAutoClose(closeSuccessDeletePopup);
        },
    });
};

const clearCountdown          = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose          = (fn: () => void) => { countdown = setTimeout(fn, 1500); };
const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };

watch(
    () => (page.props as any).flash,
    (flash) => {
        if (!flash?.success) return;
        if (flash.success === 'updated') { showSuccessUpdatePopup.value = true; startAutoClose(closeSuccessUpdatePopup); }
    },
    { immediate: true, deep: true }
);

onUnmounted(() => clearCountdown());

const countLakiLaki  = computed(() => props.pendaftaran.data.filter(p => p.jenis_kelamin === 'Laki-laki').length);
const countPerempuan = computed(() => props.pendaftaran.data.filter(p => p.jenis_kelamin === 'Perempuan').length);
const countTKRO      = computed(() => props.pendaftaran.data.filter(p => p.jurusan === 'TKRO').length);
const countTJKT      = computed(() => props.pendaftaran.data.filter(p => p.jurusan === 'TJKT').length);

const formatDate = (str?: string) => {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Parse penerima_bantuan array
const formatBantuan = (val: string | string[]): string => {
    if (Array.isArray(val)) return val.filter(v => v !== 'Tidak Ada').join(', ') || '-';
    try { const arr = JSON.parse(val); return Array.isArray(arr) ? arr.filter((v: string) => v !== 'Tidak Ada').join(', ') || '-' : val; }
    catch { return val === 'Tidak Ada' ? '-' : val; }
};

const hasBantuan = (val: string | string[]): boolean => {
    if (Array.isArray(val)) return val.some(v => v !== 'Tidak Ada');
    try { const arr = JSON.parse(val); return Array.isArray(arr) ? arr.some((v: string) => v !== 'Tidak Ada') : val !== 'Tidak Ada'; }
    catch { return val !== 'Tidak Ada'; }
};

// Daftar bulan dan tahun untuk filter export
const bulanList = [
    { value: '1', label: 'Januari' }, { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' }, { value: '4', label: 'April' },
    { value: '5', label: 'Mei' }, { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' }, { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' }, { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' }, { value: '12', label: 'Desember' },
];

// Tahun dan bulan dari props (data real di DB)
const availableBulan = computed(() => {
    if (!exportForm.value.tahun_daftar || !props.bulanDaftarMap) return [];
    const tahun = parseInt(exportForm.value.tahun_daftar);
    return props.bulanDaftarMap[tahun] || [];
});

const tahunDaftarList = computed(() => props.tahunDaftarList || []);

const openExport = (type: 'excel' | 'pdf') => {
    exportType.value = type;
    // Isi dari filter aktif
    exportForm.value = {
        bulan:        '',
        tahun_daftar: '',
        jurusan:      '',
        tahun_lulus:  '',
    };
    showExportModal.value = true;
};

const buildExportUrlModal = () => {
    const base   = `/admin/pendaftaran-export/${exportType.value}`;
    const params = new URLSearchParams();
    if (exportForm.value.bulan)        params.set('bulan', exportForm.value.bulan);
    if (exportForm.value.tahun_daftar) params.set('tahun_daftar', exportForm.value.tahun_daftar);
    if (exportForm.value.jurusan)      params.set('jurusan', exportForm.value.jurusan);
    if (exportForm.value.tahun_lulus)  params.set('tahun_lulus', exportForm.value.tahun_lulus);
    const q = params.toString();
    return q ? `${base}?${q}` : base;
};

const doExport = () => {
    window.location.href = buildExportUrlModal();
    showExportModal.value = false;
};

const buildExportUrl = (type: 'excel' | 'pdf') => {
    const base   = `/admin/pendaftaran-export/${type}`;
    const params = new URLSearchParams();
    if (props.filters?.tahun_lulus)  params.set('tahun_lulus', props.filters.tahun_lulus);
    if (props.filters?.jurusan)      params.set('jurusan', props.filters.jurusan);
    if (props.filters?.bulan)        params.set('bulan', props.filters.bulan);
    if (props.filters?.tahun_daftar) params.set('tahun_daftar', props.filters.tahun_daftar);
    const q = params.toString();
    return q ? `${base}?${q}` : base;
};
</script>

<template>
    <Head title="Data Pendaftaran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Data Pendaftaran</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Kelola data pendaftar siswa baru SMK Assalam Gegesik</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-green-500"></div><span>{{ totalPendaftar }} Total</span></div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span>{{ countLakiLaki }} L</span></div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-pink-500"></div><span>{{ countPerempuan }} P</span></div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-indigo-500"></div><span>{{ countTKRO }} TKRO</span></div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-purple-500"></div><span>{{ countTJKT }} TJKT</span></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openExport('excel')" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700">
                            <span v-html="ExcelIcon()"></span><span class="hidden sm:inline">Export Excel</span>
                        </button>
                        <button @click="openExport('pdf')" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700">
                            <span v-html="PdfIcon()"></span><span class="hidden sm:inline">Export PDF</span>
                        </button>
                    </div>
                </div>

                <!-- Filter -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2">
                            <span v-html="FilterIcon()" class="text-gray-500"></span>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pencarian & Filter</h3>
                            <div v-if="hasActiveFilters" class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">Aktif</div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-12">

                            <!-- Search -->
                            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Pendaftar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none"><span v-html="SearchIcon()" class="text-gray-400"></span></div>
                                    <input v-model="searchForm.search" type="text" placeholder="Nama, NISN, NIK, No. HP..."
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-12 pr-4 text-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700" />
                                </div>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Kelamin</label>
                                <div class="relative">
                                    <select v-model="searchForm.jenis_kelamin" class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                                        <option value="">Semua</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>

                            <!-- Jurusan -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jurusan</label>
                                <div class="relative">
                                    <select v-model="searchForm.jurusan" class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                                        <option value="">Semua Jurusan</option>
                                        <option value="TKRO">TKRO</option>
                                        <option value="TJKT">TJKT</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>

                            <!-- Tahun Lulus -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tahun Lulus</label>
                                <div class="relative">
                                    <select v-model="searchForm.tahun_lulus" class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                                        <option value="">Semua Tahun</option>
                                        <option v-for="t in tahunLulusList" :key="t" :value="t">{{ t }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>

                            <!-- Penerima Bantuan -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Penerima Bantuan</label>
                                <div class="relative">
                                    <select v-model="searchForm.penerima_bantuan" class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                                        <option value="">Semua</option>
                                        <option value="KIP">KIP</option>
                                        <option value="KPS/KKS/PKH">KPS/KKS/PKH</option>
                                        <option value="SKTM">SKTM</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>

                            <!-- Clear -->
                            <div v-if="hasActiveFilters" class="flex items-end xl:col-span-2">
                                <button @click="clearAllFilters" class="w-full rounded-xl bg-gray-600 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-700">Bersihkan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menampilkan {{ pendaftaran.data.length }} dari {{ pendaftaran.total }} pendaftar</p>
                            <div class="text-xs text-gray-500">Halaman {{ pendaftaran.current_page }} dari {{ pendaftaran.last_page }}</div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="pendaftaran.data.length > 0" class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">JK</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NISN</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asal Sekolah</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thn Lulus</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jurusan</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bantuan</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. HP</th>
                                    <th class="px-4 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Daftar</th>
                                    <th class="relative px-4 py-4"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="(item, idx) in pendaftaran.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-4 text-sm text-gray-400">{{ (pendaftaran.current_page - 1) * pendaftaran.per_page + idx + 1 }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-400">{{ item.tempat_lahir }}, {{ formatDate(item.tanggal_lahir) }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span :class="item.jenis_kelamin === 'Laki-laki' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                            {{ item.jenis_kelamin === 'Laki-laki' ? 'L' : 'P' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-mono text-gray-700 dark:text-gray-300">{{ item.nisn }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-[160px] truncate">{{ item.asal_sekolah }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700 dark:text-gray-300">{{ item.tahun_lulus }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span :class="item.jurusan === 'TKRO' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                            {{ item.jurusan }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span :class="hasBantuan(item.penerima_bantuan) ? 'bg-amber-100 text-amber-800' : ' text-gray-600'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                            {{ formatBantuan(item.penerima_bantuan) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ item.no_hp }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-400">{{ formatDate(item.created_at) }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Link :href="route('admin.pendaftaran.show', item.id)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all"
                                                title="Detail">
                                                <span v-html="EyeIcon()"></span>
                                            </Link>
                                            <Link :href="route('admin.pendaftaran.edit', item.id)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 transition-all"
                                                title="Edit">
                                                <span v-html="EditIcon()"></span>
                                            </Link>
                                            <button @click="openDelete(item)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 transition-all"
                                                title="Hapus">
                                                <span v-html="TrashIcon()"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="pendaftaran.data.length === 0" class="p-16 text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <span class="text-3xl">📋</span>
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">Belum ada data pendaftaran</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ hasActiveFilters ? 'Coba sesuaikan filter.' : 'Data pendaftar akan muncul di sini.' }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pendaftaran.last_page > 1" class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ ((pendaftaran.current_page - 1) * pendaftaran.per_page) + 1 }}</span>
                        sampai <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(pendaftaran.current_page * pendaftaran.per_page, pendaftaran.total) }}</span>
                        dari <span class="font-medium text-gray-900 dark:text-white">{{ pendaftaran.total }}</span>
                    </div>
                    <nav class="flex flex-wrap items-center gap-1">
                        <template v-for="link in pendaftaran.links" :key="link.label">
                            <Link v-if="link.url !== null" :href="link.url as string" v-html="link.label"
                                :class="['inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-all min-w-[2.5rem]',
                                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600']" />
                            <span v-else v-html="link.label" class="inline-flex items-center justify-center px-3 py-2 text-sm rounded-lg min-w-[2.5rem] bg-gray-50 text-gray-400 cursor-not-allowed" />
                        </template>
                    </nav>
                </div>

            </div>
        </div>

        <!-- Export Modal -->
        <div v-if="showExportModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showExportModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Export {{ exportType === 'excel' ? 'Excel (CSV)' : 'PDF' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Pilih filter data yang ingin diekspor</p>
                </div>

                <div class="space-y-4">
                    <!-- Tahun Daftar — hanya tahun yang ada datanya -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tahun Pendaftaran</label>
                        <select v-model="exportForm.tahun_daftar" @change="exportForm.bulan = ''"
                            class="block w-full rounded-xl border-0 bg-gray-50 py-2.5 px-4 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                            <option value="">Semua Tahun</option>
                            <option v-for="t in tahunDaftarList" :key="t" :value="String(t)">{{ t }}</option>
                        </select>
                        <p v-if="tahunDaftarList.length === 0" class="mt-1 text-xs text-gray-400">Belum ada data pendaftaran</p>
                    </div>

                    <!-- Bulan — hanya bulan yang ada datanya di tahun dipilih -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bulan Pendaftaran</label>
                        <select v-model="exportForm.bulan" :disabled="!exportForm.tahun_daftar || availableBulan.length === 0"
                            class="block w-full rounded-xl border-0 bg-gray-50 py-2.5 px-4 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                            <option value="">Semua Bulan</option>
                            <option v-for="b in availableBulan" :key="b" :value="String(b)">
                                {{ bulanList.find(bl => bl.value === String(b))?.label || b }}
                            </option>
                        </select>
                        <p v-if="!exportForm.tahun_daftar" class="mt-1 text-xs text-gray-400">Pilih tahun dulu</p>
                        <p v-else-if="availableBulan.length > 0" class="mt-1 text-xs text-gray-400">{{ availableBulan.length }} bulan tersedia</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button @click="showExportModal = false" class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Batal</button>
                    <button @click="doExport"
                        :class="exportType === 'excel' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'"
                        class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-colors">
                        <span v-if="exportType === 'excel'">Download Excel</span>
                        <span v-else>Download PDF</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Data Pendaftaran</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">Hapus data <span class="font-semibold">"{{ selectedItem?.nama_lengkap }}"</span>?</p>
                <div class="mt-6 flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Batal</button>
                    <button @click="confirmDelete" :disabled="deleteForm.processing" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                        {{ deleteForm.processing ? 'Menghapus...' : 'Hapus' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Delete Popup -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessDeletePopup" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessDeletePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 max-w-sm">
                        <button @click="closeSuccessDeletePopup" class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Berhasil Dihapus!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data pendaftaran telah dihapus dari sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Success Update Popup -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessUpdatePopup" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessUpdatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 max-w-sm">
                        <button @click="closeSuccessUpdatePopup" class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Berhasil Diperbarui!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Perubahan data pendaftaran telah berhasil disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>
