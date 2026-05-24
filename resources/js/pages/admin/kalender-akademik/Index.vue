<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const showDeleteModal        = ref(false);
const showSuccessCreatePopup = ref(false);
const showSuccessDeletePopup = ref(false);
const showSuccessUpdatePopup = ref(false);
const selectedKalender       = ref<KalenderAkademik | null>(null);
let countdown: number | null = null;

const SearchIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const PlusIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;
const TrashIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const CalendarIcon    = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" /></svg>`;
const ClockIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
const CheckIcon       = () => `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
const XIcon           = () => `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`;

interface KalenderAkademik {
    id:              number;
    judul:           string;
    tanggal_mulai:   string;
    tanggal_selesai: string;
    tahun_ajaran_id: number;
    include_weekend: boolean;
    tahun_ajaran:    { id: number; tahun: string };
    created_at:      string;
    updated_at:      string;
}

interface Props {
    kalenderAkademik: {
        data:         KalenderAkademik[];
        current_page: number;
        last_page:    number;
        per_page:     number;
        total:        number;
        links:        Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?:          string;
        tahun_ajaran_id?: string | number;
    };
    tahunAjaranList: Array<{ value: number; label: string }>;
    tahunAktifId:    number | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',         href: '/admin/dashboard' },
    { title: 'Kalender Akademik', href: '/admin/kalender-akademik' },
];

const page = usePage();

const searchForm = useForm({
    search:          props.filters?.search          || '',
    tahun_ajaran_id: props.filters?.tahun_ajaran_id || props.tahunAktifId || '',
});

const clearAllFilters = () => {
    searchForm.search          = '';
    searchForm.tahun_ajaran_id = '';
    searchForm.get('/admin/kalender-akademik', { preserveState: true, preserveScroll: true });
};

const deleteForm = useForm({});

const debounce = (func: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => { clearTimeout(timeoutId); timeoutId = setTimeout(() => func.apply(null, args), delay); };
};
const debouncedSearch = debounce(() => {
    searchForm.get('/admin/kalender-akademik', { preserveState: true, preserveScroll: true });
}, 300);
watch([() => searchForm.search, () => searchForm.tahun_ajaran_id], () => { debouncedSearch(); });

const deleteKalender = (kalender: KalenderAkademik) => {
    selectedKalender.value = kalender;
    showDeleteModal.value  = true;
};
const confirmDelete = () => {
    if (!selectedKalender.value) return;
    deleteForm.delete(`/admin/kalender-akademik/${selectedKalender.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value        = false;
            showSuccessDeletePopup.value = true;
            selectedKalender.value       = null;
            startAutoClose(closeSuccessDeletePopup);
        },
    });
};

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const formatDateShort = (date: string) =>
    new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
const formatDateRange = (start: string, end: string) => {
    const s = new Date(start), e = new Date(end);
    if (start === end) return formatDate(start);
    if (s.getMonth() === e.getMonth() && s.getFullYear() === e.getFullYear()) {
        return `${s.getDate()} - ${e.getDate()} ${e.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`;
    }
    return `${formatDate(start)} - ${formatDate(end)}`;
};
const formatDateRangeShort = (start: string, end: string) =>
    start === end ? formatDateShort(start) : `${formatDateShort(start)} - ${formatDateShort(end)}`;
const getDaysDifference = (start: string, end: string) => {
    const diff = Math.abs(new Date(end).getTime() - new Date(start).getTime());
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    return days === 0 ? 1 : days + 1;
};

const hasActiveFilters = computed(() => !!(props.filters?.search || props.filters?.tahun_ajaran_id));

const closeSuccessCreatePopup = () => { showSuccessCreatePopup.value = false; clearCountdown(); };
const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };
const clearCountdown = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose = (fn: () => void) => { countdown = setTimeout(fn, 1500); };

watch(() => (page.props as any).flash, (flash) => {
    if (!flash?.success) return;
    if (flash.success === 'created')      { showSuccessCreatePopup.value = true; startAutoClose(closeSuccessCreatePopup); }
    else if (flash.success === 'updated') { showSuccessUpdatePopup.value = true; startAutoClose(closeSuccessUpdatePopup); }
}, { immediate: true, deep: true });

onUnmounted(() => { clearCountdown(); });
</script>

