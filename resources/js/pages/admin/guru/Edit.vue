<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, reactive, nextTick } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;

interface TahunAjaran   { id: number; tahun: string; }
interface Kelas         { id: number; nama_kelas: string; existing_wali_tahun_ajaran: number[]; }
interface ExistingMapel { nama: string; }
interface GuruData {
    id: number;
    nama: string;
    jenis_kelamin: string;
    alamat?: string;
    foto?: string;
    status_tahun_ajaran: Array<{ tahun_ajaran_id: number; status: string }>;
    pengajaran: Array<{ nama_mata_pelajaran: string; tahun_ajaran_id: number }>;
    wali_kelas: Array<{ kelas_id: number; tahun_ajaran_id: number }>;
}
interface Props {
    guru: GuruData;
    tahunAjaran: TahunAjaran[];
    kelas: Kelas[];
    existingMataPelajaran: ExistingMapel[];
    previous_url: string;
}
const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Guru', href: '/admin/guru' },
    { title: `Edit ${props.guru.nama}`, href: `/admin/guru/${props.guru.id}/edit` },
];

const buildInitialStatus = () => props.guru.status_tahun_ajaran.length > 0 ? props.guru.status_tahun_ajaran.map(s => ({ tahun_ajaran_id: s.tahun_ajaran_id.toString(), status: s.status })) : [{ tahun_ajaran_id: '', status: 'Aktif' }];
const buildInitialWali   = () => props.guru.wali_kelas.length > 0 ? props.guru.wali_kelas.map(w => ({ kelas_id: w.kelas_id.toString(), tahun_ajaran_id: w.tahun_ajaran_id.toString() })) : [{ kelas_id: '', tahun_ajaran_id: '' }];
const buildInitialPengajaran = () => props.guru.pengajaran.length > 0 ? props.guru.pengajaran.map(p => ({ tahun_ajaran_id: p.tahun_ajaran_id.toString(), nama_mata_pelajaran: p.nama_mata_pelajaran })) : [{ tahun_ajaran_id: '', nama_mata_pelajaran: '' }];

const formState = reactive({
    nama: props.guru.nama,
    jenis_kelamin: props.guru.jenis_kelamin,
    alamat: props.guru.alamat || '',
    foto: null as File | null,
    status_tahun_ajaran: buildInitialStatus(),
    wali_kelas: buildInitialWali(),
    pengajaran: buildInitialPengajaran(),
    previous_url: props.previous_url,
});

const serverErrors = reactive<Record<string, string>>({});
const processing = ref(false);
const refNama = ref<HTMLElement | null>(null);
const refJK = ref<HTMLElement | null>(null);
const refStatusRows = ref<HTMLElement[]>([]);
const refWaliRows = ref<HTMLElement[]>([]);
const refPengajRows = ref<HTMLElement[]>([]);
const localErrors = ref<Record<string, string>>({});
const setError = (k: string, m: string) => { localErrors.value[k] = m; };
const clearErrors = () => { localErrors.value = {}; };
const scrollToEl = (el: HTMLElement | null) => el?.scrollIntoView({ behavior: 'smooth', block: 'center' });

const imagePreview = ref<string | null>(null);
const existingFoto = ref<string | null>(props.guru.foto ? `/storage/${props.guru.foto}` : null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const hapusFoto = ref(false);
const getCurrentImageUrl = () => existingFoto.value || '/images/default-avatar.png';
const handleImageSelect = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { setError('foto', 'Ukuran file maksimal 2MB.'); return; }
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { setError('foto', 'Format tidak didukung. Gunakan JPG atau PNG.'); return; }
    delete localErrors.value['foto'];
    formState.foto = file;
    hapusFoto.value = false;
    const reader = new FileReader();
    reader.onload = e => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
};
const removeImage = () => { formState.foto = null; imagePreview.value = null; if (fileInputRef.value) fileInputRef.value.value = ''; };
const hapusFotoAction = () => { hapusFoto.value = true; existingFoto.value = null; formState.foto = null; imagePreview.value = null; if (fileInputRef.value) fileInputRef.value.value = ''; };

