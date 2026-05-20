<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, onUnmounted, computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const EditIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`;

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
    created_at: string; updated_at: string;
}

const props = defineProps<{ pendaftaran: Pendaftaran }>();
const page  = usePage();

// Parse penerima_bantuan — bisa string atau array
const penerimaBantuanList = computed((): string[] => {
    const val = props.pendaftaran.penerima_bantuan;
    if (Array.isArray(val)) return val;
    if (typeof val === 'string') {
        try { return JSON.parse(val); } catch { return [val]; }
    }
    return [];
});

const showSuccessUpdatePopup = ref(false);
let countdown: number | null = null;
const clearCountdown = () => { if (countdown) { clearTimeout(countdown); countdown = null; } };
const closeSuccessUpdatePopup = () => { showSuccessUpdatePopup.value = false; clearCountdown(); };

watch(() => (page.props as any).flash, (flash) => {
    if (flash?.success === 'updated') {
        showSuccessUpdatePopup.value = true;
        countdown = setTimeout(closeSuccessUpdatePopup, 1500);
    }
}, { immediate: true, deep: true });

onUnmounted(() => clearCountdown());

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',        href: '/admin/dashboard' },
    { title: 'Data Pendaftaran', href: '/admin/pendaftaran' },
    { title: 'Detail Pendaftar', href: `/admin/pendaftaran/${props.pendaftaran?.id}` },
];

const formatDate = (str?: string) => {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
const formatDatetime = (str?: string) => {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head :title="`Detail Pendaftar - ${pendaftaran.nama_lengkap}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Detail Pendaftar</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Informasi lengkap pendaftar "{{ pendaftaran.nama_lengkap }}"</p>
                    </div>
                    <Link :href="route('admin.pendaftaran.edit', pendaftaran.id)"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg sm:w-auto">
                        <span v-html="EditIcon()" class="transition-transform group-hover:scale-110"></span>
                        Edit Data
                    </Link>
                </div>

                <!-- SECTION 1: Data Siswa -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Siswa</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Informasi pribadi calon siswa</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nama Lengkap</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.nama_lengkap }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Jenis Kelamin</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.jenis_kelamin }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tempat, Tanggal Lahir</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.tempat_lahir }}, {{ formatDate(pendaftaran.tanggal_lahir) }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">NISN</p>
                                <p class="mt-1 text-sm font-semibold font-mono text-gray-900 dark:text-white">{{ pendaftaran.nisn }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">NIK Siswa</p>
                                <p class="mt-1 text-sm font-semibold font-mono text-gray-900 dark:text-white">{{ pendaftaran.nik }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Agama</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.agama }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Anak Ke-</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.anak_ke }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">No. Kartu Keluarga</p>
                                <p class="mt-1 text-sm font-semibold font-mono text-gray-900 dark:text-white">{{ pendaftaran.no_kartu_keluarga }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">No. Akte</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.no_akte || '-' }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Penerima Bantuan</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ penerimaBantuanList.join(', ') }}</p>
                            </div>

                            <div v-if="pendaftaran.nomor_kip" class="border-b border-gray-100 pb-4 dark:border-gray-800">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nomor KIP</p>
                                <p class="mt-1 text-sm font-semibold font-mono text-gray-900 dark:text-white">{{ pendaftaran.nomor_kip }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">No. HP Siswa</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.no_hp }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Asal Sekolah</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.asal_sekolah }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tahun Lulus</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.tahun_lulus }}</p>
                            </div>

                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Jurusan Dipilih</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.jurusan }}</p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Data Orang Tua -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <!-- Ayah -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-gray-100 bg-blue-50/50 px-6 py-4 dark:border-gray-800 dark:bg-blue-900/10">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Ayah</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60 sm:col-span-2">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nama Ayah</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.nama_ayah }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">NIK Ayah</p>
                                <p class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ pendaftaran.nik_ayah }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Pendidikan</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.pendidikan_ayah }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tempat, Tgl Lahir</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.tempat_lahir_ayah }}, {{ formatDate(pendaftaran.tanggal_lahir_ayah) }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Pekerjaan</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.pekerjaan_ayah }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60 sm:col-span-2">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">No. HP Ayah</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.no_hp_ayah }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ibu -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-gray-100 bg-pink-50/50 px-6 py-4 dark:border-gray-800 dark:bg-pink-900/10">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Data Ibu</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60 sm:col-span-2">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nama Ibu</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.nama_ibu }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">NIK Ibu</p>
                                <p class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ pendaftaran.nik_ibu }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Pendidikan</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.pendidikan_ibu }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tempat, Tgl Lahir</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.tempat_lahir_ibu }}, {{ formatDate(pendaftaran.tanggal_lahir_ibu) }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Pekerjaan</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.pekerjaan_ibu }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60 sm:col-span-2">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">No. HP Ibu</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.no_hp_ibu }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Alamat + Info Sistem -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <!-- Alamat -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-gray-100 bg-green-50/50 px-6 py-4 dark:border-gray-800 dark:bg-green-900/10">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Alamat Tempat Tinggal</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60 sm:col-span-2">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Jalan</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ pendaftaran.jalan }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Dusun / Blok</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.dusun_blok }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">RT / RW</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.rt_rw }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Desa</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.desa }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Kecamatan</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ pendaftaran.kecamatan }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Sistem -->
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Sistem</h3>
                        </div>
                        <div class="p-6 flex flex-col gap-5">
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tanggal Mendaftar</p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDatetime(pendaftaran.created_at) }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800/60">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Terakhir Diperbarui</p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ formatDatetime(pendaftaran.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pb-4">
                    <Link href="/admin/pendaftaran"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <span v-html="ArrowLeftIcon()"></span>
                        Kembali ke Daftar
                    </Link>
                </div>

            </div>
        </div>

        <!-- Success Popup -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSuccessUpdatePopup" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="closeSuccessUpdatePopup"></div>
                <div class="relative mx-4 pointer-events-auto">
                    <div class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 max-w-sm">
                        <button @click="closeSuccessUpdatePopup" class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <div class="flex items-center justify-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Berhasil Diperbarui!</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Perubahan data pendaftaran telah berhasil disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>
