<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const GraduationIcon  = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443a55.381 55.381 0 0 1 5.25 2.882V15" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;

interface TahunAjaranStatus {
    tahun_ajaran_id: number;
    status:          string;
    tahun_ajaran:    { id: number; tahun: string };
}

interface SiswaOption {
    id:                   number;
    nama:                 string;
    nis:                  string;
    angkatan:             string;
    label:                string;
    tahun_lulus?:         number | null;
    tahun_ajaran_status?: TahunAjaranStatus[];
}

interface Props {
    siswaList: SiswaOption[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',     href: '/admin/dashboard' },
    { title: 'Data Alumni',   href: '/admin/alumni' },
    { title: 'Tambah Alumni', href: '/admin/alumni/create' },
];

const currentYear = new Date().getFullYear();

// ── State ──────────────────────────────────────────────────────────
const selectedAngkatan  = ref('');
const availableAngkatan = ref<string[]>([]);
const imagePreview      = ref<string | null>(null);
const fileInputRef      = ref<HTMLInputElement | null>(null);
const localErrors       = ref<Record<string, string>>({});

const form = useForm({
    siswa_id:        '' as string | number,
    pekerjaan:       '',
    foto:            null as File | null,
    alamat_sekarang: '',
    no_telepon:      '',
    email:           '',
    tahun_lulus:     null as number | null,
});

// ── Helper utama: ambil tahun akhir dari "2022/2023" → 2023 ───────
const parseTahunAkhir = (raw: string | null | undefined): number | null => {
    if (!raw) return null;
    const parts = raw.trim().split('/');
    const last  = parts[parts.length - 1]?.trim();
    const num   = parseInt(last);
    return isNaN(num) ? null : num;
};

const getTahunLulusFromSiswa = (siswa: SiswaOption | null): number | null => {
    if (!siswa) return null;
    if (siswa.tahun_lulus) return siswa.tahun_lulus;
    const lulusStatus = siswa.tahun_ajaran_status?.find(s => s.status === 'Lulus');
    return parseTahunAkhir(lulusStatus?.tahun_ajaran?.tahun ?? null);
};

const labelTahunLulus = (siswa: SiswaOption | null): string => {
    const tahun = getTahunLulusFromSiswa(siswa);
    return tahun ? tahun.toString() : '-';
};

// ── onMounted: isi daftar angkatan ────────────────────────────────
onMounted(() => {
    const set = new Set<string>();
    props.siswaList.forEach(s => set.add(s.angkatan.toString()));
    availableAngkatan.value = Array.from(set).sort((a, b) => parseInt(b) - parseInt(a));
});

// ── Computed ───────────────────────────────────────────────────────
const filteredSiswaList = computed(() => {
    if (!selectedAngkatan.value) return [];
    return props.siswaList.filter(s => s.angkatan.toString() === selectedAngkatan.value);
});

const selectedSiswa = computed(() =>
    form.siswa_id
        ? props.siswaList.find(s => s.id.toString() === form.siswa_id.toString()) ?? null
        : null
);

const countLulusSiswa = computed(() => props.siswaList.length);

// ── Watch ──────────────────────────────────────────────────────────
watch(selectedAngkatan, () => {
    form.siswa_id    = '';
    form.tahun_lulus = null;
    delete localErrors.value['siswa_id'];
});

watch(() => form.siswa_id, (newVal) => {
    delete localErrors.value['siswa_id'];
    form.tahun_lulus = newVal ? getTahunLulusFromSiswa(selectedSiswa.value) : null;
});

// ── Foto ───────────────────────────────────────────────────────────
const handleImageSelect = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        localErrors.value['foto'] = 'Ukuran file maksimal 2MB.';
        return;
    }
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
        localErrors.value['foto'] = 'Format file tidak didukung. Gunakan JPG atau PNG.';
        return;
    }
    delete localErrors.value['foto'];
    form.foto = file;
    const reader = new FileReader();
    reader.onload = e => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    form.foto = null;
    imagePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// ── Submit ─────────────────────────────────────────────────────────
