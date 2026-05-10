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
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
</svg>`;

interface TahunAjaranOption {
    value: number;
    label: string;
}

interface KalenderAkademik {
    id: number;
    judul: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    tahun_ajaran_id: number;
    tahun_ajaran?: { id: number; tahun: string };
    created_at: string;
    updated_at: string;
}

interface Props {
    kalenderAkademik: KalenderAkademik;
    tahunAjaranList: TahunAjaranOption[];
    // ✅ Tambah previous_url
    previous_url: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Kalender Akademik', href: '/admin/kalender-akademik' },
    { title: 'Edit Agenda', href: `/admin/kalender-akademik/${props.kalenderAkademik.id}/edit` },
];

const form = useForm({
    judul:           props.kalenderAkademik.judul,
    tahun_ajaran_id: Number(props.kalenderAkademik.tahun_ajaran_id),
    tanggal_mulai:   props.kalenderAkademik.tanggal_mulai,
    tanggal_selesai: props.kalenderAkademik.tanggal_selesai,
    // ✅ previous_url ikut dikirim ke controller saat submit
    previous_url:    props.previous_url,
});

const tanggalMulai = ref<Date | null>(
    props.kalenderAkademik.tanggal_mulai ? new Date(props.kalenderAkademik.tanggal_mulai) : null
);
const tanggalSelesai = ref<Date | null>(
    props.kalenderAkademik.tanggal_selesai ? new Date(props.kalenderAkademik.tanggal_selesai) : null
);

const showCalendarMulai   = ref(false);
const showCalendarSelesai = ref(false);

const formatDisplay = (date: Date | null): string => {
    if (!date) return '';
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const toInputFormat = (date: Date): string => {
    return date.toISOString().split('T')[0];
};

const onSelectMulai = (day: any) => {
    tanggalMulai.value      = day.date;
    form.tanggal_mulai      = toInputFormat(day.date);
    showCalendarMulai.value = false;
    if (tanggalSelesai.value && tanggalSelesai.value < day.date) {
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
    showCalendarMulai.value   = false;
    showCalendarSelesai.value = false;
};

const hasChanges = computed(() => {
    return form.judul           !== props.kalenderAkademik.judul
        || form.tahun_ajaran_id !== Number(props.kalenderAkademik.tahun_ajaran_id)
        || form.tanggal_mulai   !== props.kalenderAkademik.tanggal_mulai
        || form.tanggal_selesai !== props.kalenderAkademik.tanggal_selesai;
});

const submit = () => {
    form.put(`/admin/kalender-akademik/${props.kalenderAkademik.id}`);
};

const formatDateOnly = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Edit Agenda - ${kalenderAkademik.judul}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Agenda Kalender</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Perbarui agenda "{{ kalenderAkademik.judul }}"</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-5 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Agenda</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui detail agenda kalender akademik</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">

                            <!-- Data Saat Ini -->
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Data Saat Ini</h4>
                                <p class="text-base font-semibold text-gray-900 dark:text-white">{{ kalenderAkademik.judul }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ formatDateOnly(kalenderAkademik.tanggal_mulai) }} s/d {{ formatDateOnly(kalenderAkademik.tanggal_selesai) }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Tahun Ajaran: {{ kalenderAkademik.tahun_ajaran?.tahun ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    Dibuat: {{ formatDate(kalenderAkademik.created_at) }}
                                </p>
                            </div>

                            <!-- Judul -->
                            <div class="max-w-2xl">
                                <label for="judul" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Judul Agenda <span class="text-red-500">*</span>
                                </label>
                                <input id="judul" v-model="form.judul" type="text"
                                    placeholder="Contoh: Ujian Tengah Semester, Libur Nasional, dll"
                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 dark:focus:ring-blue-500" />
                                <div v-if="form.errors.judul" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.judul }}</div>
                            </div>

                            <!-- Tahun Ajaran -->
                            <div class="max-w-md">
                                <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tahun Ajaran <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="tahun_ajaran" v-model="form.tahun_ajaran_id"
                                        class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700 dark:focus:ring-blue-500">
                                        <option value="" disabled>Pilih tahun ajaran</option>
                                        <option v-for="tahun in tahunAjaranList" :key="tahun.value" :value="Number(tahun.value)">
                                            {{ tahun.label }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="form.errors.tahun_ajaran_id" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.tahun_ajaran_id }}</div>
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
                                            @click="showCalendarMulai = !showCalendarMulai; showCalendarSelesai = false"
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
                                            @click="showCalendarSelesai = !showCalendarSelesai; showCalendarMulai = false"
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

                        </div>

                        <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                            <button type="submit" :disabled="form.processing || !hasChanges"
                                class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Memperbarui...' : 'Perbarui Agenda' }}
                            </button>
                            <!-- ✅ Diubah dari href hardcode ke :href="previous_url" -->
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

        <div v-if="showCalendarMulai || showCalendarSelesai" class="fixed inset-0 z-40" @click="closeAll"></div>
    </AppLayout>
</template>
