<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, reactive, watch } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { type BreadcrumbItem } from '../../../types';
import RichTextEditor from '@/components/RichTextEditor.vue';

const ArrowLeftIcon   = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const SaveIcon        = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16l7-3 7 3z" /></svg>`;
const ChevronDownIcon = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>`;
const ErrIcon         = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;
const CalendarIcon    = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>`;
const ArticleIcon     = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>`;
const UserIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`;
const EditIcon        = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>`;
const XMarkIcon       = () => `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`;
const LockIcon        = () => `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>`;
const PhotoIcon       = () => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>`;

interface KategoriItem { value: string; label: string; }
interface PenulisItem  { value: string; label: string; }
interface StatusOption { value: string; label: string; }
interface Artikel {
    id:                 number;
    judul:              string;
    isi:                string;
    kategori:           string;
    penulis:            string;
    foto?:              string | null;
    images?:            string[];
    status:             'draft' | 'publish';
    tanggal_publikasi?: string | null;
}
interface Props {
    artikel:       Artikel;
    kategoriList:  KategoriItem[];
    penulisList:   PenulisItem[];
    statusOptions: StatusOption[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',    href: '/admin/dashboard' },
    { title: 'Data Artikel', href: '/admin/artikel' },
    { title: 'Edit Artikel', href: `/admin/artikel/${props.artikel.id}/edit` },
];

const today = new Date();
today.setHours(0, 0, 0, 0);
const toInputFormat = (date: Date): string => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};
const todayString = toInputFormat(today);

const parseTanggalAwal = (tanggal?: string | null): Date | null => {
    if (!tanggal) return null;
    const dateStr = tanggal.split('T')[0];
    const [year, month, day] = dateStr.split('-').map(Number);
    return new Date(year, month - 1, day);
};

const awalTanggal = props.artikel.tanggal_publikasi ? props.artikel.tanggal_publikasi.split('T')[0] : '';

const initTanggal = (): Date | null => {
    if (props.artikel.status === 'publish') return parseTanggalAwal(props.artikel.tanggal_publikasi) ?? new Date(today);
    return parseTanggalAwal(props.artikel.tanggal_publikasi);
};
const initTanggalString = (): string => {
    if (props.artikel.status === 'publish') return awalTanggal || todayString;
    return awalTanggal;
};

const formState = reactive({
    judul:             props.artikel.judul,
    isi:               props.artikel.isi,
    kategori:          props.artikel.kategori,
    penulis:           props.artikel.penulis,
    status:            props.artikel.status,
    tanggal_publikasi: initTanggalString(),
    foto:              null as File | null,
    remove_foto:       '0',
});

const serverErrors = reactive<Record<string, string>>({});
const localErrors  = ref<Record<string, string>>({});
const processing   = ref(false);
const setError     = (k: string, m: string) => { localErrors.value[k] = m; };
const clearErrors  = () => { localErrors.value = {}; };

watch(() => formState.status, (newStatus) => {
    if (newStatus === 'publish') { tanggalValue.value = new Date(today); formState.tanggal_publikasi = todayString; showCalendar.value = false; }
    else { tanggalValue.value = null; formState.tanggal_publikasi = ''; }
});

// ── Kategori combobox ─────────────────────────────────────────────
const showKategoriDropdown = ref(false);
const kategoriSearch       = ref('');
const filteredKategori = computed(() => {
    const q = (kategoriSearch.value || formState.kategori).toLowerCase().trim();
    if (!q) return props.kategoriList;
    return props.kategoriList.filter(k => k.label.toLowerCase().includes(q));
});
const onKategoriInput = () => { kategoriSearch.value = formState.kategori; showKategoriDropdown.value = true; delete localErrors.value['kategori']; };
const onKategoriFocus = () => { kategoriSearch.value = formState.kategori; showKategoriDropdown.value = true; };
const selectKategori  = (val: string) => { formState.kategori = val; kategoriSearch.value = val; showKategoriDropdown.value = false; delete localErrors.value['kategori']; };
const closeKategoriDD = () => { showKategoriDropdown.value = false; };