const handleSubmit = () => {
    localErrors.value = {};

    if (!selectedAngkatan.value) {
        localErrors.value['angkatan'] = 'Angkatan wajib dipilih';
        return;
    }
    if (!form.siswa_id) {
        localErrors.value['siswa_id'] = 'Siswa wajib dipilih';
        return;
    }
    if (!form.pekerjaan.trim()) {
        localErrors.value['pekerjaan'] = 'Pekerjaan wajib diisi';
        return;
    }

    form.post('/admin/alumni', { preserveScroll: true });
};
</script>

<template>
    <Head title="Tambah Alumni" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Alumni Baru</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Tambahkan data alumni baru dari siswa yang sudah lulus</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 sm:gap-6 sm:text-sm">
                        <div class="flex items-center gap-2">
                            <span v-html="GraduationIcon()" class="text-green-500"></span>
                            <span>{{ countLulusSiswa }} siswa dengan status "Lulus" tersedia</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                            <span>{{ availableAngkatan.length }} angkatan tersedia</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- ══ FOTO (kiri) ════════════════════════════════════════ -->
                    <div class="xl:col-span-1">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 h-fit">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Foto Alumni</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Upload foto terbaru alumni (opsional)</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-center">
                                    <div v-if="imagePreview">
                                        <img :src="imagePreview" alt="Preview"
                                            class="h-48 w-48 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                    </div>
                                    <div v-else class="flex h-48 w-48 items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada foto</p>
                                        </div>
                                    </div>
                                </div>
                                <input ref="fileInputRef" @change="handleImageSelect" type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" />
                                <button @click="fileInputRef?.click()" type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    {{ imagePreview ? 'Ganti Foto' : 'Upload Foto' }}
                                </button>
                                <!-- Tombol Hapus Foto — muncul jika ada preview -->
                                <button v-if="imagePreview" @click="removeImage" type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Foto
                                </button>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">PNG, JPG, JPEG maksimal 2MB</p>
                                <p v-if="localErrors.foto || form.errors.foto" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ localErrors.foto || form.errors.foto }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ══ FORM (kanan) ═══════════════════════════════════════ -->
                    <div class="xl:col-span-2">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Alumni</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lengkapi formulir di bawah untuk menambahkan alumni baru.</p>
                            </div>

                            <form @submit.prevent="handleSubmit" class="p-6">
                                <div class="space-y-6">

                                    <!-- Filter Angkatan -->
                                    <div class="space-y-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Pilih Angkatan <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select v-model="selectedAngkatan"
                                                :class="localErrors.angkatan ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                                <option value="">Pilih angkatan terlebih dahulu</option>
                                                <option v-for="a in availableAngkatan" :key="a" :value="a">Angkatan {{ a }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                            </div>
                                        </div>
                                        <p v-if="localErrors.angkatan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                            <span v-html="ErrIcon()"></span>{{ localErrors.angkatan }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih angkatan untuk menampilkan siswa yang sudah lulus</p>
                                    </div>

                                    <!-- Pilih Siswa -->
                                    <div class="space-y-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Pilih Siswa <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select v-model="form.siswa_id" :disabled="!selectedAngkatan"
                                                :class="(localErrors.siswa_id || form.errors.siswa_id) ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                                <option value="">{{ selectedAngkatan ? 'Pilih siswa yang sudah lulus' : 'Pilih angkatan terlebih dahulu' }}</option>
                                                <option v-for="s in filteredSiswaList" :key="s.id" :value="s.id">{{ s.label }}</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                            </div>
                                        </div>

                                        <div v-if="selectedAngkatan && filteredSiswaList.length === 0" class="flex items-center gap-2 text-sm text-yellow-600 dark:text-yellow-400">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                                            <span>Tidak ada siswa lulus dari angkatan {{ selectedAngkatan }} yang belum menjadi alumni</span>
                                        </div>
                                        <div v-else-if="selectedAngkatan && filteredSiswaList.length > 0" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span>{{ filteredSiswaList.length }} siswa lulus tersedia</span>
                                        </div>

                                        <p v-if="localErrors.siswa_id || form.errors.siswa_id" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                            <span v-html="ErrIcon()"></span>{{ localErrors.siswa_id || form.errors.siswa_id }}
                                        </p>
                                    </div>

                                    <!-- Info Siswa Terpilih -->
                                    <div v-if="selectedSiswa" class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/50 flex-shrink-0">
                                                <span v-html="GraduationIcon()" class="text-green-600 dark:text-green-400"></span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-green-900 dark:text-green-100">{{ selectedSiswa.nama }}</p>
                                                <p class="text-sm text-green-700 dark:text-green-300">
                                                    NIS: {{ selectedSiswa.nis }} · Angkatan {{ selectedSiswa.angkatan }}
                                                    <span v-if="labelTahunLulus(selectedSiswa) !== '-'">
                                                        · Lulus tahun <strong>{{ labelTahunLulus(selectedSiswa) }}</strong>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grid: Pekerjaan + Tahun Lulus -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                        <!-- Pekerjaan -->
                                        <div class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Pekerjaan Saat Ini <span class="text-red-500">*</span>
                                            </label>
                                            <input v-model="form.pekerjaan" @input="delete localErrors['pekerjaan']" type="text"
                                                placeholder="Contoh: Software Engineer, Guru, Entrepreneur"
                                                :class="(localErrors.pekerjaan || form.errors.pekerjaan) ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="localErrors.pekerjaan || form.errors.pekerjaan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                <span v-html="ErrIcon()"></span>{{ localErrors.pekerjaan || form.errors.pekerjaan }}
                                            </p>
                                        </div>

                                        <!-- Tahun Lulus -->
                                        <div class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Lulus</label>
                                            <input v-model="form.tahun_lulus" type="number" :min="1900" :max="currentYear"
                                                placeholder="Otomatis terisi saat memilih siswa"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <span v-if="selectedSiswa && labelTahunLulus(selectedSiswa) !== '-'">
                                                    Otomatis dari tahun akhir tahun ajaran Lulus
                                                    <span class="font-medium text-green-700 dark:text-green-400">({{ labelTahunLulus(selectedSiswa) }})</span>
                                                </span>
                                                <span v-else>Otomatis terisi dari tahun akhir tahun ajaran Lulus siswa</span>
                                            </p>
                                            <p v-if="form.errors.tahun_lulus" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                <span v-html="ErrIcon()"></span>{{ form.errors.tahun_lulus }}
                                            </p>
                                        </div>

                                        <!-- No Telepon -->
                                        <div class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
                                            <input v-model="form.no_telepon" type="tel" placeholder="081234567890"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="form.errors.no_telepon" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                <span v-html="ErrIcon()"></span>{{ form.errors.no_telepon }}
                                            </p>
                                        </div>

                                        <!-- Email -->
                                        <div class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Email</label>
                                            <input v-model="form.email" type="email" placeholder="alumni@email.com"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="form.errors.email" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                <span v-html="ErrIcon()"></span>{{ form.errors.email }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Alamat Sekarang -->
                                    <div class="space-y-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Saat Ini</label>
                                        <textarea v-model="form.alamat_sekarang" rows="3"
                                            placeholder="Alamat lengkap tempat tinggal saat ini"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 resize-none"></textarea>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Opsional</p>
                                        <p v-if="form.errors.alamat_sekarang" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                            <span v-html="ErrIcon()"></span>{{ form.errors.alamat_sekarang }}
                                        </p>
                                    </div>

                                </div>

                                <!-- Actions -->
                                <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                                    <button type="submit" :disabled="form.processing"
                                        class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                        <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ form.processing ? 'Menyimpan Alumni...' : 'Simpan Alumni' }}
                                    </button>
                                    <Link href="/admin/alumni"
                                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                        <span v-html="ArrowLeftIcon()"></span>
                                        Kembali
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
