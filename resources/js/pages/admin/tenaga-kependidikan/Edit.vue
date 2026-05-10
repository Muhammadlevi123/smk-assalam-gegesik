<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, reactive, nextTick } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;

interface TahunAjaran { id: number; tahun: string; }

interface TenagaKependidikan {
    id: number;
    nama: string;
    jenis_kelamin: string;
    jabatan: string;
    alamat?: string;
    foto?: string;
    status_form_data?: Array<{ tahun_ajaran_id: number; status: string }>;
    created_at: string;
    updated_at: string;
}

interface Props {
    tenagaKependidikan: TenagaKependidikan;
    tahunAjaran: TahunAjaran[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Tenaga Kependidikan', href: '/admin/tenaga-kependidikan' },
    { title: 'Edit Tenaga Kependidikan', href: `/admin/tenaga-kependidikan/${props.tenagaKependidikan?.id}/edit` },
];

// ── Ref untuk scroll target ────────────────────────────────────────
const refNama         = ref<HTMLElement | null>(null);
const refJenisKelamin = ref<HTMLElement | null>(null);
const refJabatan      = ref<HTMLElement | null>(null);
const refStatusRows   = ref<HTMLElement[]>([]);

// ── Validasi error lokal ───────────────────────────────────────────
const localErrors  = ref<Record<string, string>>({});
const clearErrors  = () => { localErrors.value = {}; };
const setError     = (key: string, msg: string) => { localErrors.value[key] = msg; };
const scrollToEl   = (el: HTMLElement | null) => { if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); };

// ── Helper: prepare initial status data dari controller ───────────
const prepareInitialStatusData = () => {
    if (props.tenagaKependidikan?.status_form_data?.length) {
        return props.tenagaKependidikan.status_form_data.map(item => ({
            tahun_ajaran_id: item.tahun_ajaran_id.toString(),
            status:          item.status,
        }));
    }
    return [{ tahun_ajaran_id: '', status: 'Aktif' }];
};

// ── State ──────────────────────────────────────────────────────────
const formState = reactive({
    nama:                props.tenagaKependidikan?.nama          || '',
    jenis_kelamin:       props.tenagaKependidikan?.jenis_kelamin || '',
    jabatan:             props.tenagaKependidikan?.jabatan       || '',
    alamat:              props.tenagaKependidikan?.alamat        || '',
    foto:                null as File | null,
    status_tahun_ajaran: prepareInitialStatusData(),
});

const serverErrors = reactive<Record<string, string>>({});
const processing   = ref(false);

// ── Status helpers ─────────────────────────────────────────────────
const addStatusTahunAjaran = () => formState.status_tahun_ajaran.push({ tahun_ajaran_id: '', status: 'Aktif' });

const removeStatusTahunAjaran = (index: number) => {
    if (formState.status_tahun_ajaran.length > 1) formState.status_tahun_ajaran.splice(index, 1);
};

const getAvailableTahunAjaranForStatus = (currentIndex: number) => {
    const usedIds = formState.status_tahun_ajaran
        .filter((_, i) => i !== currentIndex)
        .map(s => s.tahun_ajaran_id)
        .filter(Boolean);
    return props.tahunAjaran.filter(t => !usedIds.includes(t.id.toString()));
};

// ── Foto ───────────────────────────────────────────────────────────
const imagePreview = ref<string | null>(null);
const existingFoto = ref<string | null>(props.tenagaKependidikan?.foto ? `/storage/${props.tenagaKependidikan.foto}` : null);
const fileInputRef = ref<HTMLInputElement | null>(null);
// Flag hapus foto yang sudah tersimpan di server
const hapusFoto    = ref(false);

const getCurrentImageUrl = () => existingFoto.value || '/images/default-avatar.png';

const handleImageSelect = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { setError('foto', 'Ukuran file maksimal 2MB.'); return; }
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { setError('foto', 'Format file tidak didukung. Gunakan JPG atau PNG.'); return; }
    delete localErrors.value['foto'];
    formState.foto = file;
    hapusFoto.value = false; // batalkan hapus jika upload foto baru
    const reader = new FileReader();
    reader.onload = e => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
};

