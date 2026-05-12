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

interface TahunAjaran { id: number; tahun: string; }
interface Guru {
    id: number;
    nama: string;
    tahun_ajaran_tersedia: number[];
}
interface NamaItem { value: string; label: string; }
interface Props {
    tahunAjaran:      TahunAjaran[];
    guru:             Guru[];
    existingNamaList: NamaItem[]; // ✅ daftar nama mapel yang sudah ada
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Data Mata Pelajaran', href: '/admin/mata-pelajaran' },
    { title: 'Tambah Mata Pelajaran', href: '/admin/mata-pelajaran/create' },
];

const formState = reactive({
    nama:       '',
    pengajaran: [{ guru_id: '', tahun_ajaran_id: '' }] as Array<{ guru_id: string; tahun_ajaran_id: string }>,
});

const serverErrors = reactive<Record<string, string>>({});
const processing   = ref(false);

const refNama           = ref<HTMLElement | null>(null);
const refPengajaranRows = ref<HTMLElement[]>([]);

const localErrors = ref<Record<string, string>>({});
const setError    = (k: string, m: string) => { localErrors.value[k] = m; };
const clearErrors = () => { localErrors.value = {}; };
const scrollToEl  = (el: HTMLElement | null) => el?.scrollIntoView({ behavior: 'smooth', block: 'center' });

// ── Combobox Nama Mata Pelajaran ──────────────────────────────────
const showNamaDropdown = ref(false);
const namaSearch       = ref('');

const filteredNama = computed(() => {
    const q = (namaSearch.value || formState.nama).toLowerCase().trim();
    if (!q) return props.existingNamaList;
    return props.existingNamaList.filter(n => n.label.toLowerCase().includes(q));
});

const onNamaInput = () => {
    namaSearch.value      = formState.nama;
    showNamaDropdown.value = true;
    delete localErrors.value['nama'];
};
const onNamaFocus = () => {
    namaSearch.value      = formState.nama;
    showNamaDropdown.value = true;
};
const selectNama = (val: string) => {
    formState.nama        = val;
    namaSearch.value      = val;
    showNamaDropdown.value = false;
    delete localErrors.value['nama'];
};
const closeNamaDropdown = () => { showNamaDropdown.value = false; };

// ── Accordion pengajaran ──────────────────────────────────────────
const expandedRows = ref<number[]>([0]);
const toggleExpand = (i: number) => { const idx = expandedRows.value.indexOf(i); if (idx === -1) expandedRows.value.push(i); else expandedRows.value.splice(idx, 1); };
const isExpanded   = (i: number) => expandedRows.value.includes(i);

const accordionLabel = (index: number) => {
    const p = formState.pengajaran[index];
    if (!p.tahun_ajaran_id) return `Pengajaran ${index + 1}`;
    const tahun = props.tahunAjaran.find(t => t.id.toString() === p.tahun_ajaran_id)?.tahun ?? '';
    if (!p.guru_id) return tahun;
    const guru = props.guru.find(g => g.id.toString() === p.guru_id)?.nama ?? '';
    return `${tahun} — ${guru}`;
};

const addPengajaran = () => {
    formState.pengajaran.push({ guru_id: '', tahun_ajaran_id: '' });
    expandedRows.value.push(formState.pengajaran.length - 1);
};

const removePengajaran = (i: number) => {
    if (formState.pengajaran.length > 1) {
        formState.pengajaran.splice(i, 1);
        expandedRows.value = expandedRows.value.filter(n => n !== i).map(n => n > i ? n - 1 : n);
    } else {
        formState.pengajaran[0] = { guru_id: '', tahun_ajaran_id: '' };
    }
};

const onTahunChange = (index: number) => {
    const p = formState.pengajaran[index];
    if (p.guru_id && !getAvailableGuru(p.tahun_ajaran_id, index).some(g => g.id.toString() === p.guru_id)) {
        formState.pengajaran[index].guru_id = '';
    }
    delete localErrors.value[`pengajaran_${index}_tahun`];
};

const getAvailableGuru = (taId: string, currentIndex: number) => {
    if (!taId) return [];
    const id = parseInt(taId);
    return props.guru.filter(g =>
        g.tahun_ajaran_tersedia.includes(id) &&
        !formState.pengajaran.some((p, i) => i !== currentIndex && p.guru_id === g.id.toString() && p.tahun_ajaran_id === taId)
    );
};

const isDuplicate = (guruId: string, taId: string, idx: number) =>
    !!(guruId && taId && formState.pengajaran.some((p, i) => i !== idx && p.guru_id === guruId && p.tahun_ajaran_id === taId));