// ── Penulis combobox ──────────────────────────────────────────────
const showPenulisDropdown = ref(false);
const penulisSearch       = ref('');
const filteredPenulis = computed(() => {
    const q = (penulisSearch.value || formState.penulis).toLowerCase().trim();
    if (!q) return props.penulisList;
    return props.penulisList.filter(p => p.label.toLowerCase().includes(q));
});
const onPenulisInput = () => { penulisSearch.value = formState.penulis; showPenulisDropdown.value = true; delete localErrors.value['penulis']; };
const onPenulisFocus = () => { penulisSearch.value = formState.penulis; showPenulisDropdown.value = true; };
const selectPenulis  = (val: string) => { formState.penulis = val; penulisSearch.value = val; showPenulisDropdown.value = false; delete localErrors.value['penulis']; };
const closePenulisDD = () => { showPenulisDropdown.value = false; };

// ── Foto utama ────────────────────────────────────────────────────
const imagePreview   = ref<string | null>(null);
const fotoRemoved    = ref(false);
const fileInputRef   = ref<HTMLInputElement | null>(null);
const currentFotoUrl = computed(() => props.artikel.foto ? `/storage/${props.artikel.foto}` : null);
const displayedFoto  = computed(() => { if (imagePreview.value) return imagePreview.value; if (!fotoRemoved.value && currentFotoUrl.value) return currentFotoUrl.value; return null; });

const onFotoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { setError('foto', 'Ukuran file maksimal 2MB.'); return; }
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { setError('foto', 'Format tidak didukung.'); return; }
    delete localErrors.value['foto'];
    formState.foto = file; formState.remove_foto = '0'; fotoRemoved.value = false;
    const reader = new FileReader();
    reader.onload = e => { imagePreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
};
const removeFoto = () => { formState.foto = null; formState.remove_foto = '1'; imagePreview.value = null; fotoRemoved.value = true; if (fileInputRef.value) fileInputRef.value.value = ''; };

// ── Foto tambahan existing ────────────────────────────────────────
const keepExisting = ref<string[]>([...(props.artikel.images ?? [])]);
const removeExistingImage = (path: string) => { keepExisting.value = keepExisting.value.filter(p => p !== path); };

// ── Foto tambahan baru ────────────────────────────────────────────
interface NewImageItem { file: File; preview: string; }
const newImageItems  = ref<NewImageItem[]>([]);
const imagesInputRef = ref<HTMLInputElement | null>(null);
const MAX_IMAGES     = 10;
const totalImages    = computed(() => keepExisting.value.length + newImageItems.value.length);

const onImagesChange = (e: Event) => {
    const files = Array.from((e.target as HTMLInputElement).files ?? []);
    if (!files.length) return;
    const remaining = MAX_IMAGES - totalImages.value;
    if (remaining <= 0) { setError('images', `Maksimal ${MAX_IMAGES} foto tambahan.`); return; }
    const toProcess = files.slice(0, remaining);
    delete localErrors.value['images'];
    toProcess.forEach(file => {
        if (file.size > 2 * 1024 * 1024) { setError('images', `"${file.name}" melebihi 2MB.`); return; }
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { setError('images', `"${file.name}" format tidak didukung.`); return; }
        const reader = new FileReader();
        reader.onload = ev => { newImageItems.value.push({ file, preview: ev.target?.result as string }); };
        reader.readAsDataURL(file);
    });
    if (imagesInputRef.value) imagesInputRef.value.value = '';
};
const removeNewImageItem = (index: number) => { newImageItems.value.splice(index, 1); delete localErrors.value['images']; };

// ── Tanggal Publikasi ─────────────────────────────────────────────
const tanggalValue = ref<Date | null>(initTanggal());
const showCalendar = ref(false);
const formatDisplay = (date: Date | null): string => { if (!date) return ''; return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); };
const onSelectTanggal = (day: any) => { const picked = new Date(day.date); picked.setHours(0, 0, 0, 0); if (picked < today) return; tanggalValue.value = day.date; formState.tanggal_publikasi = toInputFormat(day.date); showCalendar.value = false; delete localErrors.value['tanggal_publikasi']; };
const clearTanggal  = () => { if (formState.status === 'publish') return; tanggalValue.value = null; formState.tanggal_publikasi = ''; };
const closeCalendar = () => { showCalendar.value = false; };

