<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;

interface Pendaftaran {
    id: number;
    nama_lengkap: string; jenis_kelamin: string; tempat_lahir: string;
    tanggal_lahir: string; nisn: string; nik: string; agama: string;
    anak_ke: number; no_kartu_keluarga: string; no_akte?: string;
    penerima_bantuan: string | string[]; nomor_kip?: string; no_hp: string;
    asal_sekolah: string; tahun_lulus: string; jurusan: string;
    nama_ayah: string; nik_ayah: string; pendidikan_ayah: string;
    tempat_lahir_ayah: string; tanggal_lahir_ayah?: string;
    pekerjaan_ayah: string; no_hp_ayah: string;
    nama_ibu: string; nik_ibu: string; pendidikan_ibu: string;
    tempat_lahir_ibu: string; tanggal_lahir_ibu?: string;
    pekerjaan_ibu: string; no_hp_ibu: string;
    jalan: string; dusun_blok: string; rt_rw: string; desa: string; kecamatan: string;
}

const props = defineProps<{ pendaftaran: Pendaftaran }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',        href: '/admin/dashboard' },
    { title: 'Data Pendaftaran', href: '/admin/pendaftaran' },
    { title: 'Detail',           href: `/admin/pendaftaran/${props.pendaftaran.id}` },
    { title: 'Edit',             href: `/admin/pendaftaran/${props.pendaftaran.id}/edit` },
];

const agamaList      = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
const bantuanList    = ['KIP', 'KPS/KKS/PKH', 'SKTM', 'Tidak Ada'];
const pendidikanList = ['SD/Sederajat', 'SMP/Sederajat', 'SMA/SMK/Sederajat', 'D1/D2/D3', 'S1/D4', 'S2', 'S3', 'Tidak Sekolah'];
const jurusanList    = [
    { value: 'TKRO', label: 'TEKNIK KENDARAAN RINGAN OTOMOTIF (TKRO)' },
    { value: 'TJKT', label: 'TEKNIK JARINGAN KOMPUTER DAN TELEKOMUNIKASI (TJKT)' },
];

// Parse penerima_bantuan — bisa string lama atau array baru
const parseBantuan = (val: string | string[]): string[] => {
    if (Array.isArray(val)) return val;
    try { const p = JSON.parse(val); return Array.isArray(p) ? p : [val]; }
    catch { return val ? [val] : []; }
};

const form = useForm({
    nama_lengkap:       props.pendaftaran.nama_lengkap,
    jenis_kelamin:      props.pendaftaran.jenis_kelamin,
    tempat_lahir:       props.pendaftaran.tempat_lahir,
    tanggal_lahir:      props.pendaftaran.tanggal_lahir?.split('T')[0] ?? '',
    nisn:               props.pendaftaran.nisn,
    agama:              props.pendaftaran.agama,
    anak_ke:            props.pendaftaran.anak_ke,
    no_kartu_keluarga:  props.pendaftaran.no_kartu_keluarga,
    nik:                props.pendaftaran.nik,
    no_akte:            props.pendaftaran.no_akte ?? '',
    penerima_bantuan:   parseBantuan(props.pendaftaran.penerima_bantuan) as string[],
    nomor_kip:          props.pendaftaran.nomor_kip ?? '',
    no_hp:              props.pendaftaran.no_hp,
    asal_sekolah:       props.pendaftaran.asal_sekolah,
    tahun_lulus:        props.pendaftaran.tahun_lulus,
    nama_ayah:          props.pendaftaran.nama_ayah,
    nik_ayah:           props.pendaftaran.nik_ayah,
    pendidikan_ayah:    props.pendaftaran.pendidikan_ayah,
    tempat_lahir_ayah:  props.pendaftaran.tempat_lahir_ayah,
    tanggal_lahir_ayah: props.pendaftaran.tanggal_lahir_ayah?.split('T')[0] ?? '',
    pekerjaan_ayah:     props.pendaftaran.pekerjaan_ayah,
    no_hp_ayah:         props.pendaftaran.no_hp_ayah,
    nama_ibu:           props.pendaftaran.nama_ibu,
    nik_ibu:            props.pendaftaran.nik_ibu,
    pendidikan_ibu:     props.pendaftaran.pendidikan_ibu,
    tempat_lahir_ibu:   props.pendaftaran.tempat_lahir_ibu,
    tanggal_lahir_ibu:  props.pendaftaran.tanggal_lahir_ibu?.split('T')[0] ?? '',
    pekerjaan_ibu:      props.pendaftaran.pekerjaan_ibu,
    no_hp_ibu:          props.pendaftaran.no_hp_ibu,
    jalan:              props.pendaftaran.jalan,
    dusun_blok:         props.pendaftaran.dusun_blok,
    rt_rw:              props.pendaftaran.rt_rw,
    desa:               props.pendaftaran.desa,
    kecamatan:          props.pendaftaran.kecamatan,
    jurusan:            props.pendaftaran.jurusan,
});