<template>
    <Head title="Kalender Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Kalender Akademik</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Kelola jadwal kegiatan akademik dan event sekolah</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span>{{ kalenderAkademik.total }} Total Event</span></div>
                            <div v-if="hasActiveFilters" class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-orange-500"></div><span>{{ kalenderAkademik.data.length }} Terfilter</span></div>
                        </div>
                    </div>
                    <Link :href="route('admin.kalender-akademik.create')"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg sm:w-auto sm:px-6 sm:py-3">
                        <span v-html="PlusIcon()" class="transition-transform group-hover:scale-110"></span>
                        Tambah Event
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
                        <div class="space-y-4 sm:grid sm:grid-cols-2 sm:gap-4 sm:space-y-0 lg:grid-cols-12 xl:gap-6">
                            <div class="sm:col-span-2 lg:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Event</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4">
                                        <span v-html="SearchIcon()" class="text-gray-400"></span>
                                    </div>
                                    <input v-model="searchForm.search" type="text" placeholder="Cari berdasarkan judul event..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 sm:py-3 sm:pl-12 sm:pr-4 sm:rounded-xl" />
                                </div>
                            </div>
                            <div class="lg:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tahun Ajaran</label>
                                <div class="relative">
                                    <select v-model="searchForm.tahun_ajaran_id"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Tahun Ajaran</option>
                                        <option v-for="tahun in tahunAjaranList" :key="tahun.value" :value="tahun.value">{{ tahun.label }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
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
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menampilkan {{ kalenderAkademik.data.length }} dari {{ kalenderAkademik.total }} event</p>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Halaman {{ kalenderAkademik.current_page }} dari {{ kalenderAkademik.last_page }}</span>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="kalenderAkademik.data.length > 0" class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Event</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tanggal</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Durasi</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Sabtu & Minggu</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">Tahun Ajaran</th>
                                    <th class="relative px-6 py-4"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="kalender in kalenderAkademik.data" :key="kalender.id"
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ kalender.judul }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ formatDateRange(kalender.tanggal_mulai, kalender.tanggal_selesai) }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                            <span v-html="ClockIcon()"></span>
                                            <span>{{ getDaysDifference(kalender.tanggal_mulai, kalender.tanggal_selesai) }} hari</span>
                                        </div>
                                    </td>

                                    <!-- ✅ Kolom Sabtu & Minggu -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="kalender.include_weekend"
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            <span v-html="CheckIcon()"></span>
                                            Ditandai
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            <span v-html="XIcon()"></span>
                                            Tidak
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-200">
                                            {{ kalender.tahun_ajaran.tahun }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.kalender-akademik.edit', kalender.id)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                                title="Edit Event">
                                                <span v-html="EditIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </Link>
                                            <button @click="deleteKalender(kalender)"
                                                class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-all hover:bg-red-100 hover:shadow dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                                title="Hapus Event">
                                                <span v-html="TrashIcon()" class="transition-transform group-hover/btn:scale-110"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card -->
                    <div v-if="kalenderAkademik.data.length > 0" class="lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="kalender in kalenderAkademik.data" :key="kalender.id" class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{ kalender.judul }}</h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-200">
                                            {{ kalender.tahun_ajaran.tahun }}
                                        </span>
                                        <!-- Badge Sabtu & Minggu di mobile -->
                                        <span v-if="kalender.include_weekend"
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            <span v-html="CheckIcon()"></span>
                                            Sabtu & Minggu ditandai
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            <span v-html="XIcon()"></span>
                                            Tanpa Sabtu & Minggu
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ formatDateRangeShort(kalender.tanggal_mulai, kalender.tanggal_selesai) }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Durasi</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ getDaysDifference(kalender.tanggal_mulai, kalender.tanggal_selesai) }} hari</p>
                                </div>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <Link :href="route('admin.kalender-akademik.edit', kalender.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 py-2 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                                    <span v-html="EditIcon()"></span>Ubah
                                </Link>
                                <button @click="deleteKalender(kalender)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 py-2 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
                                    <span v-html="TrashIcon()"></span>Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center sm:p-16">
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 sm:h-20 sm:w-20">
                                <span v-html="CalendarIcon()" class="text-gray-400"></span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white sm:mt-6">Belum ada event akademik</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ hasActiveFilters ? 'Coba sesuaikan kriteria pencarian atau bersihkan filter.' : 'Mulai dengan menambahkan event akademik pertama.' }}
                            </p>
                            <div class="mt-6 sm:mt-8">
                                <Link :href="route('admin.kalender-akademik.create')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:px-6 sm:py-3">
                                    <span v-html="PlusIcon()"></span>
                                    {{ hasActiveFilters ? 'Tambah Event Baru' : 'Tambah Event Pertama' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="kalenderAkademik.last_page > 1"
                    class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:p-6 sm:rounded-2xl">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan
                        <span class="font-medium text-gray-900 dark:text-white">{{ ((kalenderAkademik.current_page - 1) * kalenderAkademik.per_page) + 1 }}</span>
                        sampai
                        <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(kalenderAkademik.current_page * kalenderAkademik.per_page, kalenderAkademik.total) }}</span>
                        dari
                        <span class="font-medium text-gray-900 dark:text-white">{{ kalenderAkademik.total }}</span>
                        hasil
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-1 sm:justify-start">
                        <template v-for="link in kalenderAkademik.links" :key="link.label">
                            <Link v-if="link.url !== null" :href="link.url as string" v-html="link.label"
                                :class="['inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg transition-all min-w-[2.5rem]',
                                    link.active ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700']" />
                            <span v-else v-html="link.label" class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg min-w-[2.5rem] bg-gray-50 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600" />
                        </template>
                    </nav>
                </div>

            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600 dark:text-red-400"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Event Akademik</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">Apakah Anda yakin ingin menghapus event <span class="font-semibold">"{{ selectedKalender?.judul }}"</span>?</p>
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
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                    <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ key === 'delete' ? 'Event Berhasil Dihapus!' : key === 'create' ? 'Event Berhasil Ditambahkan!' : 'Event Berhasil Diperbarui!' }}
                                </h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ key === 'delete' ? 'Event akademik telah dihapus dari kalender.' : key === 'create' ? 'Event akademik baru telah ditambahkan.' : 'Perubahan event telah berhasil disimpan.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </template>

    </AppLayout>
</template>