const tahunAjaranAktif = computed(() => {
    const aktifIds = formState.status_tahun_ajaran.filter(s => s.status === 'Aktif' && s.tahun_ajaran_id !== '').map(s => s.tahun_ajaran_id);
    return props.tahunAjaran.filter(t => aktifIds.includes(t.id.toString()));
});
const hasStatusAktifDipilih = computed(() => tahunAjaranAktif.value.length > 0);

const addStatus = () => formState.status_tahun_ajaran.push({ tahun_ajaran_id: '', status: 'Aktif' });
const removeStatus = (i: number) => {
    if (formState.status_tahun_ajaran.length > 1) {
        const removedId = formState.status_tahun_ajaran[i].tahun_ajaran_id;
        const removedStatus = formState.status_tahun_ajaran[i].status;
        formState.status_tahun_ajaran.splice(i, 1);
        if (removedStatus === 'Aktif' && removedId) {
            const masihAktif = formState.status_tahun_ajaran.some(s => s.tahun_ajaran_id === removedId && s.status === 'Aktif');
            if (!masihAktif) { formState.wali_kelas.forEach(w => { if (w.tahun_ajaran_id === removedId) { w.tahun_ajaran_id = ''; w.kelas_id = ''; } }); formState.pengajaran.forEach(p => { if (p.tahun_ajaran_id === removedId) { p.tahun_ajaran_id = ''; p.nama_mata_pelajaran = ''; } }); }
        }
    }
};
const onStatusTahunChange = (index: number, oldId: string) => {
    if (!oldId) return;
    const masihAktif = formState.status_tahun_ajaran.some((s, i) => i !== index && s.tahun_ajaran_id === oldId && s.status === 'Aktif');
    if (!masihAktif) { formState.wali_kelas.forEach(w => { if (w.tahun_ajaran_id === oldId) { w.tahun_ajaran_id = ''; w.kelas_id = ''; } }); formState.pengajaran.forEach(p => { if (p.tahun_ajaran_id === oldId) { p.tahun_ajaran_id = ''; p.nama_mata_pelajaran = ''; } }); }
};
const onStatusValueChange = (index: number) => {
    const item = formState.status_tahun_ajaran[index];
    if (item.status !== 'Aktif' && item.tahun_ajaran_id) {
        const id = item.tahun_ajaran_id;
        const masihAktif = formState.status_tahun_ajaran.some((s, i) => i !== index && s.tahun_ajaran_id === id && s.status === 'Aktif');
        if (!masihAktif) { formState.wali_kelas.forEach(w => { if (w.tahun_ajaran_id === id) { w.tahun_ajaran_id = ''; w.kelas_id = ''; } }); formState.pengajaran.forEach(p => { if (p.tahun_ajaran_id === id) { p.tahun_ajaran_id = ''; p.nama_mata_pelajaran = ''; } }); }
    }
};
const getAvailableTahunForStatus = (currentIndex: number) => { const used = formState.status_tahun_ajaran.filter((_, i) => i !== currentIndex).map(s => s.tahun_ajaran_id).filter(Boolean); return props.tahunAjaran.filter(t => !used.includes(t.id.toString())); };
const isDupStatus = (taId: string, idx: number) => !!(taId && formState.status_tahun_ajaran.some((s, i) => i !== idx && s.tahun_ajaran_id === taId));