// Hapus foto preview baru (belum disimpan)
const removeImage = () => {
    formState.foto = null; imagePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// Hapus foto yang sudah tersimpan di server (existingFoto)
const hapusFotoAction = () => {
    hapusFoto.value = true;
    existingFoto.value = null;
    formState.foto = null;
    imagePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// ── hasChanges ─────────────────────────────────────────────────────
const hasChanges = computed(() => {
    if (!props.tenagaKependidikan) return false;
    if (formState.nama !== props.tenagaKependidikan.nama ||
        formState.jenis_kelamin !== props.tenagaKependidikan.jenis_kelamin ||
        formState.jabatan !== props.tenagaKependidikan.jabatan ||
        formState.alamat !== (props.tenagaKependidikan.alamat || '') ||
        formState.foto !== null ||
        hapusFoto.value) return true;

    const initStatus = prepareInitialStatusData();
    if (formState.status_tahun_ajaran.length !== initStatus.length) return true;
    for (let i = 0; i < formState.status_tahun_ajaran.length; i++) {
        if (formState.status_tahun_ajaran[i].tahun_ajaran_id !== initStatus[i]?.tahun_ajaran_id ||
            formState.status_tahun_ajaran[i].status !== initStatus[i]?.status) return true;
    }
    return false;
});

// ── Submit dengan validasi inline ─────────────────────────────────
const handleSubmit = async () => {
    clearErrors();
    let firstErrorEl: HTMLElement | null = null;
    const trySetFirst = (el: HTMLElement | null) => { if (!firstErrorEl && el) firstErrorEl = el; };

    if (!formState.nama.trim()) {
        setError('nama', 'Nama lengkap wajib diisi');
        trySetFirst(refNama.value);
    }
    if (!formState.jenis_kelamin) {
        setError('jenis_kelamin', 'Jenis kelamin wajib dipilih');
        trySetFirst(refJenisKelamin.value);
    }
    if (!formState.jabatan.trim()) {
        setError('jabatan', 'Jabatan wajib diisi');
        trySetFirst(refJabatan.value);
    }

    for (let i = 0; i < formState.status_tahun_ajaran.length; i++) {
        if (!formState.status_tahun_ajaran[i].tahun_ajaran_id) {
            setError(`status_${i}_tahun`, 'Tahun ajaran wajib dipilih');
            trySetFirst(refStatusRows.value[i] ?? null);
        }
    }

    // Duplikat status
    const statusIds = formState.status_tahun_ajaran.map(s => s.tahun_ajaran_id).filter(Boolean);
    if (statusIds.length !== new Set(statusIds).size) {
        setError('status_duplikat', 'Ada tahun ajaran yang sama di pengaturan status');
        trySetFirst(refStatusRows.value[0] ?? null);
    }

    if (Object.keys(localErrors.value).length > 0) {
        await nextTick();
        scrollToEl(firstErrorEl);
        return;
    }

    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('nama',          formState.nama);
    fd.append('jenis_kelamin', formState.jenis_kelamin);
    fd.append('jabatan',       formState.jabatan);
    fd.append('alamat',        formState.alamat || '');
    if (formState.foto instanceof File) fd.append('foto', formState.foto);
    if (hapusFoto.value) fd.append('hapus_foto', '1');

    formState.status_tahun_ajaran.forEach((s, i) => {
        fd.append(`status_tahun_ajaran[${i}][tahun_ajaran_id]`, s.tahun_ajaran_id);
        fd.append(`status_tahun_ajaran[${i}][status]`,          s.status);
    });

    processing.value = true;

    router.post(`/admin/tenaga-kependidikan/${props.tenagaKependidikan.id}`, fd, {
        preserveScroll: true,
        onError: (errs) => {
            Object.keys(serverErrors).forEach(k => delete serverErrors[k]);
            Object.assign(serverErrors, errs);
            processing.value = false;
        },
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <Head :title="`Edit Tenaga Kependidikan - ${tenagaKependidikan?.nama || 'Loading...'}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Tenaga Kependidikan</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Perbarui informasi untuk "{{ tenagaKependidikan?.nama }}"</p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- Foto -->
                    <div class="xl:col-span-1">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 h-fit">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Foto Tenaga Kependidikan</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui foto profil (opsional)</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-center">
                                    <div>
                                        <!-- Jika hapusFoto aktif dan tidak ada preview baru: tampilkan placeholder merah -->
                                        <div v-if="hapusFoto && !imagePreview"
                                            class="h-48 w-48 flex items-center justify-center rounded-2xl border-2 border-dashed border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/10">
                                            <div class="text-center">
                                                <svg class="mx-auto h-10 w-10 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <p class="mt-2 text-xs text-red-500 dark:text-red-400">Foto akan dihapus</p>
                                            </div>
                                        </div>
                                        <!-- Normal: tampilkan foto preview atau existing -->
                                        <div v-else>
                                            <img :src="imagePreview || getCurrentImageUrl()" :alt="tenagaKependidikan?.nama"
                                                class="h-48 w-48 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                            <div v-if="imagePreview" class="mt-2 flex justify-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 whitespace-nowrap">Foto Baru</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input ref="fileInputRef" @change="handleImageSelect" type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" />
                                <button @click="fileInputRef?.click()" type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    {{ imagePreview ? 'Ganti Foto' : (hapusFoto ? 'Upload Foto Baru' : 'Perbarui Foto') }}
                                </button>
                                <!-- Tombol Hapus Foto — muncul jika ada foto tersimpan (belum dihapus) ATAU ada imagePreview baru -->
                                <button v-if="(tenagaKependidikan.foto && !hapusFoto) || imagePreview" @click="imagePreview ? removeImage() : hapusFotoAction()" type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Foto
                                </button>
                                <!-- Batalkan hapus foto -->
                                <button v-if="hapusFoto && !imagePreview" @click="hapusFoto = false; existingFoto = tenagaKependidikan.foto ? `/storage/${tenagaKependidikan.foto}` : null" type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                                    Batalkan Hapus Foto
                                </button>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">PNG, JPG, JPEG maksimal 2MB</p>
                                <p v-if="localErrors.foto" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ localErrors.foto }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="xl:col-span-2">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Tenaga Kependidikan</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui data tenaga kependidikan dengan status per tahun ajaran.</p>
                            </div>

                            <form @submit.prevent="handleSubmit" class="p-6">
                                <div class="space-y-8">

                                    <!-- Data Saat Ini -->
                                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Data Tenaga Kependidikan Saat Ini</h4>
                                        <div class="flex items-center gap-4">
                                            <img :src="getCurrentImageUrl()" :alt="tenagaKependidikan.nama"
                                                class="h-16 w-16 rounded-xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10 flex-shrink-0" />
                                            <div class="min-w-0 flex-1">
                                                <div class="text-base font-medium text-gray-900 dark:text-white">{{ tenagaKependidikan.nama }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ tenagaKependidikan.jabatan }} · {{ tenagaKependidikan.jenis_kelamin }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Dasar -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                        <!-- Nama -->
                                        <div ref="refNama" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <input v-model="formState.nama" @input="delete localErrors['nama']" type="text" placeholder="Masukkan nama lengkap"
                                                :class="localErrors.nama || serverErrors.nama ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="localErrors.nama" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.nama }}</p>
                                            <p v-if="serverErrors.nama" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.nama }}</p>
                                        </div>

                                        <!-- Jabatan -->
                                        <div ref="refJabatan" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan <span class="text-red-500">*</span></label>
                                            <input v-model="formState.jabatan" @input="delete localErrors['jabatan']" type="text" placeholder="Contoh: Tata Usaha, Teknisi Lab"
                                                :class="localErrors.jabatan || serverErrors.jabatan ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="localErrors.jabatan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.jabatan }}</p>
                                            <p v-if="serverErrors.jabatan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.jabatan }}</p>
                                        </div>

                                        <!-- Jenis Kelamin -->
                                        <div ref="refJenisKelamin" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select v-model="formState.jenis_kelamin" @change="delete localErrors['jenis_kelamin']"
                                                    :class="localErrors.jenis_kelamin || serverErrors.jenis_kelamin ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                    class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                                    <option value="">Pilih jenis kelamin</option>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                            </div>
                                            <p v-if="localErrors.jenis_kelamin" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.jenis_kelamin }}</p>
                                            <p v-if="serverErrors.jenis_kelamin" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.jenis_kelamin }}</p>
                                        </div>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="space-y-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                        <textarea v-model="formState.alamat" rows="3" placeholder="Masukkan alamat lengkap tenaga kependidikan"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 resize-none"></textarea>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Opsional</p>
                                    </div>

                                    <!-- Status per Tahun Ajaran -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-700 dark:text-white">Status per Tahun Ajaran <span class="text-red-500">*</span></h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tentukan status untuk setiap tahun ajaran yang diikuti.</p>
                                            </div>
                                            <button @click="addStatusTahunAjaran" type="button"
                                                class="inline-flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                Tambah Status
                                            </button>
                                        </div>

                                        <p v-if="localErrors.status_duplikat" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                            <span v-html="ErrIcon()"></span>{{ localErrors.status_duplikat }}
                                        </p>

                                        <div class="space-y-3">
                                            <div v-for="(statusItem, index) in formState.status_tahun_ajaran" :key="index"
                                                :ref="el => { if (el) refStatusRows[index] = el as HTMLElement }"
                                                class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-xl border"
                                                :class="statusItem.status === 'Aktif' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/50 dark:border-gray-700'">

                                                <!-- Tahun Ajaran -->
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran <span class="text-red-500">*</span></label>
                                                    <div class="relative">
                                                        <select v-model="statusItem.tahun_ajaran_id"
                                                            @change="delete localErrors[`status_${index}_tahun`]"
                                                            :class="localErrors[`status_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in getAvailableTahunAjaranForStatus(index)" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="localErrors[`status_${index}_tahun`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                        <span v-html="ErrIcon()"></span>{{ localErrors[`status_${index}_tahun`] }}
                                                    </p>
                                                </div>

                                                <!-- Status -->
                                                <div class="md:col-span-1 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                                                    <div class="relative">
                                                        <select v-model="statusItem.status"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="Aktif">Aktif</option>
                                                            <option value="Nonaktif">Nonaktif</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                </div>

                                                <!-- Badge + Hapus -->
                                                <div class="flex items-center justify-between pt-5">
                                                    <span v-if="statusItem.status === 'Aktif' && statusItem.tahun_ajaran_id"
                                                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Aktif
                                                    </span>
                                                    <span v-else class="flex-1"></span>
                                                    <button v-if="formState.status_tahun_ajaran.length > 1" @click="removeStatusTahunAjaran(index)" type="button"
                                                        class="ml-auto inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <p v-if="serverErrors.status_tahun_ajaran" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                            <span v-html="ErrIcon()"></span>{{ serverErrors.status_tahun_ajaran }}
                                        </p>
                                    </div>

                                    <!-- Indikator perubahan -->
                                    <div v-if="hasChanges" class="rounded-xl bg-blue-50 p-4 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                                            <span class="text-sm text-blue-700 dark:text-blue-300">Ada perubahan yang belum disimpan</span>
                                        </div>
                                    </div>

                                </div>

                                <!-- Actions -->
                                <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                                    <button type="submit" :disabled="processing || !hasChanges"
                                        class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                        <span v-if="!processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ processing ? 'Memperbarui Data...' : 'Perbarui Data' }}
                                    </button>
                                    <Link href="/admin/tenaga-kependidikan"
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
