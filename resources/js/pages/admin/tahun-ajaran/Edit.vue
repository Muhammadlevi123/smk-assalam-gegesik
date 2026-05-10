<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
</svg>`;

const SaveIcon = () => `
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
    <circle cx="9" cy="7" r="4" />
</svg>`;

interface TahunAjaran {
    id: number;
    tahun: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    is_aktif: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    tahunAjaran: TahunAjaran;
    previous_url: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Tahun Ajaran', href: '/admin/tahun-ajaran' },
    { title: 'Edit Tahun Ajaran', href: `/admin/tahun-ajaran/${props.tahunAjaran?.id || 'edit'}/edit` },
];

const form = useForm({
    tahun:           props.tahunAjaran?.tahun           || '',
    tanggal_mulai:   props.tahunAjaran?.tanggal_mulai   || '',
    tanggal_selesai: props.tahunAjaran?.tanggal_selesai || '',
    // ✅ previous_url ikut dikirim ke controller saat submit
    previous_url:    props.previous_url,
});

const currentYear = new Date().getFullYear();
const yearList    = Array.from({ length: 16 }, (_, i) => currentYear - 10 + i);
const showYearPicker = ref(false);

const getInitialTahunMulai  = (): number | null => props.tahunAjaran?.tahun ? parseInt(props.tahunAjaran.tahun.split('/')[0]) : null;
const getInitialTahunSelesai = (): number | null => props.tahunAjaran?.tahun ? parseInt(props.tahunAjaran.tahun.split('/')[1]) : null;

const tahunMulaiPicker   = ref<number | null>(getInitialTahunMulai());
const tahunSelesaiPicker = ref<number | null>(getInitialTahunSelesai());

const onSelectTahun = (year: number) => {
    tahunMulaiPicker.value   = year;
    tahunSelesaiPicker.value = year + 1;
    form.tahun               = `${year}/${year + 1}`;
    showYearPicker.value     = false;
};

const tanggalMulai   = ref<Date | null>(props.tahunAjaran?.tanggal_mulai ? new Date(props.tahunAjaran.tanggal_mulai) : null);
const tanggalSelesai = ref<Date | null>(props.tahunAjaran?.tanggal_selesai ? new Date(props.tahunAjaran.tanggal_selesai) : null);
const showCalendarMulai   = ref(false);
const showCalendarSelesai = ref(false);

const formatDisplay = (date: Date | null): string => {
    if (!date) return '';
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const toInputFormat = (date: Date): string => date.toISOString().split('T')[0];

const onSelectMulai = (day: any) => {
    tanggalMulai.value      = day.date;
    form.tanggal_mulai      = toInputFormat(day.date);
    showCalendarMulai.value = false;
    if (tanggalSelesai.value && tanggalSelesai.value <= day.date) {
        tanggalSelesai.value = null;
        form.tanggal_selesai = '';
    }
};

const onSelectSelesai = (day: any) => {
    tanggalSelesai.value      = day.date;
    form.tanggal_selesai      = toInputFormat(day.date);
    showCalendarSelesai.value = false;
};

const closeAll = () => {
    showYearPicker.value     = false;
    showCalendarMulai.value   = false;
    showCalendarSelesai.value = false;
};

const hasChanges = computed(() => {
    if (!props.tahunAjaran) return false;
    return form.tahun           !== props.tahunAjaran.tahun
        || form.tanggal_mulai   !== props.tahunAjaran.tanggal_mulai
        || form.tanggal_selesai !== props.tahunAjaran.tanggal_selesai;
});

// ✅ form.put — previous_url otomatis ikut terkirim, controller redirect ke previous_url
const submit = () => {
    if (!props.tahunAjaran?.id) return;
    form.put(`/admin/tahun-ajaran/${props.tahunAjaran.id}`);
};

const formatDateOnly = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
</script>

<template>
    <Head :title="`Edit Tahun Ajaran - ${tahunAjaran?.tahun || 'Loading...'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Tahun Ajaran</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Perbarui tahun ajaran "{{ tahunAjaran.tahun }}"</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-5 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Tahun Ajaran</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui data tahun ajaran dalam sistem</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">

                            <!-- Data Saat Ini -->
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Data Saat Ini</h4>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ tahunAjaran.tahun }}</span>
                                    <span v-if="tahunAjaran.is_aktif"
                                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Sedang Berjalan
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ formatDateOnly(tahunAjaran.tanggal_mulai) }} s/d {{ formatDateOnly(tahunAjaran.tanggal_selesai) }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Dibuat: {{ formatDate(tahunAjaran.created_at) }}</p>
                            </div>

                            <!-- Tahun Ajaran -->
                            <div class="max-w-md">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tahun Ajaran <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <button type="button"
                                        @click="showYearPicker = !showYearPicker; showCalendarMulai = false; showCalendarSelesai = false"
                                        class="flex w-full items-center gap-3 rounded-xl border-0 bg-gray-50 py-3 px-4 text-left ring-1 ring-inset ring-gray-200 transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500">
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                        <span class="flex-1 text-sm" :class="form.tahun ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400 dark:text-gray-500'">
                                            {{ form.tahun || 'Pilih tahun ajaran' }}
                                        </span>
                                        <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="showYearPicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>

                                    <div v-if="showYearPicker"
                                        class="absolute left-0 top-full z-50 mt-2 w-full rounded-2xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-900">
                                        <div class="mb-3 text-center">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pilih tahun awal — tahun akhir otomatis +1</p>
                                        </div>
                                        <div v-if="tahunMulaiPicker" class="mb-3 rounded-xl bg-blue-50 px-3 py-2 text-center dark:bg-blue-900/20">
                                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ tahunMulaiPicker }}/{{ tahunSelesaiPicker }}</span>
                                        </div>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button v-for="year in yearList" :key="year" type="button"
                                                @click="onSelectTahun(year)"
                                                :class="[
                                                    'rounded-lg px-2 py-2.5 text-sm font-medium transition-all',
                                                    tahunMulaiPicker === year
                                                        ? 'bg-blue-600 text-white shadow-sm'
                                                        : year === currentYear
                                                            ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-800'
                                                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                                                ]">
                                                {{ year }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Pilih tahun awal, tahun akhir otomatis +1</p>
                                <div v-if="form.errors.tahun" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.tahun }}</div>
                            </div>

                            <!-- Tanggal -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 max-w-2xl">

                                <!-- Tanggal Mulai -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tanggal Mulai <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <button type="button"
                                            @click="showCalendarMulai = !showCalendarMulai; showCalendarSelesai = false; showYearPicker = false"
                                            class="flex w-full items-center gap-3 rounded-xl border-0 bg-gray-50 py-3 px-4 text-left ring-1 ring-inset ring-gray-200 transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500">
                                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                            <span :class="tanggalMulai ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" class="text-sm flex-1">
                                                {{ tanggalMulai ? formatDisplay(tanggalMulai) : 'Pilih tanggal mulai' }}
                                            </span>
                                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="showCalendarMulai ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                        <div v-if="showCalendarMulai"
                                            class="absolute left-0 top-full z-50 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
                                            <DatePicker v-model="tanggalMulai" @dayclick="onSelectMulai" color="blue" is-expanded class="rounded-2xl" />
                                        </div>
                                    </div>
                                    <div v-if="form.errors.tanggal_mulai" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.tanggal_mulai }}</div>
                                </div>

                                <!-- Tanggal Selesai -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tanggal Selesai <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <button type="button" :disabled="!tanggalMulai"
                                            @click="showCalendarSelesai = !showCalendarSelesai; showCalendarMulai = false; showYearPicker = false"
                                            class="flex w-full items-center gap-3 rounded-xl border-0 bg-gray-50 py-3 px-4 text-left ring-1 ring-inset ring-gray-200 transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-800 dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500">
                                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                            <span :class="tanggalSelesai ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" class="text-sm flex-1">
                                                {{ tanggalSelesai ? formatDisplay(tanggalSelesai) : 'Pilih tanggal selesai' }}
                                            </span>
                                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="showCalendarSelesai ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                        <div v-if="showCalendarSelesai"
                                            class="absolute left-0 top-full z-50 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
                                            <DatePicker v-model="tanggalSelesai" @dayclick="onSelectSelesai" :min-date="tanggalMulai ?? undefined" color="blue" is-expanded class="rounded-2xl" />
                                        </div>
                                    </div>
                                    <p v-if="!tanggalMulai" class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Pilih tanggal mulai terlebih dahulu</p>
                                    <div v-if="form.errors.tanggal_selesai" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.tanggal_selesai }}</div>
                                </div>
                            </div>

                            <!-- Changes Indicator -->
                            <div v-if="hasChanges" class="rounded-xl bg-blue-50 p-4 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                                    <span class="text-sm text-blue-700 dark:text-blue-300">Ada perubahan yang belum disimpan</span>
                                </div>
                            </div>

                            <!-- Warning jika aktif -->
                            <div v-if="tahunAjaran.is_aktif" class="rounded-xl bg-amber-50 p-4 border border-amber-200 dark:bg-amber-900/10 dark:border-amber-800">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <span class="text-sm text-amber-700 dark:text-amber-300">
                                        Tahun ajaran ini sedang berjalan. Perubahan tanggal akan mempengaruhi status aktif secara otomatis.
                                    </span>
                                </div>
                            </div>

                        </div>

                        <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                            <button type="submit" :disabled="form.processing || !hasChanges"
                                class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Memperbarui...' : 'Perbarui Tahun Ajaran' }}
                            </button>
                            <!-- ✅ Kembali ke previous_url (bukan hardcode /admin/tahun-ajaran) -->
                            <Link :href="previous_url"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                <span v-html="ArrowLeftIcon()"></span>
                                Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div v-if="showYearPicker || showCalendarMulai || showCalendarSelesai"
            class="fixed inset-0 z-40" @click="closeAll">
        </div>
    </AppLayout>
</template>
