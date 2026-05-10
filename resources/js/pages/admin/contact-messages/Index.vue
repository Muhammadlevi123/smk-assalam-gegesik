<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const showDeleteModal = ref(false);
const showSuccessDeletePopup = ref(false);
const showErrorDeletePopup = ref(false);
const showPesanModal = ref(false);
const selectedMessage = ref<ContactMessage | null>(null);
const selectedPesanMessage = ref<ContactMessage | null>(null);
const deleteErrorMessage = ref('');
let countdown: number | null = null;

const page = usePage();

// ── Icons ──────────────────────────────────────────────────────────────────
const SearchIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const FilterIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2.586a1 1 0 0 1-.293.707l-6.414 6.414a1 1 0 0 0-.293.707V17l-4 2v-6.586a1 1 0 0 0-.293-.707L3.293 7.293A1 1 0 0 1 3 6.586V4Z" /></svg>`;
const TrashIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>`;
const EyeIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const CalendarIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>`;
const MailIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>`;
const PhoneIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25z" /></svg>`;
const MessageIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>`;
const ExclamationIcon = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`;

// ── Types ──────────────────────────────────────────────────────────────────
interface ContactMessage {
    id: number;
    nama: string;
    email: string;
    nomor_telepon?: string;
    pesan: string;
    created_at: string;
    updated_at: string;
    created_at_formatted: string;
    created_at_short: string;
    created_at_time: string;
    pesan_preview: string;
    time_ago: string;
}

