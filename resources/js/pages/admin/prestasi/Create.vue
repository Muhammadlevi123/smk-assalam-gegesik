<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l7-3 7 3z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;
const SearchIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;
const TrophyIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3-3h.75m-12.75 3a3 3 0 0 0-3-3h-.75m15 0v-6.75a3.75 3.75 0 0 0-3.75-3.75h-9a3.75 3.75 0 0 0-3.75 3.75v6.75m12 0v2.25a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-2.25" /></svg>`;
const CalendarIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>`;

interface Siswa {
    id:                number;
    nama:              string;
    nis:               string;
    angkatan:          number;
    foto?:             string;
    existing_prestasi: number[];
}
interface AngkatanItem  { value: number; label: number; }
interface TingkatOption { value: string; label: string; }
interface JuaraOption   { value: string; label: string; }
interface Props {
    siswa:          Siswa[];
    angkatanList:   AngkatanItem[];
    tingkatOptions: TingkatOption[];
    juaraOptions:   JuaraOption[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',       href: '/admin/dashboard' },
    { title: 'Data Prestasi',   href: '/admin/prestasi' },
    { title: 'Tambah Prestasi', href: '/admin/prestasi/create' },
];

const form = useForm({
    nama_lomba:     '',
    tingkat:        '',
    juara:          '',
    penyelenggara:  '',
    tanggal:        '',
    deskripsi:      '',
    foto:           null as File | null,
    siswa_prestasi: [] as number[],
});

const localErrors = ref<Record<string, string>>({});
const setError    = (k: string, m: string) => { localErrors.value[k] = m; };
const clearErrors = () => { localErrors.value = {}; };

// Foto
const imagePreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const onFotoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { setError('foto', 'Ukuran file maksimal 2MB.'); return; }
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { setError('foto', 'Format file tidak didukung. Gunakan JPG atau PNG.'); return; }
    delete localErrors.value['foto'];
    form.foto = file;
    const reader = new FileReader();
    reader.onload = e => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
};

const removeFoto = () => {
    form.foto = null;
    imagePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// ── Tanggal ── FIX: pakai komponen lokal bukan ISO ───────────────
const tanggalValue = ref<Date | null>(null);
const showCalendar = ref(false);

const formatDisplay = (date: Date | null): string => {
    if (!date) return '';
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

// Ikuti cara KalenderAkademik/Edit.vue yang sudah terbukti benar
const toInputFormat = (date: Date): string => date.toISOString().split('T')[0];

const onSelectTanggal = (day: any) => {
    tanggalValue.value = day.date;
    form.tanggal       = toInputFormat(day.date);
    showCalendar.value = false;
    delete localErrors.value['tanggal'];
};

const closeCalendar = () => { showCalendar.value = false; };

// Siswa
const selectedAngkatan = ref<number | ''>('');
const searchSiswa      = ref('');
const selectedSiswaIds = ref<number[]>([]);

const onAngkatanChange = () => { searchSiswa.value = ''; };

const getSiswaByAngkatan = computed(() => {
    if (!selectedAngkatan.value) return [];
    return props.siswa.filter(s => s.angkatan === selectedAngkatan.value);
});

const filteredSiswa = computed(() => {
    const search = searchSiswa.value.toLowerCase();
    if (!search) return getSiswaByAngkatan.value;
    return getSiswaByAngkatan.value.filter(s =>
        s.nama.toLowerCase().includes(search) || s.nis.toLowerCase().includes(search)
    );
});

const toggleSiswa = (siswaId: number) => {
    const idx = selectedSiswaIds.value.indexOf(siswaId);
    if (idx === -1) selectedSiswaIds.value.push(siswaId);
    else selectedSiswaIds.value.splice(idx, 1);
    delete localErrors.value['siswa_prestasi'];
};

const isSiswaSelected = (id: number) => selectedSiswaIds.value.includes(id);

const selectAllVisible = () => {
    filteredSiswa.value.forEach(s => {
        if (!selectedSiswaIds.value.includes(s.id)) selectedSiswaIds.value.push(s.id);
    });
};

const clearAllVisible = () => {
    const visibleIds = filteredSiswa.value.map(s => s.id);
    selectedSiswaIds.value = selectedSiswaIds.value.filter(id => !visibleIds.includes(id));
};

const clearAllSelected = () => { selectedSiswaIds.value = []; };

const selectedSiswaNames = computed(() => {
    const selected = props.siswa.filter(s => selectedSiswaIds.value.includes(s.id));
    if (selected.length === 0) return '';
    if (selected.length <= 3) return selected.map(s => s.nama).join(', ');
    return `${selected.slice(0, 2).map(s => s.nama).join(', ')}, +${selected.length - 2} lainnya`;
});

const allFilteredSelected = computed(() =>
    filteredSiswa.value.length > 0 &&
    filteredSiswa.value.every(s => selectedSiswaIds.value.includes(s.id))
);

// Submit
const handleSubmit = () => {
    clearErrors();
    let valid = true;
    if (!form.nama_lomba.trim()) { setError('nama_lomba', 'Nama lomba wajib diisi'); valid = false; }
    if (!form.tingkat)           { setError('tingkat', 'Tingkat lomba wajib dipilih'); valid = false; }
    if (!form.juara)             { setError('juara', 'Juara wajib dipilih'); valid = false; }
    if (!form.tanggal)           { setError('tanggal', 'Tanggal lomba wajib diisi'); valid = false; }
    if (selectedSiswaIds.value.length === 0) {
        setError('siswa_prestasi', 'Minimal satu siswa harus dipilih');
        valid = false;
    }
    if (!valid) return;
    form.siswa_prestasi = selectedSiswaIds.value;
    form.post('/admin/prestasi');
};
</script>

<template>
    <Head title="Tambah Prestasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Prestasi Baru</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Tambahkan prestasi dan pencapaian siswa ke dalam sistem</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Prestasi</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lengkapi formulir di bawah untuk menambahkan prestasi baru</p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="p-6">
                        <div class="space-y-8">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Nama Lomba -->
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nama Lomba <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                            <span v-html="TrophyIcon()" class="text-gray-400"></span>
                                        </div>
                                        <input v-model="form.nama_lomba" @input="delete localErrors['nama_lomba']"
                                            type="text" placeholder="Contoh: Olimpiade Matematika, Lomba Karya Ilmiah"
                                            :class="localErrors.nama_lomba || form.errors.nama_lomba ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                    </div>
                                    <p v-if="localErrors.nama_lomba || form.errors.nama_lomba" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                        <span v-html="ErrIcon()"></span>{{ localErrors.nama_lomba || form.errors.nama_lomba }}
                                    </p>
                                </div>

                                <!-- Tingkat -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tingkat Lomba <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select v-model="form.tingkat" @change="delete localErrors['tingkat']"
                                            :class="localErrors.tingkat || form.errors.tingkat ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option value="">Pilih tingkat lomba</option>
                                            <option v-for="t in tingkatOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                        </div>
                                    </div>
                                    <p v-if="localErrors.tingkat || form.errors.tingkat" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                        <span v-html="ErrIcon()"></span>{{ localErrors.tingkat || form.errors.tingkat }}
                                    </p>
                                </div>

                                <!-- Juara -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Juara <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select v-model="form.juara" @change="delete localErrors['juara']"
                                            :class="localErrors.juara || form.errors.juara ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option value="">Pilih peringkat</option>
                                            <option v-for="j in juaraOptions" :key="j.value" :value="j.value">{{ j.label }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                        </div>
                                    </div>
                                    <p v-if="localErrors.juara || form.errors.juara" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                        <span v-html="ErrIcon()"></span>{{ localErrors.juara || form.errors.juara }}
                                    </p>
                                </div>

                                <!-- Penyelenggara -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Penyelenggara <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                                    </label>
                                    <input v-model="form.penyelenggara" type="text"
                                        placeholder="Nama institusi atau organisasi penyelenggara"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                </div>

                                <!-- Tanggal — FIX: hapus :max-date, pakai toInputFormat lokal -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Lomba <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <button type="button" @click="showCalendar = !showCalendar"
                                            :class="localErrors.tanggal || form.errors.tanggal ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="flex w-full items-center gap-3 rounded-xl border-0 bg-gray-50 py-3 px-4 text-left ring-1 ring-inset transition focus:bg-white focus:outline-none focus:ring-2 dark:bg-gray-800 dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <span v-html="CalendarIcon()" class="text-gray-400 flex-shrink-0"></span>
                                            <span class="flex-1 text-sm" :class="tanggalValue ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400 dark:text-gray-500'">
                                                {{ tanggalValue ? formatDisplay(tanggalValue) : 'Pilih tanggal lomba' }}
                                            </span>
                                            <span v-html="ChevronDownIcon()" class="text-gray-400 flex-shrink-0 transition-transform" :class="showCalendar ? 'rotate-180' : ''"></span>
                                        </button>
                                        <!-- FIX: tidak ada :max-date → bisa pilih tanggal masa depan -->
                                        <div v-if="showCalendar" class="absolute left-0 top-full z-50 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
                                            <DatePicker
                                                v-model="tanggalValue"
                                                @dayclick="onSelectTanggal"
                                                color="blue"
                                                is-expanded
                                                class="rounded-2xl"
                                            />
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih tanggal pelaksanaan lomba</p>
                                    <p v-if="localErrors.tanggal || form.errors.tanggal" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                        <span v-html="ErrIcon()"></span>{{ localErrors.tanggal || form.errors.tanggal }}
                                    </p>
                                </div>

                                <!-- Deskripsi -->
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Deskripsi <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                                    </label>
                                    <textarea v-model="form.deskripsi" rows="4"
                                        placeholder="Tuliskan deskripsi atau keterangan tambahan tentang prestasi ini..."
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 resize-none"></textarea>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Maksimal 5000 karakter</p>
                                    <p v-if="form.errors.deskripsi" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                        <span v-html="ErrIcon()"></span>{{ form.errors.deskripsi }}
                                    </p>
                                </div>
                            </div>

                            <!-- Foto -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Dokumentasi</label>
                                <div v-if="imagePreview" class="flex items-center gap-4">
                                    <img :src="imagePreview" alt="Preview foto"
                                        class="h-20 w-32 rounded-xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                    <div class="space-y-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ form.foto?.name }}</p>
                                        <div class="flex items-center gap-3">
                                            <label for="foto-input" class="text-xs font-medium text-blue-600 hover:text-blue-700 cursor-pointer dark:text-blue-400">Ganti foto</label>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button type="button" @click="removeFoto" class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400">Hapus foto</button>
                                        </div>
                                    </div>
                                </div>
                                <label v-else for="foto-input"
                                    class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-8 cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700 dark:hover:bg-blue-900/10">
                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Klik untuk upload</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">JPG, JPEG, PNG maksimal 2MB</p>
                                    </div>
                                </label>
                                <input id="foto-input" ref="fileInputRef" type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" @change="onFotoChange" />
                                <p v-if="localErrors.foto || form.errors.foto" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ localErrors.foto || form.errors.foto }}
                                </p>
                            </div>

                            <!-- Siswa -->
                            <div class="space-y-3">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-white">Siswa Berprestasi <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter berdasarkan angkatan, lalu centang siswa yang meraih prestasi ini</p>
                                </div>

                                <div class="rounded-xl border border-indigo-200 bg-white overflow-hidden dark:border-indigo-800 dark:bg-gray-800/50">
                                    <div class="border-b border-indigo-100 bg-indigo-50/50 px-4 py-4 dark:border-indigo-900 dark:bg-indigo-900/5 space-y-3">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
                                            <div class="space-y-1.5 sm:w-56">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Filter Angkatan</label>
                                                <div class="relative">
                                                    <select v-model="selectedAngkatan" @change="onAngkatanChange"
                                                        class="block w-full appearance-none rounded-lg border-0 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                        <option value="">Semua Angkatan</option>
                                                        <option v-for="a in angkatanList" :key="a.value" :value="a.value">Angkatan {{ a.label }}</option>
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                </div>
                                            </div>
                                            <div class="flex-1 space-y-1.5">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Cari Siswa</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><span v-html="SearchIcon()" class="text-gray-400"></span></div>
                                                    <input v-model="searchSiswa" type="text" placeholder="Cari nama atau NIS siswa..."
                                                        class="block w-full rounded-lg border-0 bg-white py-2.5 pl-9 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:placeholder:text-gray-500" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <span v-if="selectedAngkatan">{{ getSiswaByAngkatan.length }} siswa<span v-if="searchSiswa"> · {{ filteredSiswa.length }} hasil</span></span>
                                                <span v-else>{{ props.siswa.length }} total siswa</span>
                                                <span v-if="selectedSiswaIds.length > 0"> · <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ selectedSiswaIds.length }} dipilih</span></span>
                                            </p>
                                            <div class="flex gap-2">
                                                <button v-if="filteredSiswa.length > 0 && !allFilteredSelected" @click="selectAllVisible" type="button" class="inline-flex items-center rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">Pilih Semua</button>
                                                <button v-if="filteredSiswa.length > 0 && allFilteredSelected" @click="clearAllVisible" type="button" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Batalkan Semua</button>
                                                <button v-if="selectedSiswaIds.length > 0" @click="clearAllSelected" type="button" class="inline-flex items-center rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">Hapus Pilihan</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                                        <div v-if="!selectedAngkatan && !searchSiswa" class="flex flex-col items-center justify-center py-10 gap-3">
                                            <div class="h-12 w-12 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Pilih angkatan atau ketik nama siswa<br/>untuk melihat daftar siswa</p>
                                        </div>
                                        <div v-else-if="filteredSiswa.length === 0" class="flex flex-col items-center justify-center py-10 gap-2">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada siswa yang sesuai</p>
                                            <button v-if="searchSiswa" @click="searchSiswa = ''" type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Hapus pencarian</button>
                                        </div>
                                        <label v-for="s in filteredSiswa" :key="s.id"
                                            class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors"
                                            :class="isSiswaSelected(s.id) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''">
                                            <input type="checkbox" :checked="isSiswaSelected(s.id)" @change="toggleSiswa(s.id)"
                                                class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700" />
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                <img v-if="s.foto" :src="`/storage/${s.foto}`" :alt="s.nama" class="h-9 w-9 rounded-full object-cover flex-shrink-0 border border-gray-200 dark:border-gray-600" />
                                                <div v-else class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex-shrink-0 flex items-center justify-center text-sm font-bold text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">{{ s.nama.charAt(0) }}</div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ s.nama }}</p>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 flex-shrink-0">{{ s.angkatan }}</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        NIS: {{ s.nis }}
                                                        <span v-if="s.existing_prestasi.length > 0" class="ml-1">· {{ s.existing_prestasi.length }} prestasi lainnya</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="selectedSiswaIds.length > 0" class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 dark:bg-green-900/10 dark:border-green-800">
                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ selectedSiswaIds.length }} siswa terpilih</p>
                                    <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">{{ selectedSiswaNames }}</p>
                                </div>

                                <p v-if="localErrors.siswa_prestasi || form.errors.siswa_prestasi" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ localErrors.siswa_prestasi || form.errors.siswa_prestasi }}
                                </p>
                            </div>

                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:items-center">
                            <button type="submit" :disabled="form.processing"
                                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Menyimpan Prestasi...' : 'Simpan Prestasi' }}
                            </button>
                            <Link href="/admin/prestasi"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                <span v-html="ArrowLeftIcon()"></span>Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div v-if="showCalendar" class="fixed inset-0 z-40" @click="closeCalendar"></div>
    </AppLayout>
</template>