// Tampilkan KIP field hanya jika KIP dipilih
const showKIP = computed(() => (form.penerima_bantuan as string[]).includes('KIP'));
watch(showKIP, (val) => { if (!val) form.nomor_kip = ''; });

const submit = () => {
    form.put(route('admin.pendaftaran.update', props.pendaftaran.id));
};

const e = (field: string) => (form.errors as any)[field] || '';
</script>

<template>
    <Head :title="`Edit Pendaftar - ${pendaftaran.nama_lengkap}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Data Pendaftaran</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Perbarui informasi pendaftar "{{ pendaftaran.nama_lengkap }}"</p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    <!-- SECTION 1: Data Siswa -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Siswa</h3>
                        </div>
                        <div class="p-6 space-y-6">

                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input v-model="form.nama_lengkap" type="text"
                                    @input="form.nama_lengkap = form.nama_lengkap.toUpperCase()"
                                    :class="e('nama_lengkap') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                <p v-if="e('nama_lengkap')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nama_lengkap') }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <div class="flex gap-3">
                                        <label v-for="jk in ['Laki-laki','Perempuan']" :key="jk"
                                            class="flex flex-1 items-center gap-2 cursor-pointer rounded-xl border px-4 py-2.5 transition-all"
                                            :class="form.jenis_kelamin === jk ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800'">
                                            <input type="radio" v-model="form.jenis_kelamin" :value="jk" class="accent-blue-600" />
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ jk }}</span>
                                        </label>
                                    </div>
                                    <p v-if="e('jenis_kelamin')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('jenis_kelamin') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir <span class="text-red-500">*</span></label>
                                    <input v-model="form.tempat_lahir" type="text"
                                        :class="e('tempat_lahir') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('tempat_lahir')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('tempat_lahir') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input v-model="form.tanggal_lahir" type="date"
                                        :class="e('tanggal_lahir') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('tanggal_lahir')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('tanggal_lahir') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NISN <span class="text-red-500">*</span></label>
                                    <input v-model="form.nisn" type="text" placeholder="10 digit NISN"
                                        :class="e('nisn') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('nisn')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nisn') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agama <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select v-model="form.agama" :class="e('agama') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option value="">Pilih agama</option>
                                            <option v-for="a in agamaList" :key="a" :value="a">{{ a }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                    </div>
                                    <p v-if="e('agama')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('agama') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anak Ke- <span class="text-red-500">*</span></label>
                                    <input v-model="form.anak_ke" type="number" min="1" max="30"
                                        :class="e('anak_ke') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('anak_ke')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('anak_ke') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Kartu Keluarga <span class="text-red-500">*</span></label>
                                    <input v-model="form.no_kartu_keluarga" type="text"
                                        :class="e('no_kartu_keluarga') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('no_kartu_keluarga')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('no_kartu_keluarga') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK Siswa <span class="text-red-500">*</span></label>
                                    <input v-model="form.nik" type="text"
                                        :class="e('nik') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('nik')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nik') }}</p>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Akte Kelahiran <span class="text-red-500">*</span></label>
                                <input v-model="form.no_akte" type="text"
                                    :class="e('no_akte') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                <p v-if="e('no_akte')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('no_akte') }}</p>
                            </div>

                            <!-- Penerima Bantuan — CHECKBOX MULTIPLE -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penerima Bantuan <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-400">Boleh pilih lebih dari satu</p>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="b in bantuanList" :key="b"
                                        class="flex items-center gap-2 cursor-pointer rounded-xl border px-4 py-2.5 transition-all text-sm font-medium"
                                        :class="(form.penerima_bantuan as string[]).includes(b)
                                            ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'
                                            : 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'">
                                        <input type="checkbox" v-model="form.penerima_bantuan" :value="b" class="accent-blue-600" />
                                        {{ b }}
                                    </label>
                                </div>
                                <p v-if="e('penerima_bantuan')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('penerima_bantuan') }}</p>
                            </div>

                            <!-- Nomor KIP — muncul jika KIP dipilih -->
                            <div v-if="showKIP" class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor KIP <span class="text-red-500">*</span></label>
                                <input v-model="form.nomor_kip" type="text"
                                    :class="e('nomor_kip') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                <p v-if="e('nomor_kip')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nomor_kip') }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP Siswa <span class="text-red-500">*</span></label>
                                    <input v-model="form.no_hp" type="tel"
                                        :class="e('no_hp') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('no_hp')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('no_hp') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asal Sekolah <span class="text-red-500">*</span></label>
                                    <input v-model="form.asal_sekolah" type="text"
                                        :class="e('asal_sekolah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('asal_sekolah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('asal_sekolah') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Lulus <span class="text-red-500">*</span></label>
                                    <input v-model="form.tahun_lulus" type="text" maxlength="4" placeholder="2024"
                                        :class="e('tahun_lulus') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('tahun_lulus')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('tahun_lulus') }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan <span class="text-red-500">*</span></label>
                                <div class="flex flex-col gap-2">
                                    <label v-for="j in jurusanList" :key="j.value"
                                        class="flex items-start gap-3 cursor-pointer rounded-xl border px-4 py-3 transition-all"
                                        :class="form.jurusan === j.value ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800'">
                                        <input type="radio" v-model="form.jurusan" :value="j.value" class="accent-blue-600 mt-0.5" />
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ j.label }}</span>
                                    </label>
                                </div>
                                <p v-if="e('jurusan')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('jurusan') }}</p>
                            </div>

                        </div>
                    </div>

                    <!-- SECTION 2: Data Ayah -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-blue-100 bg-blue-50/50 px-6 py-4 dark:border-gray-800 dark:bg-blue-900/10">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Ayah</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Ayah <span class="text-red-500">*</span></label>
                                    <input v-model="form.nama_ayah" type="text" :class="e('nama_ayah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('nama_ayah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nama_ayah') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK Ayah <span class="text-red-500">*</span></label>
                                    <input v-model="form.nik_ayah" type="text" :class="e('nik_ayah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('nik_ayah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nik_ayah') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select v-model="form.pendidikan_ayah" :class="e('pendidikan_ayah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option value="">Pilih pendidikan</option>
                                            <option v-for="p in pendidikanList" :key="p" :value="p">{{ p }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                    </div>
                                    <p v-if="e('pendidikan_ayah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('pendidikan_ayah') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir <span class="text-red-500">*</span></label>
                                    <input v-model="form.tempat_lahir_ayah" type="text" :class="e('tempat_lahir_ayah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('tempat_lahir_ayah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('tempat_lahir_ayah') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                                    <input v-model="form.tanggal_lahir_ayah" type="date"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan <span class="text-red-500">*</span></label>
                                    <input v-model="form.pekerjaan_ayah" type="text" :class="e('pekerjaan_ayah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('pekerjaan_ayah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('pekerjaan_ayah') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP <span class="text-red-500">*</span></label>
                                    <input v-model="form.no_hp_ayah" type="tel" :class="e('no_hp_ayah') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('no_hp_ayah')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('no_hp_ayah') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Data Ibu -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-pink-100 bg-pink-50/50 px-6 py-4 dark:border-gray-800 dark:bg-pink-900/10">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Ibu</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Ibu <span class="text-red-500">*</span></label>
                                    <input v-model="form.nama_ibu" type="text" :class="e('nama_ibu') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('nama_ibu')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nama_ibu') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK Ibu <span class="text-red-500">*</span></label>
                                    <input v-model="form.nik_ibu" type="text" :class="e('nik_ibu') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('nik_ibu')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('nik_ibu') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select v-model="form.pendidikan_ibu" :class="e('pendidikan_ibu') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option value="">Pilih pendidikan</option>
                                            <option v-for="p in pendidikanList" :key="p" :value="p">{{ p }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                    </div>
                                    <p v-if="e('pendidikan_ibu')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('pendidikan_ibu') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir <span class="text-red-500">*</span></label>
                                    <input v-model="form.tempat_lahir_ibu" type="text" :class="e('tempat_lahir_ibu') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('tempat_lahir_ibu')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('tempat_lahir_ibu') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                                    <input v-model="form.tanggal_lahir_ibu" type="date"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:bg-white focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan <span class="text-red-500">*</span></label>
                                    <input v-model="form.pekerjaan_ibu" type="text" :class="e('pekerjaan_ibu') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('pekerjaan_ibu')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('pekerjaan_ibu') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP <span class="text-red-500">*</span></label>
                                    <input v-model="form.no_hp_ibu" type="tel" :class="e('no_hp_ibu') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('no_hp_ibu')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('no_hp_ibu') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: Alamat -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-green-100 bg-green-50/50 px-6 py-4 dark:border-gray-800 dark:bg-green-900/10">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Alamat Tempat Tinggal</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jalan <span class="text-red-500">*</span></label>
                                <input v-model="form.jalan" type="text" :class="e('jalan') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                <p v-if="e('jalan')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('jalan') }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dusun / Blok <span class="text-red-500">*</span></label>
                                    <input v-model="form.dusun_blok" type="text" :class="e('dusun_blok') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('dusun_blok')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('dusun_blok') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">RT / RW <span class="text-red-500">*</span></label>
                                    <input v-model="form.rt_rw" type="text" placeholder="001/002" :class="e('rt_rw') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('rt_rw')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('rt_rw') }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Desa <span class="text-red-500">*</span></label>
                                    <input v-model="form.desa" type="text" :class="e('desa') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                    <p v-if="e('desa')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('desa') }}</p>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kecamatan <span class="text-red-500">*</span></label>
                                <input v-model="form.kecamatan" type="text" :class="e('kecamatan') ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                    class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700" />
                                <p v-if="e('kecamatan')" class="text-xs text-red-500 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ e('kecamatan') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                        <button type="submit" :disabled="form.processing"
                            class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                            <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                        <Link :href="route('admin.pendaftaran.show', pendaftaran.id)"
                            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <span v-html="ArrowLeftIcon()"></span>
                            Batal
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AppLayout>
</template>