interface Props {
    contactMessages: {
        data: ContactMessage[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url?: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?: string;
        date_from?: string;
        date_to?: string;
        bulan?: string;
        tahun?: string;
    };
    bulanList: Array<{ value: number; label: string }>;
    tahunList: Array<{ value: number; label: number }>;
    stats: {
        total_messages: number;
        today_messages: number;
        this_week_messages: number;
        this_month_messages: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pesan Masuk', href: '/admin/contact-messages' },
];

// ── Search & Filter ───────────────────────────────────────────────────────
const searchForm = useForm({
    search: props.filters?.search || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    bulan: props.filters?.bulan || '',
    tahun: props.filters?.tahun || '',
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
    searchForm.get('/admin/contact-messages', { preserveState: true, preserveScroll: true });
}, 300);

watch(
    [
        () => searchForm.search,
        () => searchForm.date_from,
        () => searchForm.date_to,
        () => searchForm.bulan,
        () => searchForm.tahun,
    ],
    () => { debouncedSearch(); }
);

const clearAllFilters = () => {
    searchForm.search = '';
    searchForm.date_from = '';
    searchForm.date_to = '';
    searchForm.bulan = '';
    searchForm.tahun = '';
    searchForm.get('/admin/contact-messages', { preserveState: true, preserveScroll: true });
};

// ── Pesan Modal ───────────────────────────────────────────────────────────
const openPesanModal = (message: ContactMessage) => {
    selectedPesanMessage.value = message;
    showPesanModal.value = true;
};

const closePesanModal = () => {
    showPesanModal.value = false;
    selectedPesanMessage.value = null;
};

// ── Delete ────────────────────────────────────────────────────────────────
const deleteMessage = (message: ContactMessage) => {
    selectedMessage.value = message;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!selectedMessage.value) return;
    deleteForm.delete(`/admin/contact-messages/${selectedMessage.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            showSuccessDeletePopup.value = true;
            selectedMessage.value = null;
            startAutoClose(closeSuccessDeletePopup);
        },
        onError: (errors: Record<string, string | string[]>) => {
            showDeleteModal.value = false;
            if (errors.delete_error) {
                deleteErrorMessage.value = Array.isArray(errors.delete_error)
                    ? errors.delete_error[0]
                    : errors.delete_error;
            } else {
                deleteErrorMessage.value = 'Terjadi kesalahan saat menghapus pesan.';
            }
            showErrorDeletePopup.value = true;
            startAutoClose(closeErrorDeletePopup, 3000);
            selectedMessage.value = null;
        },
        onFinish: () => { deleteForm.clearErrors(); },
    });
};

// ── Computed ──────────────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
    !!(props.filters?.search || props.filters?.date_from || props.filters?.date_to ||
        props.filters?.bulan || props.filters?.tahun)
);

// ── Popups ────────────────────────────────────────────────────────────────
const clearCountdown = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const startAutoClose = (fn: () => void, delay = 1500) => { countdown = setTimeout(fn, delay); };

const closeSuccessDeletePopup = () => { showSuccessDeletePopup.value = false; clearCountdown(); };
const closeErrorDeletePopup = () => { showErrorDeletePopup.value = false; deleteErrorMessage.value = ''; clearCountdown(); };

// Flash dari redirect (pola sama dengan Guru)
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (!flash?.success) return;
        if (flash.success === 'deleted') {
            showSuccessDeletePopup.value = true;
            startAutoClose(closeSuccessDeletePopup);
        }
    },
    { immediate: true, deep: true }
);

onUnmounted(() => { clearCountdown(); });
</script>

<template>

    <Head title="Pesan Masuk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- ══ Header ══ -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Pesan
                            Masuk</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Kelola pesan, saran, dan kritik
                            dari pengunjung website</p>
                        <div class="mt-3">
                            <div
                                class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div><span>{{ contactMessages.total
                                        }} Total Pesan</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-green-500"></div><span>{{ stats.today_messages
                                        }} Hari Ini</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-purple-500"></div><span>{{
                                        stats.this_week_messages }} Minggu Ini</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-orange-500"></div><span>{{
                                        stats.this_month_messages }} Bulan Ini</span>
                                </div>
                                <div v-if="hasActiveFilters" class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-red-500"></div><span>{{
                                        contactMessages.data.length }} Terfilter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ Search & Filter ══ -->
                <div
                    class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl">
                    <div
                        class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex items-center gap-2">
                            <span v-html="FilterIcon()" class="text-gray-500 dark:text-gray-400"></span>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pencarian & Filter</h3>
                            <div v-if="hasActiveFilters"
                                class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Aktif</div>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-12 xl:gap-6">
                            <!-- Search -->
                            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari
                                    Pesan</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none sm:pl-4">
                                        <span v-html="SearchIcon()" class="text-gray-400"></span>
                                    </div>
                                    <input v-model="searchForm.search" type="text"
                                        placeholder="Cari nama, email, nomor telepon, atau isi pesan..."
                                        class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-12 sm:pr-4 sm:rounded-xl" />
                                </div>
                            </div>
                            <!-- Dari Tanggal -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dari
                                    Tanggal</label>
                                <input v-model="searchForm.date_from" type="date"
                                    class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-4 sm:rounded-xl" />
                            </div>
                            <!-- Sampai Tanggal -->
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sampai
                                    Tanggal</label>
                                <input v-model="searchForm.date_to" type="date"
                                    class="block w-full rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-4 sm:rounded-xl" />
                            </div>
                            <!-- Bulan -->
                            <div class="xl:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bulan</label>
                                <div class="relative">
                                    <select v-model="searchForm.bulan"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Bulan</option>
                                        <option v-for="b in bulanList" :key="b.value" :value="b.value.toString()">{{
                                            b.label }}</option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>
                            <!-- Tahun -->
                            <div class="xl:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tahun</label>
                                <div class="relative">
                                    <select v-model="searchForm.tahun"
                                        class="block w-full appearance-none rounded-lg border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500 sm:py-3 sm:pl-4 sm:pr-10 sm:rounded-xl">
                                        <option value="">Semua Tahun</option>
                                        <option v-for="t in tahunList" :key="t.value" :value="t.value.toString()">{{
                                            t.label }}</option>
                                    </select>
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none sm:pr-4">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                </div>
                            </div>
                            <!-- Clear Filter -->
                            <div v-if="hasActiveFilters" class="flex items-end xl:col-span-2">
                                <button @click="clearAllFilters"
                                    class="w-full rounded-lg bg-gray-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:rounded-xl sm:py-3">
                                    Bersihkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ Table ══ -->
                <div
                    class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl overflow-hidden">
                    <div
                        class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Menampilkan {{
                                contactMessages.data.length }} dari {{ contactMessages.total }} pesan</p>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400"><span>Halaman
                                    {{ contactMessages.current_page }} dari {{ contactMessages.last_page }}</span></div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div v-if="contactMessages.data.length > 0" class="hidden lg:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50/80 dark:bg-gray-800/50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            Pengirim</th>
                                        <th scope="col"
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            Kontak</th>
                                        <th scope="col"
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            Pesan</th>
                                        <th scope="col"
                                            class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                            Tanggal</th>
                                        <th scope="col" class="relative px-6 py-4"><span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    <tr v-for="item in contactMessages.data" :key="item.id"
                                        class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <!-- Pengirim -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ item.nama
                                                }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{
                                                item.time_ago }}</div>
                                        </td>
                                        <!-- Kontak -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span v-html="MailIcon()"
                                                        class="text-gray-400 flex-shrink-0"></span>
                                                    <span class="text-sm text-gray-900 dark:text-white">{{ item.email
                                                        }}</span>
                                                </div>
                                                <div v-if="item.nomor_telepon" class="flex items-center gap-2">
                                                    <span v-html="PhoneIcon()"
                                                        class="text-gray-400 flex-shrink-0"></span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{
                                                        item.nomor_telepon }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Pesan -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button @click="openPesanModal(item)"
                                                class="group/pesan inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 shadow-sm transition-all hover:bg-blue-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 dark:focus:ring-offset-gray-900">
                                                <span v-html="MessageIcon()"
                                                    class="transition-transform group-hover/pesan:scale-110"></span>
                                                Lihat Pesan
                                            </button>
                                        </td>
                                        <!-- Tanggal -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span v-html="CalendarIcon()" class="text-gray-400"></span>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        item.created_at_short }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{
                                                        item.created_at_time }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Aksi -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <Link :href="route('admin.contact-messages.show', item.id)"
                                                    class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900"
                                                    title="Lihat Detail">
                                                    <span v-html="EyeIcon()"
                                                        class="transition-transform group-hover/btn:scale-110"></span>
                                                </Link>
                                                <!-- <button @click="deleteMessage(item)"
                                                    class="group/btn inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-all hover:bg-red-100 hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 dark:focus:ring-offset-gray-900"
                                                    title="Hapus Pesan">
                                                    <span v-html="TrashIcon()"
                                                        class="transition-transform group-hover/btn:scale-110"></span>
                                                </button> -->
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div v-if="contactMessages.data.length > 0"
                        class="lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="item in contactMessages.data" :key="item.id" class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">{{
                                        item.nama }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ item.time_ago }}</p>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-2">
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ item.email }}
                                    </p>
                                </div>
                                <div v-if="item.nomor_telepon"
                                    class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Telepon</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{
                                        item.nomor_telepon }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pesan</p>
                                    <button @click="openPesanModal(item)"
                                        class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                        <span v-html="MessageIcon()"></span>Lihat Pesan
                                    </button>
                                </div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{
                                        item.created_at_formatted }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <Link :href="route('admin.contact-messages.show', item.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    <span v-html="EyeIcon()"></span>Lihat
                                </Link>
                                <!-- <button @click="deleteMessage(item)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 py-2 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors">
                                    <span v-html="TrashIcon()"></span>Hapus
                                </button> -->
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center sm:p-16">
                        <div class="mx-auto max-w-sm">
                            <div
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 sm:h-20 sm:w-20">
                                <span class="text-2xl sm:text-3xl">📮</span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white sm:mt-6">Belum ada pesan
                                masuk</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ hasActiveFilters ? 'Coba sesuaikan kriteria pencarian atau bersihkan filter.' :
                                'Pesan dari pengunjung akan muncul di sini.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ══ Pagination ══ -->
                <div v-if="contactMessages.last_page > 1"
                    class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between sm:p-6 sm:rounded-2xl">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Menampilkan
                        <span class="font-medium text-gray-900 dark:text-white">{{ ((contactMessages.current_page - 1) *
                            contactMessages.per_page) + 1 }}</span>
                        sampai
                        <span class="font-medium text-gray-900 dark:text-white">{{ Math.min(contactMessages.current_page
                            * contactMessages.per_page, contactMessages.total) }}</span>
                        dari
                        <span class="font-medium text-gray-900 dark:text-white">{{ contactMessages.total }}</span>
                        hasil
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-1 sm:justify-start"
                        aria-label="Navigasi halaman">
                        <template v-for="link in contactMessages.links" :key="link.label">
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

        <!-- ══ Modal Pesan Lengkap ══ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="showPesanModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="closePesanModal">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

                <Transition enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4" appear>
                    <div
                        class="relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">

                        <!-- Header -->
                        <div
                            class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Isi Pesan</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ selectedPesanMessage?.nama
                                    }}</p>
                            </div>
                            <button @click="closePesanModal"
                                class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="max-h-[60vh] overflow-y-auto p-6">
                            <div
                                class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/30">
                                <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-900 dark:text-white">{{
                                    selectedPesanMessage?.pesan }}</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex justify-end border-t border-gray-100 bg-gray-50/80 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                            <button @click="closePesanModal"
                                class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
                                Tutup
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- ══ Delete Modal ══ -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
            @click.self="showDeleteModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            <div
                class="relative mx-4 w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 transition-all dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                        <span v-html="TrashIcon()" class="text-red-600 dark:text-red-400"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Pesan</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Apakah Anda yakin ingin menghapus pesan dari
                        <span class="font-semibold">"{{ selectedMessage?.nama }}"</span>?
                    </p>
                    <div
                        class="mt-2 rounded-lg border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20">
                        <p class="text-xs text-yellow-800 dark:text-yellow-300">
                            <strong>Peringatan:</strong> Data pesan akan dihapus permanen dari sistem.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button @click="showDeleteModal = false"
                        class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                        Batal
                    </button>
                    <button @click="confirmDelete" :disabled="deleteForm.processing"
                        class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                        <span v-if="deleteForm.processing">Menghapus...</span>
                        <span v-else>Hapus</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ Success Delete Popup ══ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessDeletePopup"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm transition-opacity"
                    @click="closeSuccessDeletePopup">
                </div>
                <div class="relative mx-4 transform transition-all duration-300 pointer-events-auto">
                    <div
                        class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                        <div class="absolute right-4 top-4">
                            <button @click="closeSuccessDeletePopup" type="button"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pesan Berhasil Dihapus!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Data pesan telah dihapus secara
                                permanen
                                dari sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══ Error Delete Popup ══ -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showErrorDeletePopup"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm transition-opacity"
                    @click="closeErrorDeletePopup"></div>
                <div class="relative mx-4 transform transition-all duration-300 pointer-events-auto">
                    <div
                        class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                        <div class="absolute right-4 top-4">
                            <button @click="closeErrorDeletePopup" type="button"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-center">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                <span v-html="ExclamationIcon()" class="text-red-600 dark:text-red-400"></span>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gagal Menghapus Pesan!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ deleteErrorMessage || 'Terjadi kesalahan saat menghapus pesan.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>
