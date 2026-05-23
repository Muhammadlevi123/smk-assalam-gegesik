<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem } from '../../../types';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l7-3 7 3z" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;
const PhotoIcon       = () => `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const UserIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const ClockIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" /></svg>`;

import JadwalPicker from '@/components/JadwalPicker.vue';

interface Props {
    jenisList: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',       href: '/admin/dashboard' },
    { title: 'Data Organisasi', href: '/admin/organisasi' },
    { title: 'Tambah Organisasi', href: '/admin/organisasi/create' },
];

const form = useForm({
    nama:           '',
    jenis:          '',
    deskripsi:      '',
    pembina:        '',
    jadwal_latihan: '',
    logo:           null as File | null,
});

// ── Jenis input mode ──────────────────────────────────────────────
const inputMode = ref<'dropdown' | 'manual'>(props.jenisList.length > 0 ? 'dropdown' : 'manual');

const onDropdownChange = (e: Event) => {
    const val = (e.target as HTMLSelectElement).value;
    if (val === '__manual__') { form.jenis = ''; inputMode.value = 'manual'; }
    else { form.jenis = val; }
};

const backToDropdown = () => { form.jenis = ''; inputMode.value = 'dropdown'; };

// ── Logo upload ───────────────────────────────────────────────────
const logoPreview = ref<string | null>(null);

const onLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { logoPreview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
};

const removeLogo = () => {
    form.logo = null; logoPreview.value = null;
    const input = document.getElementById('logo-input') as HTMLInputElement;
    if (input) input.value = '';
};

const handleSubmit = () => {
    form.post(route('admin.organisasi.store'), { preserveScroll: true, forceFormData: true });
};
</script>

<template>
    <Head title="Tambah Organisasi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-2xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Tambah Organisasi</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Tambahkan data organisasi baru ke dalam sistem sekolah</p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Organisasi</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lengkapi formulir di bawah untuk menambahkan organisasi baru</p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="p-6 space-y-6">

                        <!-- Nama -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Organisasi <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.nama" type="text" placeholder="Contoh: OSIS, Pramuka, PMR..."
                                :class="form.errors.nama ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                            <p v-if="form.errors.nama" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.nama }}
                            </p>
                        </div>

                        <!-- Jenis -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jenis <span class="text-red-500">*</span>
                            </label>
                            <div v-if="inputMode === 'dropdown'" class="space-y-2">
                                <div class="relative">
                                    <select :value="form.jenis" @change="onDropdownChange"
                                        :class="form.errors.jenis ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                        <option value="">Pilih jenis organisasi</option>
                                        <option v-for="j in jenisList" :key="j" :value="j">{{ j }}</option>
                                        <option value="__manual__">+ Jenis baru (ketik sendiri)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <span v-html="ChevronDownIcon()" class="text-gray-400"></span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pilih dari jenis yang sudah ada, atau pilih "Jenis baru" untuk mengetik sendiri</p>
                            </div>
                            <div v-else class="space-y-2">
                                <div class="flex gap-2">
                                    <input v-model="form.jenis" type="text" placeholder="Ketik jenis organisasi baru..."
                                        :class="form.errors.jenis ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="flex-1 rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                    <button v-if="jenisList.length > 0" type="button" @click="backToDropdown"
                                        class="flex-shrink-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                                        ← Pilih dari daftar
                                    </button>
                                </div>
                            </div>
                            <p v-if="form.errors.jenis" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.jenis }}
                            </p>
                        </div>

                        <!-- Pembina & Jadwal Latihan -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                            <!-- Pembina -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pembina <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <span v-html="UserIcon()" class="text-gray-400"></span>
                                    </div>
                                    <input v-model="form.pembina" type="text" placeholder="Nama pembina / penanggung jawab"
                                        :class="form.errors.pembina ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                </div>
                                <p v-if="form.errors.pembina" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ form.errors.pembina }}
                                </p>
                            </div>

                            <!-- Jadwal Latihan -->
                            <div class="sm:col-span-2 space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Jadwal Latihan <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                                </label>
                                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                    <JadwalPicker
                                        v-model="form.jadwal_latihan"
                                        :has-error="!!form.errors.jadwal_latihan"
                                    />
                                </div>
                                <p v-if="form.errors.jadwal_latihan" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ form.errors.jadwal_latihan }}
                                </p>
                            </div>

                                                </div>

                        <!-- Deskripsi -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                            <textarea v-model="form.deskripsi" rows="4" placeholder="Deskripsi singkat tentang organisasi ini..."
                                :class="form.errors.deskripsi ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 resize-none dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700"></textarea>
                            <p v-if="form.errors.deskripsi" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.deskripsi }}
                            </p>
                        </div>

                        <!-- Logo Upload -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                            <div v-if="logoPreview" class="flex items-center gap-4">
                                <img :src="logoPreview" alt="Preview logo"
                                    class="h-20 w-20 rounded-xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                <div class="space-y-2">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ form.logo?.name }}</p>
                                    <div class="flex items-center gap-3">
                                        <label for="logo-input" class="text-xs font-medium text-blue-600 hover:text-blue-700 cursor-pointer dark:text-blue-400">Ganti logo</label>
                                        <span class="text-gray-300 dark:text-gray-600">|</span>
                                        <button type="button" @click="removeLogo" class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400">Hapus logo</button>
                                    </div>
                                </div>
                            </div>
                            <label v-else for="logo-input"
                                class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-8 cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700 dark:hover:bg-blue-900/10">
                                <span v-html="PhotoIcon()" class="text-gray-400 dark:text-gray-600"></span>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Klik untuk upload</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PNG, JPG, SVG maksimal 2MB</p>
                                </div>
                            </label>
                            <input id="logo-input" type="file" accept="image/jpeg,image/png,image/jpg,image/svg+xml" class="hidden" @change="onLogoChange" />
                            <p v-if="form.errors.logo" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.logo }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:items-center">
                            <button type="submit" :disabled="form.processing"
                                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Organisasi' }}
                            </button>
                            <Link :href="route('admin.organisasi.index')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                <span v-html="ArrowLeftIcon()"></span>Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