const infoStatusTanggal = computed(() => {
    if (formState.status === 'publish') return { type: 'success', msg: `Akan dipublikasi langsung hari ini (${formatDisplay(today)})` };
    if (tanggalValue.value) return { type: 'info', msg: `Disimpan sebagai draft, dijadwalkan publish pada ${formatDisplay(tanggalValue.value)} via scheduler` };
    return { type: 'info', msg: 'Disimpan sebagai draft — tidak ada jadwal publish' };
});

// ── hasChanges ────────────────────────────────────────────────────
const hasChanges = computed(() => {
    const origImages = props.artikel.images ?? [];
    const imagesChanged = keepExisting.value.length !== origImages.length || keepExisting.value.some(p => !origImages.includes(p)) || newImageItems.value.length > 0;
    return (
        formState.judul             !== props.artikel.judul    ||
        formState.isi               !== props.artikel.isi      ||
        formState.kategori          !== props.artikel.kategori ||
        formState.penulis           !== props.artikel.penulis  ||
        formState.status            !== props.artikel.status   ||
        formState.tanggal_publikasi !== initTanggalString()    ||
        formState.foto !== null || fotoRemoved.value || imagesChanged
    );
});

const closeAll = () => { closeCalendar(); closeKategoriDD(); closePenulisDD(); };

const handleSubmit = () => {
    clearErrors();
    let valid = true;
    if (!formState.judul.trim())    { setError('judul',    'Judul artikel wajib diisi'); valid = false; }
    if (!formState.isi.trim())      { setError('isi',      'Isi artikel wajib diisi'); valid = false; }
    if (!formState.kategori.trim()) { setError('kategori', 'Kategori wajib diisi'); valid = false; }
    if (!formState.penulis.trim())  { setError('penulis',  'Penulis wajib diisi'); valid = false; }
    if (!formState.status)          { setError('status',   'Status wajib dipilih'); valid = false; }
    if (!valid) return;

    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('judul', formState.judul);
    fd.append('isi', formState.isi);
    fd.append('kategori', formState.kategori);
    fd.append('penulis', formState.penulis);
    fd.append('status', formState.status);
    fd.append('tanggal_publikasi', formState.tanggal_publikasi || '');
    fd.append('remove_foto', formState.remove_foto);
    if (formState.foto instanceof File) fd.append('foto', formState.foto);
    keepExisting.value.forEach(path => fd.append('existing_images[]', path));
    newImageItems.value.forEach(item => fd.append('images[]', item.file));

    processing.value = true;
    router.post(`/admin/artikel/${props.artikel.id}`, fd, {
        preserveScroll: true,
        onError: (errs) => { Object.keys(serverErrors).forEach(k => delete serverErrors[k]); Object.assign(serverErrors, errs); processing.value = false; },
        onFinish: () => { processing.value = false; },
    });
};
</script>

