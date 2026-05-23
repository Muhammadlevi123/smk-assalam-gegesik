<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const showDeleteModal        = ref(false);
const showSuccessCreatePopup = ref(false);
const showSuccessDeletePopup = ref(false);
const showSuccessUpdatePopup = ref(false);
const showDeskripsiModal     = ref(false);
const showJadwalModal        = ref(false);
const selectedOrganisasi     = ref<Organisasi | null>(null);
const selectedDeskripsi      = ref<Organisasi | null>(null);
const selectedJadwal         = ref<Organisasi | null>(null);
let countdown: number | null = null;

const SearchIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const PlusIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const TrashIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const EyeIcon         = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const BuildingIcon    = () => `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4" /></svg>`;
const UserIcon        = () => `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const ClockIcon       = () => `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" /></svg>`;
const ClockIconMd     = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" /></svg>`;
const AlignLeftIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>`;
const MessageIcon     = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>`;
const XMarkIcon       = () => `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;

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

interface Props {
    organisasi: {
        data:         Organisasi[];
        current_page: number;
        last_page:    number;
        per_page:     number;
        total:        number;
        links:        Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?:  { search?: string; jenis?: string; };
    jenisList: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const page  = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',       href: '/admin/dashboard' },
    { title: 'Data Organisasi', href: '/admin/organisasi' },
];

// ── Search & Filter ───────────────────────────────────────────────
const searchForm = useForm({
    search: props.filters?.search || '',
    jenis:  props.filters?.jenis  || '',
});
const deleteForm = useForm({});

const debounce = (func: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => { clearTimeout(timeoutId); timeoutId = setTimeout(() => func.apply(null, args), delay); };
};
const debouncedSearch = debounce(() => {
    searchForm.get('/admin/organisasi', { preserveState: true, preserveScroll: true });
}, 300);
watch([() => searchForm.search, () => searchForm.jenis], () => { debouncedSearch(); });
const clearAllFilters = () => {
    searchForm.search = ''; searchForm.jenis = '';
    searchForm.get('/admin/organisasi', { preserveState: true, preserveScroll: true });
};

// ── Modal Deskripsi ───────────────────────────────────────────────
const openDeskripsiModal  = (org: Organisasi) => { selectedDeskripsi.value = org; showDeskripsiModal.value = true; };
const closeDeskripsiModal = () => { showDeskripsiModal.value = false; selectedDeskripsi.value = null; };

// ── Modal Jadwal ──────────────────────────────────────────────────
const openJadwalModal  = (org: Organisasi) => { selectedJadwal.value = org; showJadwalModal.value = true; };
const closeJadwalModal = () => { showJadwalModal.value = false; selectedJadwal.value = null; };

// Parse jadwal: "Senin 08.00–10.00; Rabu 13.00–Selesai" → array baris
const jadwalLines = computed(() => {
    if (!selectedJadwal.value?.jadwal_latihan) return [];
    return selectedJadwal.value.jadwal_latihan.split('; ').map(part => {
        const match = part.match(/^(\w+)\s+(.+)$/);
        return match ? { hari: match[1], jam: match[2] } : { hari: part, jam: '' };
    });
});

// ── Delete ────────────────────────────────────────────────────────
const deleteOrganisasi = (org: Organisasi) => { selectedOrganisasi.value = org; showDeleteModal.value = true; };
const confirmDelete = () => {
    if (!selectedOrganisasi.value) return;
    deleteForm.delete(`/admin/organisasi/${selectedOrganisasi.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false; showSuccessDeletePopup.value = true;
            selectedOrganisasi.value = null; startAutoClose(closeSuccessDeletePopup);
        },
    });
};

const getLogoUrl       = (logo?: string) => logo ? `/storage/${logo}` : null;
const hasActiveFilters = computed(() => !!(props.filters?.search || props.filters?.jenis));

// ── Popups ────────────────────────────────────────────────────────
const clearCountdown  = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose  = (fn: () => void) => { countdown = setTimeout(fn, 1500); };
const closeSuccessCreatePopup = () => { showSuccessCreatePopup.value = false; clearCountdown(); };
const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };

watch(() => (page.props as any).flash, (flash) => {
    if (!flash?.success) return;
    if (flash.success === 'created')      { showSuccessCreatePopup.value = true; startAutoClose(closeSuccessCreatePopup); }
    else if (flash.success === 'updated') { showSuccessUpdatePopup.value = true; startAutoClose(closeSuccessUpdatePopup); }
}, { immediate: true, deep: true });

onUnmounted(() => { clearCountdown(); });
</script>