const addWali = () => formState.wali_kelas.push({ kelas_id: '', tahun_ajaran_id: '' });
const removeWali = (i: number) => { if (formState.wali_kelas.length > 1) formState.wali_kelas.splice(i, 1); else formState.wali_kelas[0] = { kelas_id: '', tahun_ajaran_id: '' }; };
const onWaliTahunChange = (i: number) => { formState.wali_kelas[i].kelas_id = ''; delete localErrors.value[`wali_${i}_kelas`]; delete localErrors.value[`wali_${i}_tahun`]; };
const getAvailableKelas = (taId: string, currentIndex: number) => { if (!taId) return []; const id = parseInt(taId); return props.kelas.filter(k => !k.existing_wali_tahun_ajaran.includes(id) && !formState.wali_kelas.some((w, i) => i !== currentIndex && w.kelas_id === k.id.toString() && w.tahun_ajaran_id === taId)); };
const getAvailableTahunForWali = (currentIndex: number) => { const used = formState.wali_kelas.filter((_, i) => i !== currentIndex).map(w => w.tahun_ajaran_id).filter(Boolean); return tahunAjaranAktif.value.filter(t => !used.includes(t.id.toString())); };
const isDupWali = (kelasId: string, taId: string, idx: number) => !!(kelasId && taId && formState.wali_kelas.some((w, i) => i !== idx && w.kelas_id === kelasId && w.tahun_ajaran_id === taId));

const addPengaj = () => formState.pengajaran.push({ tahun_ajaran_id: '', nama_mata_pelajaran: '' });
const removePengaj = (i: number) => { if (formState.pengajaran.length > 1) formState.pengajaran.splice(i, 1); else formState.pengajaran[0] = { tahun_ajaran_id: '', nama_mata_pelajaran: '' }; };
const onPengajTahunChange = (i: number) => { formState.pengajaran[i].nama_mata_pelajaran = ''; delete localErrors.value[`pengaj_${i}_tahun`]; };
const getAvailableTahunForPengaj = () => tahunAjaranAktif.value;
const isPengajPartial = (p: { tahun_ajaran_id: string; nama_mata_pelajaran: string }) => (p.tahun_ajaran_id !== '' && !p.nama_mata_pelajaran) || (!p.tahun_ajaran_id && p.nama_mata_pelajaran !== '');

const hasChanges = computed(() => {
    if (formState.nama !== props.guru.nama) return true;
    if (formState.jenis_kelamin !== props.guru.jenis_kelamin) return true;
    if (formState.alamat !== (props.guru.alamat || '')) return true;
    if (formState.foto !== null) return true;
    if (hapusFoto.value) return true;
    const initStatus = buildInitialStatus();
    if (formState.status_tahun_ajaran.length !== initStatus.length) return true;
    for (let i = 0; i < formState.status_tahun_ajaran.length; i++) {
        if (formState.status_tahun_ajaran[i].tahun_ajaran_id !== (initStatus[i]?.tahun_ajaran_id ?? '') || formState.status_tahun_ajaran[i].status !== (initStatus[i]?.status ?? '')) return true;
    }
    const currentWali = formState.wali_kelas.filter(w => w.kelas_id && w.tahun_ajaran_id).map(w => `${w.kelas_id}:${w.tahun_ajaran_id}`).sort().join('|');
    const originalWali = (props.guru.wali_kelas || []).map(w => `${w.kelas_id}:${w.tahun_ajaran_id}`).sort().join('|');
    if (currentWali !== originalWali) return true;
    const currentPengaj = formState.pengajaran.filter(p => p.tahun_ajaran_id && p.nama_mata_pelajaran).map(p => `${p.tahun_ajaran_id}:${p.nama_mata_pelajaran}`).sort().join('|');
    const originalPengaj = (props.guru.pengajaran || []).map(p => `${p.tahun_ajaran_id}:${p.nama_mata_pelajaran}`).sort().join('|');
    if (currentPengaj !== originalPengaj) return true;
    return false;
});

