<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, computed, nextTick } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l7-3 7 3z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ChevronUpIcon   = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;
const SearchIcon      = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>`;

interface TahunAjaran { id: number; tahun: string; }
interface Guru {
    id: number; nama: string;
    tahun_ajaran_tersedia: number[];
    existing_wali_tahun_ajaran: number[];
}
interface SiswaData {
    id: number; nis: string; nama: string; jenis_kelamin: string; foto?: string;
    tahun_ajaran_aktif: number[];
    existing_kelas_tahun: Array<{ kelas_id: number; tahun_ajaran_id: number }>;
}
// ✅ Tambah jurusanList dari controller
interface Props {
    tahunAjaran:  TahunAjaran[];
    guru:         Guru[];
    siswa:        SiswaData[];
    jurusanList:  Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Kelas', href: '/admin/kelas' },
    { title: 'Tambah Kelas', href: '/admin/kelas/create' },
];

const tingkatOptions = [
    { value: 'X',   label: 'Kelas X'   },
    { value: 'XI',  label: 'Kelas XI'  },
    { value: 'XII', label: 'Kelas XII' },
];

// ── State ─────────────────────────────────────────────────────────
const formState = reactive({
    nama_kelas:      '',
    jurusan:         '',
    tingkat:         '',
    wali_kelas:      [{ guru_id: '', tahun_ajaran_id: '' }] as Array<{ guru_id: string; tahun_ajaran_id: string }>,
    siswa_per_tahun: [{ tahun_ajaran_id: '', siswa_ids: [] as string[] }] as Array<{ tahun_ajaran_id: string; siswa_ids: string[] }>,
});

const serverErrors = reactive<Record<string, string>>({});
const processing   = ref(false);

const refNamaKelas = ref<HTMLElement | null>(null);
const refJurusan   = ref<HTMLElement | null>(null);
const refTingkat   = ref<HTMLElement | null>(null);
const refWaliRows  = ref<HTMLElement[]>([]);
const refSiswaRows = ref<HTMLElement[]>([]);

const localErrors = ref<Record<string, string>>({});
const setError    = (k: string, m: string) => { localErrors.value[k] = m; };
const clearErrors = () => { localErrors.value = {}; };
const scrollToEl  = (el: HTMLElement | null) => el?.scrollIntoView({ behavior: 'smooth', block: 'center' });

// ── Combobox Jurusan ──────────────────────────────────────────────
const showJurusanDropdown = ref(false);
const jurusanSearch       = ref('');

const filteredJurusan = computed(() => {
    const q = (jurusanSearch.value || formState.jurusan).toLowerCase().trim();
    if (!q) return props.jurusanList;
    return props.jurusanList.filter(j => j.label.toLowerCase().includes(q));
});

const onJurusanInput = () => {
    jurusanSearch.value       = formState.jurusan;
    showJurusanDropdown.value = true;
    delete localErrors.value['jurusan'];
};
const onJurusanFocus = () => {
    jurusanSearch.value       = formState.jurusan;
    showJurusanDropdown.value = true;
};
const selectJurusan = (val: string) => {
    formState.jurusan         = val;
    jurusanSearch.value       = val;
    showJurusanDropdown.value = false;
    delete localErrors.value['jurusan'];
};
const closeJurusanDropdown = () => { showJurusanDropdown.value = false; };

// ── Accordion state ───────────────────────────────────────────────
const expandedWali  = ref<number[]>([0]);
const expandedSiswa = ref<number[]>([0]);

const toggleWaliExpand  = (i: number) => { const idx = expandedWali.value.indexOf(i); if (idx === -1) expandedWali.value.push(i); else expandedWali.value.splice(idx, 1); };
const toggleSiswaExpand = (i: number) => { const idx = expandedSiswa.value.indexOf(i); if (idx === -1) expandedSiswa.value.push(i); else expandedSiswa.value.splice(idx, 1); };
const isWaliExpanded    = (i: number) => expandedWali.value.includes(i);
const isSiswaExpanded   = (i: number) => expandedSiswa.value.includes(i);

const siswaSearch    = ref<string[]>(['']);
const getSiswaSearch = (i: number) => siswaSearch.value[i] ?? '';
const setSiswaSearch = (i: number, v: string) => { siswaSearch.value[i] = v; };

// ── Wali helpers ──────────────────────────────────────────────────
const addWaliKelas = () => { formState.wali_kelas.push({ guru_id: '', tahun_ajaran_id: '' }); expandedWali.value.push(formState.wali_kelas.length - 1); };
const removeWaliKelas = (i: number) => {
    if (formState.wali_kelas.length > 1) { formState.wali_kelas.splice(i, 1); expandedWali.value = expandedWali.value.filter(n => n !== i).map(n => n > i ? n - 1 : n); }
    else { formState.wali_kelas[0] = { guru_id: '', tahun_ajaran_id: '' }; }
};
const onWaliTahunChange = (index: number) => {
    const w = formState.wali_kelas[index];
    if (w.guru_id && !getAvailableGuruForWali(w.tahun_ajaran_id, index).some(g => g.id.toString() === w.guru_id))
        formState.wali_kelas[index].guru_id = '';
};
const getAvailableGuruForWali = (taId: string, currentIndex: number) => {
    if (!taId) return [];
    const id = parseInt(taId);
    return props.guru.filter(g =>
        g.tahun_ajaran_tersedia.includes(id) &&
        !g.existing_wali_tahun_ajaran.includes(id) &&
        !formState.wali_kelas.some((w, i) => i !== currentIndex && w.guru_id === g.id.toString() && w.tahun_ajaran_id === taId)
    );
};
const isDupWali = (guruId: string, taId: string, idx: number) =>
    !!(guruId && taId && formState.wali_kelas.some((w, i) => i !== idx && w.guru_id === guruId && w.tahun_ajaran_id === taId));
const waliAccordionLabel = (index: number) => {
    const w = formState.wali_kelas[index];
    if (!w.tahun_ajaran_id) return `Wali Kelas ${index + 1}`;
    const tahun = props.tahunAjaran.find(t => t.id.toString() === w.tahun_ajaran_id)?.tahun ?? '';
    if (!w.guru_id) return tahun;
    const guru = props.guru.find(g => g.id.toString() === w.guru_id)?.nama ?? '';
    return `${tahun} — ${guru}`;
};

// ── Siswa helpers ─────────────────────────────────────────────────
const addSiswaGroup = () => { formState.siswa_per_tahun.push({ tahun_ajaran_id: '', siswa_ids: [] }); siswaSearch.value.push(''); expandedSiswa.value.push(formState.siswa_per_tahun.length - 1); };
const removeSiswaGroup = (i: number) => {
    if (formState.siswa_per_tahun.length > 1) { formState.siswa_per_tahun.splice(i, 1); siswaSearch.value.splice(i, 1); expandedSiswa.value = expandedSiswa.value.filter(n => n !== i).map(n => n > i ? n - 1 : n); }
    else { formState.siswa_per_tahun[0] = { tahun_ajaran_id: '', siswa_ids: [] }; siswaSearch.value[0] = ''; }
};
const getAvailableTahunForSiswaGroup = (currentIndex: number) => {
    const used = formState.siswa_per_tahun.filter((_, i) => i !== currentIndex).map(g => g.tahun_ajaran_id).filter(Boolean);
    return props.tahunAjaran.filter(t => !used.includes(t.id.toString()));
};
const onSiswaGroupTahunChange = (index: number) => { formState.siswa_per_tahun[index].siswa_ids = []; setSiswaSearch(index, ''); };
const getAvailableSiswaForGroup = (taId: string, currentIndex: number) => {
    if (!taId) return [];
    const id = parseInt(taId);
    return props.siswa.filter(s => {
        const aktif    = s.tahun_ajaran_aktif.includes(id);
        const sudahAda = s.existing_kelas_tahun.some(e => e.tahun_ajaran_id === id);
        const diGroup  = formState.siswa_per_tahun.some((g, i) => i !== currentIndex && g.siswa_ids.includes(s.id.toString()));
        return aktif && !sudahAda && !diGroup;
    });
};
const getFilteredSiswa = (taId: string, currentIndex: number) => {
    const search = getSiswaSearch(currentIndex).toLowerCase();
    const avail  = getAvailableSiswaForGroup(taId, currentIndex);
    if (!search) return avail;
    return avail.filter(s => s.nama.toLowerCase().includes(search) || s.nis.toLowerCase().includes(search));
};
const toggleSiswa     = (gi: number, sid: string) => { const ids = formState.siswa_per_tahun[gi].siswa_ids; const idx = ids.indexOf(sid); if (idx === -1) ids.push(sid); else ids.splice(idx, 1); };
const isSiswaSelected = (gi: number, id: string) => formState.siswa_per_tahun[gi].siswa_ids.includes(id);
const selectAllSiswa  = (gi: number) => { const g = formState.siswa_per_tahun[gi]; getFilteredSiswa(g.tahun_ajaran_id, gi).forEach(s => { if (!g.siswa_ids.includes(s.id.toString())) g.siswa_ids.push(s.id.toString()); }); };
const clearAllSiswa   = (gi: number) => { formState.siswa_per_tahun[gi].siswa_ids = []; };
const siswaAccordionLabel = (index: number) => {
    const g = formState.siswa_per_tahun[index];
    if (!g.tahun_ajaran_id) return `Tahun Ajaran ${index + 1}`;
    const tahun = props.tahunAjaran.find(t => t.id.toString() === g.tahun_ajaran_id)?.tahun ?? '';
    if (g.siswa_ids.length === 0) return tahun;
    return `${tahun} — ${g.siswa_ids.length} siswa dipilih`;
};

// ── Submit ────────────────────────────────────────────────────────
const handleSubmit = async () => {
    clearErrors();
    Object.keys(serverErrors).forEach(k => delete serverErrors[k]);

    let firstEl: HTMLElement | null = null;
    const trySet = (el: HTMLElement | null) => { if (!firstEl && el) firstEl = el; };

    if (!formState.nama_kelas.trim()) { setError('nama_kelas', 'Nama kelas wajib diisi'); trySet(refNamaKelas.value); }
    if (!formState.jurusan.trim())    { setError('jurusan', 'Jurusan wajib diisi');       trySet(refJurusan.value); }
    if (!formState.tingkat)           { setError('tingkat', 'Tingkat wajib dipilih');     trySet(refTingkat.value); }

    formState.wali_kelas.forEach((w, i) => {
        const isEmpty = !w.guru_id && !w.tahun_ajaran_id;
        if (isEmpty) return;
        if (w.guru_id && !w.tahun_ajaran_id)  { setError(`wali_${i}_tahun`, 'Tahun ajaran wajib dipilih'); trySet(refWaliRows.value[i]); if (!isWaliExpanded(i)) expandedWali.value.push(i); }
        if (!w.guru_id && w.tahun_ajaran_id)  { setError(`wali_${i}_guru`, 'Guru wajib dipilih');          trySet(refWaliRows.value[i]); if (!isWaliExpanded(i)) expandedWali.value.push(i); }
        if (w.guru_id && w.tahun_ajaran_id && isDupWali(w.guru_id, w.tahun_ajaran_id, i)) { setError(`wali_${i}_dup`, 'Kombinasi guru & tahun sudah ada'); trySet(refWaliRows.value[i]); if (!isWaliExpanded(i)) expandedWali.value.push(i); }
    });

    const usedTahun: string[] = [];
    formState.siswa_per_tahun.forEach((g, i) => {
        if (!g.tahun_ajaran_id) return;
        if (usedTahun.includes(g.tahun_ajaran_id)) { setError(`siswa_${i}_tahun`, 'Tahun ajaran sudah dipakai di grup lain'); trySet(refSiswaRows.value[i]); if (!isSiswaExpanded(i)) expandedSiswa.value.push(i); }
        usedTahun.push(g.tahun_ajaran_id);
    });

    if (Object.keys(localErrors.value).length > 0) { await nextTick(); scrollToEl(firstEl); return; }

    const fd = new FormData();
    fd.append('nama_kelas', formState.nama_kelas);
    fd.append('jurusan',    formState.jurusan);
    fd.append('tingkat',    formState.tingkat);

    formState.wali_kelas.filter(w => w.guru_id && w.tahun_ajaran_id).forEach((w, i) => {
        fd.append(`wali_kelas[${i}][guru_id]`,         w.guru_id);
        fd.append(`wali_kelas[${i}][tahun_ajaran_id]`, w.tahun_ajaran_id);
    });

    let fi = 0;
    formState.siswa_per_tahun.forEach(g => {
        if (g.tahun_ajaran_id && g.siswa_ids.length > 0) {
            g.siswa_ids.forEach(sid => {
                fd.append(`siswa_kelas[${fi}][siswa_id]`,        sid);
                fd.append(`siswa_kelas[${fi}][tahun_ajaran_id]`, g.tahun_ajaran_id);
                fi++;
            });
        }
    });

    processing.value = true;
    router.post('/admin/kelas', fd, {
        preserveScroll: true,
        onError: (errs) => { Object.assign(serverErrors, errs); processing.value = false; },
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <Head title="Tambah Kelas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Kelas Baru</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Tambahkan data kelas baru ke dalam sistem sekolah</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Kelas</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lengkapi formulir di bawah untuk menambahkan kelas baru</p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="p-6">
                        <div class="space-y-8">

                            <!-- ══ INFO DASAR ══════════════════════════════════ -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">

                                <!-- Nama Kelas -->
                                <div ref="refNamaKelas" class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kelas <span class="text-red-500">*</span></label>
                                    <input v-model="formState.nama_kelas" @input="delete localErrors['nama_kelas']" type="text" placeholder="Contoh: X-TKJ-1"
                                        :class="localErrors.nama_kelas || serverErrors.nama_kelas ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Contoh: X-TKJ-1, XI-IPA-2</p>
                                    <p v-if="localErrors.nama_kelas || serverErrors.nama_kelas" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.nama_kelas || serverErrors.nama_kelas }}</p>
                                </div>

                                <!-- Tingkat -->
                                <div ref="refTingkat" class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select v-model="formState.tingkat" @change="delete localErrors['tingkat']"
                                            :class="localErrors.tingkat ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option value="">Pilih tingkat</option>
                                            <option v-for="t in tingkatOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                    </div>
                                    <p v-if="localErrors.tingkat" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.tingkat }}</p>
                                </div>

                                <!-- ✅ Jurusan — COMBOBOX -->
                                <div ref="refJurusan" class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input
                                            v-model="formState.jurusan"
                                            @input="onJurusanInput"
                                            @focus="onJurusanFocus"
                                            type="text"
                                            placeholder="Ketik atau pilih jurusan..."
                                            autocomplete="off"
                                            :class="localErrors.jurusan || serverErrors.jurusan ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700"
                                        />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span v-html="ChevronDownIcon()" class="text-gray-400" :class="showJurusanDropdown ? 'rotate-180' : ''"></span>
                                        </div>
                                        <!-- Dropdown -->
                                        <div v-if="showJurusanDropdown && filteredJurusan.length > 0"
                                            class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                                            <div class="py-1 max-h-48 overflow-y-auto">
                                                <button v-for="j in filteredJurusan" :key="j.value" type="button"
                                                    @mousedown.prevent="selectJurusan(j.value)"
                                                    class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 transition-colors"
                                                    :class="formState.jurusan === j.value ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : ''">
                                                    {{ j.label }}
                                                </button>
                                            </div>
                                            <div v-if="formState.jurusan && !filteredJurusan.find(j => j.value === formState.jurusan)"
                                                class="border-t border-gray-100 dark:border-gray-700 px-4 py-2.5">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Jurusan baru: <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ formState.jurusan }}"</span></p>
                                            </div>
                                        </div>
                                        <div v-if="showJurusanDropdown && filteredJurusan.length === 0 && formState.jurusan"
                                            class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 px-4 py-3">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Jurusan baru: <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ formState.jurusan }}"</span></p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Ketik bebas atau pilih dari daftar yang ada</p>
                                    <p v-if="localErrors.jurusan || serverErrors.jurusan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.jurusan || serverErrors.jurusan }}</p>
                                </div>
                            </div>

                            <!-- ══ WALI KELAS — ACCORDION ══════════════════════ -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-white">Wali Kelas <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span></h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih tahun ajaran dulu, guru akan difilter otomatis. Biarkan kosong jika tidak ada.</p>
                                    </div>
                                    <button @click="addWaliKelas" type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(wali, index) in formState.wali_kelas" :key="index"
                                        :ref="el => { if (el) refWaliRows[index] = el as HTMLElement }"
                                        class="rounded-xl border border-green-200 bg-white overflow-hidden dark:border-green-800 dark:bg-gray-800/50">
                                        <button type="button" @click="toggleWaliExpand(index)"
                                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="h-6 w-6 flex-shrink-0 flex items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ index + 1 }}</div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ waliAccordionLabel(index) }}</span>
                                                <span v-if="localErrors[`wali_${index}_tahun`] || localErrors[`wali_${index}_guru`] || localErrors[`wali_${index}_dup`]"
                                                    class="flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Ada error
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                                <button @click.stop="removeWaliKelas(index)" type="button"
                                                    class="inline-flex items-center justify-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                                <span v-html="isWaliExpanded(index) ? ChevronUpIcon() : ChevronDownIcon()" class="text-gray-400"></span>
                                            </div>
                                        </button>
                                        <div v-show="isWaliExpanded(index)" class="border-t border-green-100 bg-green-50/50 px-4 py-4 dark:border-green-900 dark:bg-green-900/5">
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div class="space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                                                    <div class="relative">
                                                        <select v-model="wali.tahun_ajaran_id" @change="onWaliTahunChange(index); delete localErrors[`wali_${index}_tahun`]"
                                                            :class="localErrors[`wali_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in tahunAjaran" :key="t.id" :value="t.id.toString()">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="localErrors[`wali_${index}_tahun`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`wali_${index}_tahun`] }}</p>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Guru</label>
                                                    <div class="relative">
                                                        <select v-model="wali.guru_id" :disabled="!wali.tahun_ajaran_id" @change="delete localErrors[`wali_${index}_guru`]"
                                                            :class="localErrors[`wali_${index}_guru`] || localErrors[`wali_${index}_dup`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-gray-800">
                                                            <option value="">{{ wali.tahun_ajaran_id ? 'Pilih guru' : 'Pilih tahun ajaran dulu' }}</option>
                                                            <option v-for="g in getAvailableGuruForWali(wali.tahun_ajaran_id, index)" :key="g.id" :value="g.id.toString()">{{ g.nama }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                    </div>
                                                    <p v-if="wali.tahun_ajaran_id" class="text-xs text-blue-600 dark:text-blue-400">{{ getAvailableGuruForWali(wali.tahun_ajaran_id, index).length }} guru tersedia</p>
                                                    <p v-if="localErrors[`wali_${index}_guru`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`wali_${index}_guru`] }}</p>
                                                    <p v-if="localErrors[`wali_${index}_dup`]"  class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`wali_${index}_dup`] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="serverErrors.wali_kelas" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.wali_kelas }}</p>
                            </div>

                            <!-- ══ SISWA PER TAHUN — ACCORDION ═════════════════ -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-white">Siswa per Tahun Ajaran <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span></h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih tahun ajaran, lalu centang siswa. Hanya siswa berstatus <span class="font-medium text-green-700 dark:text-green-400">Aktif</span> yang tersedia.</p>
                                    </div>
                                    <button @click="addSiswaGroup" type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-3 py-2 text-sm font-medium text-purple-700 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:hover:bg-purple-900/40">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(group, index) in formState.siswa_per_tahun" :key="index"
                                        :ref="el => { if (el) refSiswaRows[index] = el as HTMLElement }"
                                        class="rounded-xl border border-purple-200 bg-white overflow-hidden dark:border-purple-800 dark:bg-gray-800/50">
                                        <button type="button" @click="toggleSiswaExpand(index)"
                                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-colors">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="h-6 w-6 flex-shrink-0 flex items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">{{ index + 1 }}</div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ siswaAccordionLabel(index) }}</span>
                                                <span v-if="localErrors[`siswa_${index}_tahun`]"
                                                    class="flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Ada error
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                                <button @click.stop="removeSiswaGroup(index)" type="button"
                                                    class="inline-flex items-center justify-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                                <span v-html="isSiswaExpanded(index) ? ChevronUpIcon() : ChevronDownIcon()" class="text-gray-400"></span>
                                            </div>
                                        </button>
                                        <div v-show="isSiswaExpanded(index)" class="border-t border-purple-100 bg-purple-50/50 px-4 py-4 dark:border-purple-900 dark:bg-purple-900/5 space-y-3">
                                            <div class="space-y-1.5">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                                                <div class="relative w-full sm:w-56">
                                                    <select v-model="group.tahun_ajaran_id" @change="onSiswaGroupTahunChange(index); delete localErrors[`siswa_${index}_tahun`]"
                                                        :class="localErrors[`siswa_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'"
                                                        class="block w-full appearance-none rounded-lg border-0 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-purple-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                        <option value="">Pilih tahun ajaran</option>
                                                        <option v-for="t in getAvailableTahunForSiswaGroup(index)" :key="t.id" :value="t.id.toString()">{{ t.tahun }}</option>
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                                </div>
                                                <p v-if="localErrors[`siswa_${index}_tahun`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors[`siswa_${index}_tahun`] }}</p>
                                            </div>
                                            <template v-if="group.tahun_ajaran_id">
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="relative flex-1">
                                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><span v-html="SearchIcon()" class="text-gray-400"></span></div>
                                                        <input :value="getSiswaSearch(index)" @input="setSiswaSearch(index, ($event.target as HTMLInputElement).value)"
                                                            type="text" placeholder="Cari nama atau NIS siswa..."
                                                            class="block w-full rounded-lg border-0 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-purple-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:placeholder:text-gray-500" />
                                                    </div>
                                                    <div class="flex gap-2 flex-shrink-0">
                                                        <button @click="selectAllSiswa(index)" type="button" class="inline-flex items-center rounded-lg bg-purple-100 px-3 py-2 text-xs font-medium text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-300">Pilih Semua</button>
                                                        <button v-if="group.siswa_ids.length > 0" @click="clearAllSiswa(index)" type="button" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Hapus Semua</button>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ getAvailableSiswaForGroup(group.tahun_ajaran_id, index).length }} siswa aktif tersedia
                                                    <span v-if="getSiswaSearch(index)"> · {{ getFilteredSiswa(group.tahun_ajaran_id, index).length }} hasil pencarian</span>
                                                </p>
                                                <div v-if="getFilteredSiswa(group.tahun_ajaran_id, index).length > 0"
                                                    class="max-h-60 overflow-y-auto rounded-lg border border-purple-200 bg-white dark:border-purple-800 dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                                    <label v-for="s in getFilteredSiswa(group.tahun_ajaran_id, index)" :key="s.id"
                                                        class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-colors"
                                                        :class="isSiswaSelected(index, s.id.toString()) ? 'bg-purple-50 dark:bg-purple-900/20' : ''">
                                                        <input type="checkbox" :checked="isSiswaSelected(index, s.id.toString())" @change="toggleSiswa(index, s.id.toString())"
                                                            class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-purple-600 focus:ring-purple-600 dark:border-gray-600 dark:bg-gray-700" />
                                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                                            <img v-if="s.foto" :src="`/storage/${s.foto}`" :alt="s.nama" class="h-7 w-7 rounded-full object-cover flex-shrink-0 border border-gray-200 dark:border-gray-600" />
                                                            <div v-else class="h-7 w-7 rounded-full bg-gray-200 dark:bg-gray-600 flex-shrink-0 flex items-center justify-center text-xs font-medium text-gray-600 dark:text-gray-400">{{ s.nama.charAt(0) }}</div>
                                                            <div class="min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ s.nama }}</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.nis }} · {{ s.jenis_kelamin }}</p>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div v-else-if="getSiswaSearch(index)" class="rounded-lg border border-dashed border-purple-200 p-5 text-center dark:border-purple-800">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada siswa yang cocok dengan pencarian</p>
                                                </div>
                                                <div v-else class="rounded-lg border border-dashed border-purple-200 p-5 text-center dark:border-purple-800">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">Semua siswa aktif sudah terdaftar di kelas lain pada tahun ini</p>
                                                </div>
                                            </template>
                                            <div v-else class="flex items-center gap-3 rounded-lg border border-dashed border-gray-200 p-4 dark:border-gray-700">
                                                <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Pilih tahun ajaran untuk melihat daftar siswa</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="serverErrors.siswa_kelas" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ serverErrors.siswa_kelas }}</p>
                            </div>

                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:items-center">
                            <button type="submit" :disabled="processing"
                                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ processing ? 'Menyimpan Kelas...' : 'Simpan Kelas' }}
                            </button>
                            <Link href="/admin/kelas"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                <span v-html="ArrowLeftIcon()"></span>Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Overlay tutup dropdown jurusan -->
        <div v-if="showJurusanDropdown" class="fixed inset-0 z-40" @click="closeJurusanDropdown()"></div>
    </AppLayout>
</template>