<template>
    <Head title="Data Organisasi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Data Organisasi</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Kelola data organisasi dan ekstrakurikuler sekolah</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span>{{ organisasi.total }} Total Organisasi</span></div>
                            <div v-if="hasActiveFilters" class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-orange-500"></div><span>{{ organisasi.data.length }} Terfilter</span></div>
                        </div>
                    </div>
                    <Link :href="route('admin.organisasi.create')"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg sm:w-auto sm:px-6 sm:py-3">
                        <span v-html="PlusIcon()" class="transition-transform group-hover:scale-110"></span>
                        Tambah Organisasi
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
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:gap-6">
                            <div class="sm:col-span-2 lg:col-span-7">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Organisasi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4"><span v-html="SearchIcon()" class="text-gray-400"></span></div>
                                    <input v-model="searchForm.search" type="text" placeholder="Cari nama, jenis, pembina..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 sm:py-3 sm:pl-12 sm:pr-4 sm:rounded-xl" />
                                </div>
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis</label>
                                <div class="relative">
                                    <select v-model="searchForm.jenis"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Jenis</option>
                                        <option v-for="j in jenisList" :key="j.value" :value="j.value">{{ j.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>
                            <div v-if="hasActiveFilters" class="flex items-end lg:col-span-2">
                                <button @click="clearAllFilters" class="w-full rounded-lg bg-gray-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 sm:rounded-xl sm:py-3">Bersihkan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menampilkan {{ organisasi.data.length }} dari {{ organisasi.total }} organisasi</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Halaman {{ organisasi.current_page }} dari {{ organisasi.last_page }}</span>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="organisasi.data.length > 0" class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Organisasi</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Jenis</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Pembina</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Jadwal Latihan</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Deskripsi</th>
                                    <th class="relative px-6 py-4"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="org in organisasi.data" :key="org.id" class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                                    <!-- Organisasi -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <img v-if="getLogoUrl(org.logo)" :src="getLogoUrl(org.logo)!" :alt="org.nama"
                                                class="h-12 w-12 rounded-lg border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10 flex-shrink-0" />
                                            <div v-else class="h-12 w-12 rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 flex-shrink-0 flex items-center justify-center">
                                                <span v-html="BuildingIcon()" class="text-gray-400"></span>
                                            </div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ org.nama }}</div>
                                        </div>
                                    </td>

                                    <!-- Jenis -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">{{ org.jenis }}</span>
                                    </td>

                                    <!-- Pembina -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="org.pembina" class="flex items-center gap-1.5">
                                            <span v-html="UserIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <span class="text-sm text-gray-900 dark:text-white">{{ org.pembina }}</span>
                                        </div>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">—</span>
                                    </td>

                                    <!-- Jadwal Latihan — tombol modal seperti mapel guru -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button v-if="org.jadwal_latihan" @click="openJadwalModal(org)"
                                            class="group/jadwal inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 shadow-sm transition-all hover:bg-indigo-100 hover:shadow dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/40">
                                            <span v-html="ClockIcon()" class="transition-transform group-hover/jadwal:scale-110"></span>
                                            Lihat Jadwal
                                        </button>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">—</span>
                                    </td>

                                    <!-- Deskripsi — tombol modal seperti contact message -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button v-if="org.deskripsi" @click="openDeskripsiModal(org)"
                                            class="group/desk inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40">
                                            <span v-html="MessageIcon()" class="transition-transform group-hover/desk:scale-110"></span>
                                            Lihat Deskripsi
                                        </button>
                                        <span v-else class="text-sm text-gray-400 dark:text-gray-500">—</span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.organisasi.show', org.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" title="Lihat Detail">
                                                <span v-html="EyeIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link>
                                            <Link :href="route('admin.organisasi.edit', org.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40" title="Edit">
                                                <span v-html="EditIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link>
                                            <button @click="deleteOrganisasi(org)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-all hover:bg-red-100 hover:shadow dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40" title="Hapus">
                                                <span v-html="TrashIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div v-if="organisasi.data.length > 0" class="lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="org in organisasi.data" :key="org.id" class="p-4">
                            <div class="flex items-start gap-3">
                                <img v-if="getLogoUrl(org.logo)" :src="getLogoUrl(org.logo)!" :alt="org.nama"
                                    class="h-14 w-14 rounded-lg border border-gray-200 bg-gray-50 object-cover flex-shrink-0" />
                                <div v-else class="h-14 w-14 rounded-lg border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 flex-shrink-0 flex items-center justify-center">
                                    <span v-html="BuildingIcon()" class="text-gray-400"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{ org.nama }}</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">{{ org.jenis }}</span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5">
                                        <div v-if="org.pembina" class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span v-html="UserIcon()" class="text-gray-400"></span>{{ org.pembina }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tombol jadwal & deskripsi di mobile -->
                            <div v-if="org.jadwal_latihan || org.deskripsi" class="mt-2 flex flex-wrap gap-2">
                                <button v-if="org.jadwal_latihan" @click="openJadwalModal(org)"
                                    class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400">
                                    <span v-html="ClockIcon()"></span>Jadwal
                                </button>
                                <button v-if="org.deskripsi" @click="openDeskripsiModal(org)"
                                    class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                    <span v-html="MessageIcon()"></span>Deskripsi
                                </button>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <Link :href="route('admin.organisasi.show', org.id)" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    <span v-html="EyeIcon()"></span>Lihat
                                </Link>
                                <Link :href="route('admin.organisasi.edit', org.id)" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 py-2 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                                    <span v-html="EditIcon()"></span>Ubah
                                </Link>
                                <button @click="deleteOrganisasi(org)" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 py-2 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
                                    <span v-html="TrashIcon()"></span>Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center sm:p-16">
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 sm:h-20 sm:w-20">
                                <span v-html="BuildingIcon()" class="text-gray-400"></span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white sm:mt-6">Belum ada data organisasi</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ hasActiveFilters ? 'Coba sesuaikan kriteria pencarian atau bersihkan filter.' : 'Mulai dengan menambahkan organisasi pertama.' }}</p>
                            <div class="mt-6 sm:mt-8">
                                <Link :href="route('admin.organisasi.create')" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:px-6 sm:py-3">
                                    <span v-html="PlusIcon()"></span>{{ hasActiveFilters ? 'Tambah Organisasi Baru' : 'Tambah Organisasi Pertama' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="organisasi.last_page > 1" class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:p-6 sm:rounded-2xl">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ ((organisasi.current_page - 1) * organisasi.per_page) + 1 }}</span>
                        sampai <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(organisasi.current_page * organisasi.per_page, organisasi.total) }}</span>
                        dari <span class="font-medium text-gray-900 dark:text-white">{{ organisasi.total }}</span> hasil
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-1 sm:justify-start">
                        <template v-for="link in organisasi.links" :key="link.label">
                            <Link v-if="link.url !== null" :href="link.url as string" v-html="link.label"
                                :class="['inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-all min-w-[2.5rem]',
                                    link.active ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700']" />
                            <span v-else v-html="link.label" class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg min-w-[2.5rem] bg-gray-50 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600" />
                        </template>
                    </nav>
                </div>

            </div>
        </div>

        <!-- ══ Modal Deskripsi (seperti contact message) ══ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeskripsiModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeDeskripsiModal">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 translate-y-4" appear>
                    <div class="relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="AlignLeftIcon()" class="text-gray-500"></span>
                                    Deskripsi Organisasi
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ selectedDeskripsi?.nama }}</p>
                            </div>
                            <button @click="closeDeskripsiModal" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>
                        <!-- Body -->
                        <div class="max-h-[60vh] overflow-y-auto p-6">
                            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/30">
                                <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-900 dark:text-white">{{ selectedDeskripsi?.deskripsi }}</p>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="flex justify-end border-t border-gray-100 bg-gray-50/80 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                            <button @click="closeDeskripsiModal" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600">Tutup</button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- ══ Modal Jadwal (seperti mapel guru) ══ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showJadwalModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeJadwalModal">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 translate-y-4" appear>
                    <div class="relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span v-html="ClockIconMd()" class="text-gray-500"></span>
                                    Jadwal Latihan
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ selectedJadwal?.nama }}</p>
                            </div>
                            <button @click="closeJadwalModal" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <span v-html="XMarkIcon()"></span>
                            </button>
                        </div>
                        <!-- Body -->
                        <div class="max-h-[60vh] overflow-y-auto p-6">
                            <div v-if="jadwalLines.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div v-for="line in jadwalLines" :key="line.hari" class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                                    <!-- Ikon jam -->
                                    <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                        <span v-html="ClockIcon()" class="text-indigo-600 dark:text-indigo-400" style="width:14px;height:14px;"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ line.hari }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ line.jam }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-12">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <span v-html="ClockIconMd()" class="text-gray-400"></span>
                                </div>
                                <p class="mt-3 text-sm text-gray-400 dark:text-gray-500">Jadwal belum tersedia</p>
                            </div>
                        </div>
                        <!-- Footer -->
                        <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/80 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ jadwalLines.length }} hari latihan</span>
                            <button @click="closeJadwalModal" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600">Tutup</button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600 dark:text-red-400"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Organisasi</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">Apakah Anda yakin ingin menghapus organisasi <span class="font-semibold">"{{ selectedOrganisasi?.nama }}"</span>?</p>
                </div>
                <div class="mt-6 flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Batal</button>
                    <button @click="confirmDelete" :disabled="deleteForm.processing" class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="deleteForm.processing">Menghapus...</span><span v-else>Hapus</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Popups -->
        <template v-for="(show, key) in { delete: showSuccessDeletePopup, create: showSuccessCreatePopup, update: showSuccessUpdatePopup }" :key="key">
            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="key === 'delete' ? closeSuccessDeletePopup() : key === 'create' ? closeSuccessCreatePopup() : closeSuccessUpdatePopup()"></div>
                    <div class="relative mx-4 pointer-events-auto">
                        <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                            <div class="absolute right-4 top-4">
                                <button @click="key === 'delete' ? closeSuccessDeletePopup() : key === 'create' ? closeSuccessCreatePopup() : closeSuccessUpdatePopup()" type="button" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800">
                                    <span v-html="XMarkIcon()"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                    <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ key === 'delete' ? 'Organisasi Berhasil Dihapus!' : key === 'create' ? 'Organisasi Berhasil Ditambahkan!' : 'Data Organisasi Berhasil Diperbarui!' }}
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ key === 'delete' ? 'Data organisasi telah dihapus secara permanen.' : key === 'create' ? 'Data organisasi baru telah berhasil ditambahkan.' : 'Perubahan data organisasi telah berhasil disimpan.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </template>

    </AppLayout>
</template>