// ── Submit ────────────────────────────────────────────────────────
const handleSubmit = async () => {
    clearErrors();
    Object.keys(serverErrors).forEach(k => delete serverErrors[k]);

    let firstEl: HTMLElement | null = null;
    const trySet = (el: HTMLElement | null) => { if (!firstEl && el) firstEl = el; };

    if (!formState.nama.trim()) { setError('nama', 'Nama mata pelajaran wajib diisi'); trySet(refNama.value); }

    formState.pengajaran.forEach((p, i) => {
        const isEmpty = !p.guru_id && !p.tahun_ajaran_id;
        if (isEmpty) return;
        if (p.guru_id && !p.tahun_ajaran_id) { setError(`pengajaran_${i}_tahun`, 'Tahun ajaran wajib dipilih'); trySet(refPengajaranRows.value[i]); if (!isExpanded(i)) expandedRows.value.push(i); }
        if (!p.guru_id && p.tahun_ajaran_id) { setError(`pengajaran_${i}_guru`, 'Guru wajib dipilih'); trySet(refPengajaranRows.value[i]); if (!isExpanded(i)) expandedRows.value.push(i); }
        if (p.guru_id && p.tahun_ajaran_id && isDuplicate(p.guru_id, p.tahun_ajaran_id, i)) { setError(`pengajaran_${i}_dup`, 'Kombinasi guru & tahun ajaran sudah ada'); trySet(refPengajaranRows.value[i]); if (!isExpanded(i)) expandedRows.value.push(i); }
    });

    if (Object.keys(localErrors.value).length > 0) { await nextTick(); scrollToEl(firstEl); return; }

    const validPengajaran = formState.pengajaran.filter(p => p.guru_id && p.tahun_ajaran_id);

    const fd = new FormData();
    fd.append('nama', formState.nama);
    validPengajaran.forEach((p, i) => {
        fd.append(`pengajaran[${i}][guru_id]`,         p.guru_id);
        fd.append(`pengajaran[${i}][tahun_ajaran_id]`, p.tahun_ajaran_id);
    });

    processing.value = true;
    router.post('/admin/mata-pelajaran', fd, {
        preserveScroll: true,
        onError: (errs) => { Object.assign(serverErrors, errs); processing.value = false; },
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <Head title="Tambah Mata Pelajaran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Mata Pelajaran Baru</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Tambahkan mata pelajaran baru ke dalam kurikulum sekolah</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Mata Pelajaran</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lengkapi formulir di bawah untuk menambahkan mata pelajaran baru</p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="p-6">
                        <div class="space-y-8">

                            <!-- ══ NAMA — COMBOBOX ════════════════════════════ -->
                            <div ref="refNama" class="space-y-1.5 max-w-sm">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Mata Pelajaran <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="formState.nama"
                                        @input="onNamaInput"
                                        @focus="onNamaFocus"
                                        type="text"
                                        placeholder="Ketik atau pilih nama mata pelajaran..."
                                        autocomplete="off"
                                        :class="localErrors.nama || serverErrors.nama ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400" :class="showNamaDropdown ? 'rotate-180' : ''"></span>
                                    </div>

                                    <!-- Dropdown list -->
                                    <div v-if="showNamaDropdown && filteredNama.length > 0"
                                        class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                                        <div class="py-1 max-h-48 overflow-y-auto">
                                            <button
                                                v-for="n in filteredNama"
                                                :key="n.value"
                                                type="button"
                                                @mousedown.prevent="selectNama(n.value)"
                                                class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 transition-colors"
                                                :class="formState.nama === n.value ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : ''">
                                                {{ n.label }}
                                            </button>
                                        </div>
                                        <!-- Nama baru -->
                                        <div v-if="formState.nama && !filteredNama.find(n => n.value === formState.nama)"
                                            class="border-t border-gray-100 dark:border-gray-700 px-4 py-2.5">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Nama baru: <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ formState.nama }}"</span></p>
                                        </div>
                                    </div>

                                    <!-- Tidak ada hasil tapi user sudah ketik -->
                                    <div v-if="showNamaDropdown && filteredNama.length === 0 && formState.nama"
                                        class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 px-4 py-3">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Nama baru: <span class="font-semibold text-blue-600 dark:text-blue-400">"{{ formState.nama }}"</span></p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Ketik bebas atau pilih dari daftar yang ada</p>
                                <p v-if="localErrors.nama || serverErrors.nama" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ localErrors.nama || serverErrors.nama }}
                                </p>
                            </div>

                            <!-- ══ PENGAJARAN ══════════════════════════════════ -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between flex-wrap gap-3">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 dark:text-white">
                                            Pengajaran <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            Pilih tahun ajaran dulu — semua guru Aktif di tahun tersebut akan ditampilkan. Satu guru boleh mengampu lebih dari satu mata pelajaran.
                                        </p>
                                    </div>
                                    <button @click="addPengajaran" type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <div v-for="(p, index) in formState.pengajaran" :key="index"
                                        :ref="el => { if (el) refPengajaranRows[index] = el as HTMLElement }"
                                        class="rounded-xl border border-green-200 bg-white overflow-hidden dark:border-green-800 dark:bg-gray-800/50">

                                        <button type="button" @click="toggleExpand(index)"
                                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="h-6 w-6 flex-shrink-0 flex items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                                    {{ index + 1 }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ accordionLabel(index) }}</span>
                                                <span v-if="localErrors[`pengajaran_${index}_tahun`] || localErrors[`pengajaran_${index}_guru`] || localErrors[`pengajaran_${index}_dup`]"
                                                    class="flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Ada error
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                                <button @click.stop="removePengajaran(index)" type="button"
                                                    class="inline-flex items-center justify-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                                <span v-html="isExpanded(index) ? ChevronUpIcon() : ChevronDownIcon()" class="text-gray-400"></span>
                                            </div>
                                        </button>

                                        <div v-show="isExpanded(index)" class="border-t border-green-100 bg-green-50/50 px-4 py-4 dark:border-green-900 dark:bg-green-900/5">
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div class="space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran</label>
                                                    <div class="relative">
                                                        <select v-model="p.tahun_ajaran_id" @change="onTahunChange(index)"
                                                            :class="localErrors[`pengajaran_${index}_tahun`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                            <option value="">Pilih tahun ajaran</option>
                                                            <option v-for="t in tahunAjaran" :key="t.id" :value="t.id.toString()">{{ t.tahun }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                            <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                                        </div>
                                                    </div>
                                                    <p v-if="localErrors[`pengajaran_${index}_tahun`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                        <span v-html="ErrIcon()"></span>{{ localErrors[`pengajaran_${index}_tahun`] }}
                                                    </p>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Guru</label>
                                                    <div class="relative">
                                                        <select v-model="p.guru_id" :disabled="!p.tahun_ajaran_id"
                                                            @change="delete localErrors[`pengajaran_${index}_guru`]"
                                                            :class="localErrors[`pengajaran_${index}_guru`] || localErrors[`pengajaran_${index}_dup`] ? 'ring-red-400' : 'ring-gray-200'"
                                                            class="block w-full appearance-none rounded-lg border-0 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-900 ring-1 ring-inset focus:ring-2 focus:ring-green-600 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:disabled:bg-gray-800">
                                                            <option value="">{{ p.tahun_ajaran_id ? 'Pilih guru' : 'Pilih tahun ajaran dulu' }}</option>
                                                            <option v-for="g in getAvailableGuru(p.tahun_ajaran_id, index)" :key="g.id" :value="g.id.toString()">{{ g.nama }}</option>
                                                        </select>
                                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                            <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                                        </div>
                                                    </div>
                                                    <p v-if="p.tahun_ajaran_id" class="text-xs text-blue-600 dark:text-blue-400">
                                                        {{ getAvailableGuru(p.tahun_ajaran_id, index).length }} guru Aktif tersedia
                                                    </p>
                                                    <p v-if="localErrors[`pengajaran_${index}_guru`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                        <span v-html="ErrIcon()"></span>{{ localErrors[`pengajaran_${index}_guru`] }}
                                                    </p>
                                                    <p v-if="localErrors[`pengajaran_${index}_dup`]" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                                        <span v-html="ErrIcon()"></span>{{ localErrors[`pengajaran_${index}_dup`] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p v-if="serverErrors.pengajaran" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ serverErrors.pengajaran }}
                                </p>
                            </div>

                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:items-center">
                            <button type="submit" :disabled="processing"
                                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ processing ? 'Menyimpan...' : 'Simpan Mata Pelajaran' }}
                            </button>
                            <Link href="/admin/mata-pelajaran"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                <span v-html="ArrowLeftIcon()"></span>Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Overlay tutup dropdown saat klik luar -->
        <div v-if="showNamaDropdown" class="fixed inset-0 z-40" @click="closeNamaDropdown()"></div>
    </AppLayout>
</template>
