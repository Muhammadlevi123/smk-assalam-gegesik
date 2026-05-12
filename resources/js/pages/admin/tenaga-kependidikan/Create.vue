<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;

interface TahunAjaran { id: number; tahun: string; }
interface JabatanItem { value: string; label: string; }
interface Props {
    tahunAjaran: TahunAjaran[];
    jabatanList: JabatanItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Tenaga Kependidikan', href: '/admin/tenaga-kependidikan' },
    { title: 'Tambah Tenaga Kependidikan', href: '/admin/tenaga-kependidikan/create' },
];

const form = useForm({
    nama:                '',
    jenis_kelamin:       '',
    jabatan:             '',
    alamat:              '',
    foto:                null as File | null,
    status_tahun_ajaran: [{ tahun_ajaran_id: '', status: 'Aktif' }] as Array<{ tahun_ajaran_id: string; status: string }>,
});

const refNama         = ref<HTMLElement | null>(null);
const refJenisKelamin = ref<HTMLElement | null>(null);
const refJabatan      = ref<HTMLElement | null>(null);
const refStatusRows   = ref<HTMLElement[]>([]);

const localErrors = ref<Record<string, string>>({});
const clearErrors = () => { localErrors.value = {}; };
const setError    = (key: string, msg: string) => { localErrors.value[key] = msg; };
const scrollToEl  = (el: HTMLElement | null) => { if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); };

// ── Combobox Jabatan ──────────────────────────────────────────────
const showJabatanDropdown = ref(false);
const jabatanSearch       = ref('');

const filteredJabatan = computed(() => {
    const q = (jabatanSearch.value || form.jabatan).toLowerCase().trim();
    if (!q) return props.jabatanList;
    return props.jabatanList.filter(j => j.label.toLowerCase().includes(q));
});

const onJabatanInput = () => {
    jabatanSearch.value       = form.jabatan;
    showJabatanDropdown.value = true;
    delete localErrors.value['jabatan'];
};
const onJabatanFocus = () => {
    jabatanSearch.value       = form.jabatan;
    showJabatanDropdown.value = true;
};
const selectJabatan = (val: string) => {
    form.jabatan              = val;
    jabatanSearch.value       = val;
    showJabatanDropdown.value = false;
    delete localErrors.value['jabatan'];
};
const closeJabatanDropdown = () => { showJabatanDropdown.value = false; };