<template>
    <Head :title="`Edit Artikel - ${artikel.judul}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Artikel</h1>
                    <p class="text-base text-gray-600 dark:text-gray-400">Perbarui informasi artikel "{{ artikel.judul }}"</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2"><span v-html="EditIcon()" class="text-blue-600 dark:text-blue-400"></span><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Informasi Artikel</h3></div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui formulir di bawah untuk mengubah data artikel</p>
                    </div>
                    <form @submit.prevent="handleSubmit" class="p-6">
                        <div class="space-y-8">

                            <!-- Info artikel saat ini -->
                            <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-900/10">
                                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Data Artikel Saat Ini</h4>
                                <div class="flex items-center gap-4">
                                    <div v-if="currentFotoUrl && !imagePreview" class="flex-shrink-0"><img :src="currentFotoUrl" :alt="artikel.judul" class="h-16 w-24 rounded-lg object-cover border border-blue-200 dark:border-blue-800" /></div>
                                    <div class="min-w-0 flex-1 text-sm text-blue-700 dark:text-blue-400 space-y-0.5">
                                        <p><span class="font-medium">Judul:</span> {{ artikel.judul }}</p>
                                        <p><span class="font-medium">Kategori:</span> {{ artikel.kategori }}</p>
                                        <p><span class="font-medium">Penulis:</span> {{ artikel.penulis }}</p>
                                        <p><span class="font-medium">Status:</span> <span :class="artikel.status === 'publish' ? 'text-green-700 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'" class="ml-1 font-semibold">{{ artikel.status === 'publish' ? 'Publish' : 'Draft' }}</span></p>
                                        <p v-if="(artikel.images ?? []).length > 0"><span class="font-medium">Foto tambahan:</span> {{ (artikel.images ?? []).length }} foto</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <!-- Judul -->
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Artikel <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none"><span v-html="ArticleIcon()" class="text-gray-400"></span></div>
                                        <input v-model="formState.judul" @input="delete localErrors['judul']" type="text" placeholder="Masukkan judul artikel"
                                            :class="localErrors.judul || serverErrors.judul ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                    </div>
                                    <p v-if="localErrors.judul || serverErrors.judul" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.judul || serverErrors.judul }}</p>
                                </div>

                                <!-- Kategori -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input v-model="formState.kategori" @input="onKategoriInput" @focus="onKategoriFocus" type="text" placeholder="Ketik atau pilih kategori..." autocomplete="off"
                                            :class="localErrors.kategori || serverErrors.kategori ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400" :class="showKategoriDropdown ? 'rotate-180' : ''"></span></div>
                                        <div v-if="showKategoriDropdown && filteredKategori.length > 0" class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                                            <div class="py-1 max-h-48 overflow-y-auto">
                                                <button v-for="k in filteredKategori" :key="k.value" type="button" @mousedown.prevent="selectKategori(k.value)"
                                                    class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 transition-colors"
                                                    :class="formState.kategori === k.value ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : ''">{{ k.label }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Ketik bebas atau pilih dari daftar yang ada</p>
                                    <p v-if="localErrors.kategori || serverErrors.kategori" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.kategori || serverErrors.kategori }}</p>
                                </div>

                                <!-- Penulis -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penulis <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none"><span v-html="UserIcon()" class="text-gray-400"></span></div>
                                        <input v-model="formState.penulis" @input="onPenulisInput" @focus="onPenulisFocus" type="text" placeholder="Ketik atau pilih nama penulis..." autocomplete="off"
                                            :class="localErrors.penulis || serverErrors.penulis ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-10 pr-10 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400" :class="showPenulisDropdown ? 'rotate-180' : ''"></span></div>
                                        <div v-if="showPenulisDropdown && filteredPenulis.length > 0" class="absolute left-0 top-full z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
                                            <div class="py-1 max-h-48 overflow-y-auto">
                                                <button v-for="p in filteredPenulis" :key="p.value" type="button" @mousedown.prevent="selectPenulis(p.value)"
                                                    class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 transition-colors"
                                                    :class="formState.penulis === p.value ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : ''">{{ p.label }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Ketik bebas atau pilih dari daftar yang ada</p>
                                    <p v-if="localErrors.penulis || serverErrors.penulis" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.penulis || serverErrors.penulis }}</p>
                                </div>

                                <!-- Status -->
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select v-model="formState.status" @change="delete localErrors['status']"
                                            :class="localErrors.status || serverErrors.status ? 'ring-red-400' : 'ring-gray-200 focus:ring-blue-600'"
                                            class="block w-full appearance-none rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-10 text-sm text-gray-900 ring-1 ring-inset focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:bg-gray-700">
                                            <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none"><span v-html="ChevronDownIcon()" class="text-gray-400"></span></div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Draft = jadwalkan tanggal terbit · Publish = tayang hari ini</p>
                                    <p v-if="localErrors.status || serverErrors.status" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.status || serverErrors.status }}</p>
                                </div>

                                <!-- Tanggal Publikasi -->
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Publikasi
                                        <span v-if="formState.status === 'publish'" class="text-xs font-normal text-gray-400 ml-1">(otomatis hari ini, tidak dapat diubah)</span>
                                        <span v-else class="text-xs font-normal text-gray-400 ml-1">(opsional — hanya tanggal hari ini atau mendatang)</span>
                                    </label>
                                    <div v-if="formState.status === 'publish'" class="flex items-center gap-3 rounded-xl border-0 bg-gray-100 py-3 px-4 ring-1 ring-inset ring-gray-200 cursor-not-allowed dark:bg-gray-800 dark:ring-gray-700">
                                        <span v-html="CalendarIcon()" class="text-gray-400 flex-shrink-0"></span>
                                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-white">{{ formatDisplay(today) }}</span>
                                        <span v-html="LockIcon()" class="text-gray-400 flex-shrink-0"></span>
                                    </div>
                                    <div v-else class="flex items-center gap-2">
                                        <div class="relative flex-1">
                                            <button type="button" @click="showCalendar = !showCalendar; closeKategoriDD(); closePenulisDD()"
                                                class="flex w-full items-center gap-3 rounded-xl border-0 bg-gray-50 py-3 px-4 text-left ring-1 ring-inset ring-gray-200 transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 dark:bg-gray-800 dark:ring-gray-700 dark:focus:bg-gray-700">
                                                <span v-html="CalendarIcon()" class="text-gray-400 flex-shrink-0"></span>
                                                <span class="flex-1 text-sm" :class="tanggalValue ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400 dark:text-gray-500'">{{ tanggalValue ? formatDisplay(tanggalValue) : 'Pilih tanggal publikasi (opsional)' }}</span>
                                                <span v-html="ChevronDownIcon()" class="text-gray-400 flex-shrink-0 transition-transform" :class="showCalendar ? 'rotate-180' : ''"></span>
                                            </button>
                                            <div v-if="showCalendar" class="absolute left-0 top-full z-50 mt-2 rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
                                                <DatePicker v-model="tanggalValue" @dayclick="onSelectTanggal" color="blue" is-expanded :min-date="today" class="rounded-2xl" />
                                            </div>
                                        </div>
                                        <button v-if="tanggalValue" type="button" @click="clearTanggal" class="flex-shrink-0 rounded-xl bg-gray-100 p-3 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400 transition-colors"><span v-html="XMarkIcon()"></span></button>
                                    </div>
                                    <div :class="{ 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800': infoStatusTanggal.type === 'success', 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800': infoStatusTanggal.type === 'info' }"
                                        class="flex items-center gap-2 rounded-lg border px-3 py-2">
                                        <svg v-if="infoStatusTanggal.type === 'success'" class="h-4 w-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <svg v-else class="h-4 w-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <p class="text-xs" :class="{ 'text-green-700 dark:text-green-300': infoStatusTanggal.type === 'success', 'text-blue-700 dark:text-blue-300': infoStatusTanggal.type === 'info' }">{{ infoStatusTanggal.msg }}</p>
                                    </div>
                                </div>

                                <!-- Isi Artikel (RichTextEditor) -->
                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Isi Artikel <span class="text-red-500">*</span></label>
                                    <RichTextEditor
                                        v-model="formState.isi"
                                        :has-error="!!(localErrors.isi || serverErrors.isi)"
                                        placeholder="Tulis isi artikel dengan lengkap dan jelas..."
                                        @update:modelValue="delete localErrors['isi']"
                                    />
                                    <p v-if="localErrors.isi || serverErrors.isi" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.isi || serverErrors.isi }}</p>
                                </div>
                            </div>

                            <!-- Foto Utama -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Utama <span class="text-xs font-normal text-gray-400 ml-1">(digunakan sebagai cover/thumbnail)</span></label>
                                <div v-if="displayedFoto" class="flex items-center gap-4">
                                    <img :src="displayedFoto" alt="Foto artikel" class="h-20 w-32 rounded-xl border border-gray-200 bg-gray-50 object-cover ring-1 ring-black/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/10" />
                                    <div class="space-y-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formState.foto?.name ?? 'Foto saat ini' }}</p>
                                        <div class="flex items-center gap-3">
                                            <label for="foto-input" class="text-xs font-medium text-blue-600 hover:text-blue-700 cursor-pointer dark:text-blue-400 dark:hover:text-blue-300">Ganti foto</label>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button type="button" @click="removeFoto" class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                                <label v-else for="foto-input" class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-8 cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700 dark:hover:bg-blue-900/10">
                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    <div class="text-center"><p class="text-sm font-medium text-blue-600 dark:text-blue-400">Klik untuk upload foto utama</p><p class="text-xs text-gray-500 dark:text-gray-400 mt-1">JPG, JPEG, PNG · Maks. 2MB</p></div>
                                </label>
                                <input id="foto-input" ref="fileInputRef" type="file" accept="image/jpeg,image/jpg,image/png" class="hidden" @change="onFotoChange" />
                                <p v-if="localErrors.foto || serverErrors.foto" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.foto || serverErrors.foto }}</p>
                            </div>

                            <!-- Foto Tambahan -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Tambahan <span class="text-xs font-normal text-gray-400 ml-1">(tampil sebagai galeri di detail artikel)</span></label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ totalImages }}/{{ MAX_IMAGES }} foto · JPG, JPEG, PNG · Maks. 2MB per foto</p>
                                    </div>
                                    <label v-if="totalImages < MAX_IMAGES" for="images-input" class="inline-flex items-center gap-2 cursor-pointer rounded-xl bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"><span v-html="PhotoIcon()"></span>Tambah Foto</label>
                                </div>
                                <div v-if="totalImages > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    <!-- Foto lama -->
                                    <div v-for="path in keepExisting" :key="path" class="group relative rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 aspect-square">
                                        <img :src="`/storage/${path}`" alt="Foto existing" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center">
                                            <button type="button" @click="removeExistingImage(path)" class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg"><span v-html="XMarkIcon()"></span></button>
                                        </div>
                                        <div class="absolute top-1.5 left-1.5 bg-green-600/80 text-white text-xs font-bold rounded-md px-1.5 py-0.5">Tersimpan</div>
                                    </div>
                                    <!-- Foto baru -->
                                    <div v-for="(item, index) in newImageItems" :key="`new-${index}`" class="group relative rounded-xl overflow-hidden border border-blue-200 dark:border-blue-700 bg-gray-50 dark:bg-gray-800 aspect-square">
                                        <img :src="item.preview" :alt="`Foto baru ${index + 1}`" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center">
                                            <button type="button" @click="removeNewImageItem(index)" class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg"><span v-html="XMarkIcon()"></span></button>
                                        </div>
                                        <div class="absolute top-1.5 left-1.5 bg-blue-600/80 text-white text-xs font-bold rounded-md px-1.5 py-0.5">Baru</div>
                                    </div>
                                    <!-- Tambah inline -->
                                    <label v-if="totalImages < MAX_IMAGES" for="images-input" class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 aspect-square cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 dark:hover:border-blue-700 dark:hover:bg-blue-900/10 transition-colors">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Tambah</span>
                                    </label>
                                </div>
                                <label v-else for="images-input" class="flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-8 cursor-pointer hover:border-blue-300 hover:bg-blue-50/30 transition-colors dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700 dark:hover:bg-blue-900/10">
                                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" /></svg>
                                    <div class="text-center"><p class="text-sm font-medium text-blue-600 dark:text-blue-400">Klik untuk upload foto tambahan</p><p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bisa pilih beberapa foto sekaligus · Maks. {{ MAX_IMAGES }} foto</p></div>
                                </label>
                                <input id="images-input" ref="imagesInputRef" type="file" accept="image/jpeg,image/jpg,image/png" multiple class="hidden" @change="onImagesChange" />
                                <p v-if="localErrors.images || serverErrors['images.0']" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1"><span v-html="ErrIcon()"></span>{{ localErrors.images || serverErrors['images.0'] }}</p>
                            </div>

                            <!-- Indikator perubahan -->
                            <div v-if="hasChanges" class="rounded-xl bg-blue-50 p-4 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800">
                                <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div><span class="text-sm text-blue-700 dark:text-blue-300">Ada perubahan yang belum disimpan</span></div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:items-center">
                            <button type="submit" :disabled="processing || !hasChanges"
                                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ processing ? 'Memperbarui Artikel...' : 'Perbarui Artikel' }}
                            </button>
                            <Link href="/admin/artikel" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                <span v-html="ArrowLeftIcon()"></span>Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showCalendar || showKategoriDropdown || showPenulisDropdown" class="fixed inset-0 z-40" @click="closeAll()"></div>
    </AppLayout>
</template>