const handleSubmit = async () => {
    clearErrors();
    Object.keys(serverErrors).forEach(k => delete serverErrors[k]);
    let firstEl: HTMLElement | null = null;
    const trySet = (el: HTMLElement | null) => { if (!firstEl && el) firstEl = el; };
    if (!formState.nama.trim()) { setError('nama', 'Nama wajib diisi'); trySet(refNama.value); }
    if (!formState.jenis_kelamin) { setError('jenis_kelamin', 'Jenis kelamin wajib'); trySet(refJK.value); }
    formState.status_tahun_ajaran.forEach((s, i) => {
        if (!s.tahun_ajaran_id) { setError(`status_${i}_tahun`, 'Tahun ajaran wajib dipilih'); trySet(refStatusRows.value[i]); }
        if (isDupStatus(s.tahun_ajaran_id, i)) { setError(`status_${i}_dup`, 'Tahun ajaran sudah dipakai'); trySet(refStatusRows.value[i]); }
    });
    formState.wali_kelas.forEach((w, i) => {
        const partial = (w.kelas_id && !w.tahun_ajaran_id) || (!w.kelas_id && w.tahun_ajaran_id);
        if (partial) { if (w.kelas_id && !w.tahun_ajaran_id) setError(`wali_${i}_tahun`, 'Tahun ajaran wajib dipilih'); else setError(`wali_${i}_kelas`, 'Kelas wajib dipilih'); trySet(refWaliRows.value[i]); }
        if (isDupWali(w.kelas_id, w.tahun_ajaran_id, i)) { setError(`wali_${i}_dup`, 'Kombinasi kelas & tahun sudah ada'); trySet(refWaliRows.value[i]); }
    });
    formState.pengajaran.forEach((p, i) => {
        if (isPengajPartial(p)) { if (p.tahun_ajaran_id && !p.nama_mata_pelajaran) setError(`pengaj_${i}_mapel`, 'Mata pelajaran wajib dipilih'); else setError(`pengaj_${i}_tahun`, 'Tahun ajaran wajib dipilih'); trySet(refPengajRows.value[i]); }
    });
    if (Object.keys(localErrors.value).length > 0) { await nextTick(); scrollToEl(firstEl); return; }

    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('nama', formState.nama);
    fd.append('jenis_kelamin', formState.jenis_kelamin);
    fd.append('alamat', formState.alamat);
    fd.append('previous_url', formState.previous_url);
    if (formState.foto) fd.append('foto', formState.foto);
    if (hapusFoto.value) fd.append('hapus_foto', '1');
    formState.status_tahun_ajaran.forEach((s, i) => { fd.append(`status_tahun_ajaran[${i}][tahun_ajaran_id]`, s.tahun_ajaran_id); fd.append(`status_tahun_ajaran[${i}][status]`, s.status); });
    let wi = 0;
    formState.wali_kelas.forEach(w => { if (w.kelas_id && w.tahun_ajaran_id) { fd.append(`wali_kelas[${wi}][kelas_id]`, w.kelas_id); fd.append(`wali_kelas[${wi}][tahun_ajaran_id]`, w.tahun_ajaran_id); wi++; } });
    let pi = 0;
    formState.pengajaran.forEach(p => { if (p.tahun_ajaran_id && p.nama_mata_pelajaran) { fd.append(`pengajaran[${pi}][nama_mata_pelajaran]`, p.nama_mata_pelajaran); fd.append(`pengajaran[${pi}][tahun_ajaran_id]`, p.tahun_ajaran_id); pi++; } });

    processing.value = true;
    router.post(`/admin/guru/${props.guru.id}`, fd, {
        preserveScroll: true,
        onError: (errs) => { Object.assign(serverErrors, errs); processing.value = false; },
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <Head :title="`Edit Guru - ${guru.nama}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Guru</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Perbarui informasi untuk guru "{{ guru.nama }}"</p>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <!-- FOTO -->
                    <div class="xl:col-span-1">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 h-fit">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Foto Guru</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui foto profil (opsional)</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-center">
                                    <div v-if="hapusFoto && !imagePreview" class="h-48 w-48 flex items-center justify-center rounded-2xl border-2 border-dashed border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/10">
                                        <div class="text-center"><svg class="mx-auto h-10 w-10 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg><p class="mt-2 text-xs text-red-500 dark:text-red-400">Foto akan dihapus</p></div>
                                    </div>
                                    <div v-else>
                                        <img :src="imagePreview || getCurrentImageUrl()" :alt="guru.nama" class="h-48 w-48 rounded-2xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                        <div v-if="imagePreview" class="mt-2 flex justify-center"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 whitespace-nowrap">Foto Baru</span></div>
                                    </div>
                                </div>
                                <input ref="fileInputRef" @change="handleImageSelect" type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" />
                                <button @click="fileInputRef?.click()" type="button" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    {{ imagePreview ? 'Ganti Foto' : (hapusFoto ? 'Upload Foto Baru' : 'Perbarui Foto') }}
                                </button>
                                <button v-if="(guru.foto && !hapusFoto) || imagePreview" @click="imagePreview ? removeImage() : hapusFotoAction()" type="button" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    Hapus Foto
                                </button>
                                <button v-if="hapusFoto && !imagePreview" @click="hapusFoto = false; existingFoto = guru.foto ? `/storage/${guru.foto}` : null" type="button" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                                    Batalkan Hapus Foto
                                </button>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">PNG, JPG, JPEG maksimal 2MB</p>
                                <p v-if="localErrors.foto" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.foto }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- FORM -->
                    <div class="xl:col-span-2">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Guru</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui data guru dengan status, wali kelas, dan mata pelajaran per tahun ajaran.</p>
                            </div>
                            <form @submit.prevent="handleSubmit" class="p-6">
                                <div class="space-y-8">
                                    <!-- Data Saat Ini -->
                                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Data Guru Saat Ini</h4>
                                        <div class="flex items-center gap-4">
                                            <img :src="getCurrentImageUrl()" :alt="guru.nama" class="h-16 w-16 rounded-xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10 flex-shrink-0" />
                                            <div class="min-w-0 flex-1">
                                                <div class="text-base font-medium text-gray-900 dark:text-white">{{ guru.nama }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ guru.jenis_kelamin }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- DATA DASAR -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div ref="refNama" class="md:col-span-2 space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <input v-model="formState.nama" @input="delete localErrors['nama']" type="text" placeholder="Masukkan nama lengkap"
                                                :class="localErrors.nama || serverErrors.nama ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                            <p v-if="localErrors.nama || serverErrors.nama" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.nama || serverErrors.nama }}</p>
                                        </div>
                                        <div ref="refJK" class="space-y-1.5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <select v-model="formState.jenis_kelamin" @change="delete localErrors['jenis_kelamin']"
                                                    :class="localErrors.jenis_kelamin ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                                    class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                                    <option value="">Pilih jenis kelamin</option>
                                                    <option value="Laki-laki">Laki-laki</option>
                                                    <option value="Perempuan">Perempuan</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                            </div>
                                            <p v-if="localErrors.jenis_kelamin" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.jenis_kelamin }}</p>
                                        </div>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="space-y-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                        <textarea v-model="formState.alamat" rows="3" placeholder="Masukkan alamat lengkap guru" class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700 resize-none"></textarea>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Opsional</p>
                                    </div>

                                    <!-- STATUS TAHUN AJARAN -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between flex-wrap gap-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-700 dark:text-white">Status per Tahun Ajaran <span class="text-red-500">*</span></h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Status <span class="font-semibold text-green-700 dark:text-green-400">Aktif</span> membuka bagian Wali Kelas dan Mata Pelajaran.</p>
                                            </div>
                                            <button @click="addStatus" type="button" class="inline-flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                Tambah Status
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <div v-for="(s, index) in formState.status_tahun_ajaran" :key="index"
                                                :ref="el => { if (el) refStatusRows[index] = el as HTMLElement }"
                                                class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-xl border"
                                                :class="s.status === 'Aktif' ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800/50 dark:border-gray-700'">
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran <span class="text-red-500">*</span></label>
                                                    <div class="relative">
                                                        <select :value="s.tahun_ajaran_id"
                                                            @change="(e) => { const oldId = s.tahun_ajaran_id; s.tahun_ajaran_id = (e.target as HTMLSelectElement).value; onStatusTahunChange(index, oldId); delete localErrors[`status_${index}_tahun`]; }"
                                                            :class="localErrors[`status_${index}_tahun`] || localErrors[`status_${index}_dup`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in getAvailableTahunForStatus(index)" :key="t.id" :value="t.id.toString()">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="localErrors[`status_${index}_tahun`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`status_${index}_tahun`] }}</p>
                                                    <p v-if="localErrors[`status_${index}_dup`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`status_${index}_dup`] }}</p>
                                                </div>
                                                <div class="md:col-span-1 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Status</label>
                                                    <div class="relative">
                                                        <select v-model="s.status" @change="onStatusValueChange(index)" class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="Aktif">Aktif</option>
                                                            <option value="Nonaktif">Nonaktif</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-start pt-5">
                                                    <button v-if="formState.status_tahun_ajaran.length > 1" @click="removeStatus(index)" type="button" class="ml-auto inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="serverErrors.status_tahun_ajaran" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.status_tahun_ajaran }}</p>
                                    </div>

                                    <!-- WALI KELAS -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between flex-wrap gap-3">
                                            <div>
                                                <h4 class="text-sm font-semibold" :class="hasStatusAktifDipilih ? 'text-gray-700 dark:text-white' : 'text-gray-400 dark:text-gray-500'">Wali Kelas <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span></h4>
                                                <p class="text-sm mt-1" :class="hasStatusAktifDipilih ? 'text-gray-500 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500'">
                                                    <span v-if="hasStatusAktifDipilih">Pilih dari tahun ajaran Aktif <span class="font-medium text-green-700 dark:text-green-400">({{ tahunAjaranAktif.map(t => t.tahun).join(', ') }})</span>. Biarkan kosong jika tidak ada.</span>
                                                    <span v-else>Isi status <strong>Aktif</strong> terlebih dahulu.</span>
                                                </p>
                                            </div>
                                            <button v-if="hasStatusAktifDipilih" @click="addWali" type="button" class="inline-flex items-center gap-2 rounded-lg bg-purple-50 px-3 py-2 text-sm font-medium text-purple-700 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:hover:bg-purple-900/40">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                Tambah
                                            </button>
                                        </div>
                                        <div v-if="!hasStatusAktifDipilih" class="flex items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 p-6 dark:border-gray-700">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 flex-shrink-0"><svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></div>
                                            <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bagian ini terkunci</p><p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Set status <strong>Aktif</strong> pada langkah 1 untuk membuka.</p></div>
                                        </div>
                                        <div v-else class="space-y-3">
                                            <div v-for="(w, index) in formState.wali_kelas" :key="index"
                                                :ref="el => { if (el) refWaliRows[index] = el as HTMLElement }"
                                                class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 rounded-xl border border-purple-200 bg-purple-50 dark:border-purple-800 dark:bg-purple-900/10">
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                                                    <div class="relative">
                                                        <select v-model="w.tahun_ajaran_id" @change="onWaliTahunChange(index)" :class="localErrors[`wali_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'" class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-purple-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in getAvailableTahunForWali(index)" :key="t.id" :value="t.id.toString()">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="localErrors[`wali_${index}_tahun`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`wali_${index}_tahun`] }}</p>
                                                    <p class="text-xs text-green-600 dark:text-green-400">Dari tahun ajaran Aktif</p>
                                                </div>
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Kelas</label>
                                                    <div class="relative">
                                                        <select v-model="w.kelas_id" :disabled="!w.tahun_ajaran_id" @change="delete localErrors[`wali_${index}_kelas`]" :class="localErrors[`wali_${index}_kelas`] || localErrors[`wali_${index}_dup`] ? 'ring-red-400' : 'ring-gray-200'" class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-purple-600 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:disabled:bg-gray-800">
                                                            <option value="">{{ w.tahun_ajaran_id ? 'Pilih kelas' : 'Pilih tahun ajaran dulu' }}</option>
                                                            <option v-for="k in getAvailableKelas(w.tahun_ajaran_id, index)" :key="k.id" :value="k.id.toString()">{{ k.nama_kelas }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="w.tahun_ajaran_id" class="text-xs text-blue-600 dark:text-blue-400">{{ getAvailableKelas(w.tahun_ajaran_id, index).length }} kelas tersedia</p>
                                                    <p v-if="localErrors[`wali_${index}_kelas`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`wali_${index}_kelas`] }}</p>
                                                    <p v-if="localErrors[`wali_${index}_dup`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`wali_${index}_dup`] }}</p>
                                                </div>
                                                <div class="flex items-start pt-5">
                                                    <button @click="removeWali(index)" type="button" class="ml-auto inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="serverErrors.wali_kelas" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.wali_kelas }}</p>
                                    </div>

                                    <!-- MATA PELAJARAN -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between flex-wrap gap-3">
                                            <div>
                                                <h4 class="text-sm font-semibold" :class="hasStatusAktifDipilih ? 'text-gray-700 dark:text-white' : 'text-gray-400 dark:text-gray-500'">Mata Pelajaran yang Diampu <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span></h4>
                                                <p class="text-sm mt-1" :class="hasStatusAktifDipilih ? 'text-gray-500 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500'">
                                                    <span v-if="hasStatusAktifDipilih">Pilih tahun ajaran Aktif, kemudian pilih mata pelajaran. Biarkan kosong jika tidak ada.</span>
                                                    <span v-else>Isi status <strong>Aktif</strong> terlebih dahulu.</span>
                                                </p>
                                            </div>
                                            <button v-if="hasStatusAktifDipilih" @click="addPengaj" type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/40">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                Tambah
                                            </button>
                                        </div>
                                        <div v-if="!hasStatusAktifDipilih" class="flex items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 p-6 dark:border-gray-700">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 flex-shrink-0"><svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></div>
                                            <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bagian ini terkunci</p><p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Set status <strong>Aktif</strong> pada langkah 1 untuk membuka.</p></div>
                                        </div>
                                        <div v-else class="space-y-3">
                                            <div v-for="(p, index) in formState.pengajaran" :key="index"
                                                :ref="el => { if (el) refPengajRows[index] = el as HTMLElement }"
                                                class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 rounded-xl border border-indigo-200 bg-indigo-50 dark:border-indigo-800 dark:bg-indigo-900/10">
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                                                    <div class="relative">
                                                        <select v-model="p.tahun_ajaran_id" @change="onPengajTahunChange(index)" :class="localErrors[`pengaj_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'" class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in getAvailableTahunForPengaj()" :key="t.id" :value="t.id.toString()">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="localErrors[`pengaj_${index}_tahun`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`pengaj_${index}_tahun`] }}</p>
                                                    <p class="text-xs text-green-600 dark:text-green-400">Dari tahun ajaran Aktif</p>
                                                </div>
                                                <div class="md:col-span-2 space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Mata Pelajaran</label>
                                                    <div class="relative">
                                                        <select v-model="p.nama_mata_pelajaran" :disabled="!p.tahun_ajaran_id" @change="delete localErrors[`pengaj_${index}_mapel`]" :class="localErrors[`pengaj_${index}_mapel`] ? 'ring-red-400' : 'ring-gray-200'" class="block w-full appearance-none rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-indigo-600 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:disabled:bg-gray-800">
                                                            <option value="">{{ p.tahun_ajaran_id ? 'Pilih mata pelajaran' : 'Pilih tahun ajaran dulu' }}</option>
                                                            <option v-for="m in existingMataPelajaran" :key="m.nama" :value="m.nama">{{ m.nama }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="p.tahun_ajaran_id && existingMataPelajaran.length === 0" class="text-xs text-amber-600 dark:text-amber-400">Belum ada data mata pelajaran</p>
                                                    <p v-if="localErrors[`pengaj_${index}_mapel`]" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`pengaj_${index}_mapel`] }}</p>
                                                </div>
                                                <div class="flex items-start pt-5">
                                                    <button @click="removePengaj(index)" type="button" class="ml-auto inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="serverErrors.pengajaran" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.pengajaran }}</p>
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
                                    <button type="submit" :disabled="processing || !hasChanges" class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                        <span v-if="!processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ processing ? 'Memperbarui Data...' : 'Perbarui Guru' }}
                                    </button>
                                    <Link :href="previous_url || '/admin/guru'" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
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