// ── Foto ──────────────────────────────────────────────────────────
const imagePreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const handleImageSelect = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { setError('foto', 'Ukuran file maksimal 2MB.'); return; }
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { setError('foto', 'Format file tidak didukung. Gunakan JPG atau PNG.'); return; }
    delete localErrors.value['foto'];
    form.foto = file;
    const reader = new FileReader();
    reader.onload = e => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
};
const removeImage = () => {
    form.foto = null; imagePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// ── Status helpers ────────────────────────────────────────────────
const addStatusTahunAjaran    = () => form.status_tahun_ajaran.push({ tahun_ajaran_id: '', status: 'Aktif' });
const removeStatusTahunAjaran = (index: number) => { if (form.status_tahun_ajaran.length > 1) form.status_tahun_ajaran.splice(index, 1); };
const getAvailableTahunAjaranForStatus = (currentIndex: number) => {
    const usedIds = form.status_tahun_ajaran.filter((_, i) => i !== currentIndex).map(s => s.tahun_ajaran_id).filter(Boolean);
    return props.tahunAjaran.filter(t => !usedIds.includes(t.id.toString()));
};

// ── Submit ────────────────────────────────────────────────────────
const handleSubmit = async () => {
    clearErrors();
    let firstErrorEl: HTMLElement | null = null;
    const trySetFirst = (el: HTMLElement | null) => { if (!firstErrorEl && el) firstErrorEl = el; };

    if (!form.nama.trim())    { setError('nama', 'Nama lengkap wajib diisi'); trySetFirst(refNama.value); }
    if (!form.jenis_kelamin)  { setError('jenis_kelamin', 'Jenis kelamin wajib dipilih'); trySetFirst(refJenisKelamin.value); }
    if (!form.jabatan.trim()) { setError('jabatan', 'Jabatan wajib diisi'); trySetFirst(refJabatan.value); }

    for (let i = 0; i < form.status_tahun_ajaran.length; i++) {
        if (!form.status_tahun_ajaran[i].tahun_ajaran_id) {
            setError(`status_${i}_tahun`, 'Tahun ajaran wajib dipilih');
            trySetFirst(refStatusRows.value[i] ?? null);
        }
    }

    const statusIds = form.status_tahun_ajaran.map(s => s.tahun_ajaran_id).filter(Boolean);
    if (statusIds.length !== new Set(statusIds).size) {
        setError('status_duplikat', 'Ada tahun ajaran yang sama di pengaturan status');
        trySetFirst(refStatusRows.value[0] ?? null);
    }

    if (Object.keys(localErrors.value).length > 0) { await nextTick(); scrollToEl(firstErrorEl); return; }

    form.post('/admin/tenaga-kependidikan');
};
</script>

<template>
    <Head title="Tambah Tenaga Kependidikan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Tenaga Kependidikan Baru</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Tambahkan data tenaga kependidikan baru ke dalam sistem sekolah</p>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                    <!-- Foto -->
                    <div class="xl:col-span-1">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 h-fit">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Foto Tenaga Kependidikan</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Upload foto profil (opsional)</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-center">
                                    <img v-if="imagePreview" :src="imagePreview" alt="Preview"
                                        class="h-48 w-48 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
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
                                <button v-if="imagePreview" @click="removeImage" type="button"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Foto
                                </button>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">PNG, JPG, JPEG maksimal 2MB</p>
                                <p v-if="localErrors.foto" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.foto }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="xl:col-span-2">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Tenaga Kependidikan</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lengkapi formulir di bawah untuk menambahkan tenaga kependidikan baru.</p>
                            </div>

                            <form @submit.prevent="handleSubmit" class="p-6">
                                <div class="space-y-8">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                        <!-- Nama -->
                                        <div ref="refNama" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <input v-model="form.nama" @input="delete localErrors['nama']" type="text" placeholder="Masukkan nama lengkap"
                                                :class="localErrors.nama || form.errors.nama ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="localErrors.nama || form.errors.nama" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.nama || form.errors.nama }}</p>
                                        </div>

                                        <!-- ✅ Jabatan — COMBOBOX -->
                                        <div ref="refJabatan" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input
                                                    v-model="form.jabatan"
                                                    @input="onJabatanInput"
                                                    @focus="onJabatanFocus"
                                                    type="text"
                                                    placeholder="Ketik atau pilih jabatan..."
                                                    autocomplete="off"
                                                    :class="localErrors.jabatan || form.errors.jabatan ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700"
                                                />
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <span v-html="ChevronDownIcon()" class="text-gray-400" :class="showJabatanDropdown ? 'rotate-180' : ''"></span>
                                                </div>
                                                <!-- Dropdown -->
                                                <div v-if="showJabatanDropdown && filteredJabatan.length > 0"
                                                    class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                                                    <div class="py-1 max-h-48 overflow-y-auto">
                                                        <button v-for="j in filteredJabatan" :key="j.value" type="button"
                                                            @mousedown.prevent="selectJabatan(j.value)"
                                                            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 transition-colors"
                                                            :class="form.jabatan === j.value ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : ''">
                                                            {{ j.label }}
                                                        </button>
                                                    </div>
                                                    <div v-if="form.jabatan && !filteredJabatan.find(j => j.value === form.jabatan)"
                                                        class="border-t border-gray-100 dark:border-gray-700 px-4 py-2.5">
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Jabatan baru: <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ form.jabatan }}"</span></p>
                                                    </div>
                                                </div>
                                                <div v-if="showJabatanDropdown && filteredJabatan.length === 0 && form.jabatan"
                                                    class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 px-4 py-3">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Jabatan baru: <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ form.jabatan }}"</span></p>
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Ketik bebas atau pilih dari daftar yang ada</p>
                                            <p v-if="localErrors.jabatan || form.errors.jabatan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.jabatan || form.errors.jabatan }}</p>
                                        </div>

                                        <!-- Jenis Kelamin -->
                                        <div ref="refJenisKelamin" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select v-model="form.jenis_kelamin" @change="delete localErrors['jenis_kelamin']"
                                                    :class="localErrors.jenis_kelamin || form.errors.jenis_kelamin ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                    class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                                    <option value="">Pilih jenis kelamin</option>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                            </div>
                                            <p v-if="localErrors.jenis_kelamin || form.errors.jenis_kelamin" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.jenis_kelamin || form.errors.jenis_kelamin }}</p>
                                        </div>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="space-y-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                        <textarea v-model="form.alamat" rows="3" placeholder="Masukkan alamat lengkap tenaga kependidikan"
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
                                        <p v-if="localErrors.status_duplikat" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.status_duplikat }}</p>
                                        <div class="space-y-3">
                                            <div v-for="(statusItem, index) in form.status_tahun_ajaran" :key="index"
                                                :ref="el => { if (el) refStatusRows[index] = el as HTMLElement }"
                                                class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-xl border"
                                                :class="statusItem.status === 'Aktif' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/50 dark:border-gray-700'">
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran <span class="text-red-500">*</span></label>
                                                    <div class="relative">
                                                        <select v-model="statusItem.tahun_ajaran_id" @change="delete localErrors[`status_${index}_tahun`]"
                                                            :class="localErrors[`status_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in getAvailableTahunAjaranForStatus(index)" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="localErrors[`status_${index}_tahun`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`status_${index}_tahun`] }}</p>
                                                </div>
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
                                                <div class="flex items-start pt-5">
                                                    <button v-if="form.status_tahun_ajaran.length > 1" @click="removeStatusTahunAjaran(index)" type="button"
                                                        class="ml-auto inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.status_tahun_ajaran" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ form.errors.status_tahun_ajaran }}</p>
                                    </div>

                                </div>

                                <!-- Actions -->
                                <div class="mt-8 flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                                    <button type="submit" :disabled="form.processing"
                                        class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                        <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ form.processing ? 'Menyimpan Data...' : 'Simpan Data' }}
                                    </button>
                                    <Link href="/admin/tenaga-kependidikan"
                                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                        <span v-html="ArrowLeftIcon()"></span>Kembali
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay tutup dropdown jabatan -->
        <div v-if="showJabatanDropdown" class="fixed inset-0 z-40" @click="closeJabatanDropdown()"></div>
    </AppLayout>
</template>
