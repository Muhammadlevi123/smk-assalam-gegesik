<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';

const currentStep = ref(1);

const form = useForm({
    nama_lengkap:       '',
    jenis_kelamin:      '',
    tempat_lahir:       '',
    tanggal_lahir:      '',
    nisn:               '',
    agama:              '',
    anak_ke:            '',
    no_kartu_keluarga:  '',
    nik:                '',
    no_akte:            '',
    penerima_bantuan:   [] as string[],
    nomor_kip:          '',
    no_hp:              '',
    asal_sekolah:       '',
    tahun_lulus:        '',
    nama_ayah:          '',
    nik_ayah:           '',
    pendidikan_ayah:    '',
    tempat_lahir_ayah:  '',
    tanggal_lahir_ayah: '',
    pekerjaan_ayah:     '',
    no_hp_ayah:         '',
    nama_ibu:           '',
    nik_ibu:            '',
    pendidikan_ibu:     '',
    tempat_lahir_ibu:   '',
    tanggal_lahir_ibu:  '',
    pekerjaan_ibu:      '',
    no_hp_ibu:          '',
    jalan:              '',
    dusun_blok:         '',
    rt_rw:              '',
    desa:               '',
    kecamatan:          '',
    jurusan:            '',
});

// ── Date picker state ──────────────────────────────────────────────
const tglLahir        = ref<Date | null>(null);
const tglLahirAyah   = ref<Date | null>(null);
const tglLahirIbu    = ref<Date | null>(null);

const showCalTglLahir      = ref(false);
const showCalTglLahirAyah  = ref(false);
const showCalTglLahirIbu   = ref(false);

const formatDisplay = (date: Date | null): string => {
    if (!date) return '';
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const toInputFormat = (date: Date): string => date.toISOString().split('T')[0];

const onSelectTglLahir = (day: any) => {
    tglLahir.value          = day.date;
    form.tanggal_lahir      = toInputFormat(day.date);
    showCalTglLahir.value   = false;
};
const onSelectTglLahirAyah = (day: any) => {
    tglLahirAyah.value         = day.date;
    form.tanggal_lahir_ayah    = toInputFormat(day.date);
    showCalTglLahirAyah.value  = false;
};
const onSelectTglLahirIbu = (day: any) => {
    tglLahirIbu.value          = day.date;
    form.tanggal_lahir_ibu     = toInputFormat(day.date);
    showCalTglLahirIbu.value   = false;
};

const closeAllCal = () => {
    showCalTglLahir.value     = false;
    showCalTglLahirAyah.value = false;
    showCalTglLahirIbu.value  = false;
};

// ── Validasi step 1 ───────────────────────────────────────────────
const step1Errors = ref<Record<string, string>>({});

const validateStep1 = (): boolean => {
    step1Errors.value = {};

    if (!form.nama_lengkap) {
        step1Errors.value['nama_lengkap'] = 'Nama lengkap wajib diisi.';
    } else if (form.nama_lengkap.length > 255) {
        step1Errors.value['nama_lengkap'] = 'Nama lengkap maksimal 255 karakter.';
    }

    if (!form.jenis_kelamin) {
        step1Errors.value['jenis_kelamin'] = 'Jenis kelamin wajib dipilih.';
    } else if (!['Laki-laki','Perempuan'].includes(form.jenis_kelamin)) {
        step1Errors.value['jenis_kelamin'] = 'Jenis kelamin tidak valid.';
    }

    if (!form.tempat_lahir) {
        step1Errors.value['tempat_lahir'] = 'Tempat lahir wajib diisi.';
    } else if (form.tempat_lahir.length > 100) {
        step1Errors.value['tempat_lahir'] = 'Tempat lahir maksimal 100 karakter.';
    }

    if (!form.tanggal_lahir) {
        step1Errors.value['tanggal_lahir'] = 'Tanggal lahir wajib diisi.';
    }

    if (!form.nisn) {
        step1Errors.value['nisn'] = 'NISN wajib diisi.';
    } else if (form.nisn.length > 20) {
        step1Errors.value['nisn'] = 'NISN maksimal 20 karakter.';
    }

    if (!form.agama) {
        step1Errors.value['agama'] = 'Agama wajib dipilih.';
    }

    if (!form.anak_ke) {
        step1Errors.value['anak_ke'] = 'Anak ke- wajib diisi.';
    } else if (parseInt(String(form.anak_ke)) < 1 || parseInt(String(form.anak_ke)) > 30) {
        step1Errors.value['anak_ke'] = 'Anak ke- harus antara 1 - 30.';
    }

    if (!form.no_kartu_keluarga) {
        step1Errors.value['no_kartu_keluarga'] = 'No. Kartu Keluarga wajib diisi.';
    } else if (form.no_kartu_keluarga.length > 30) {
        step1Errors.value['no_kartu_keluarga'] = 'No. KK maksimal 30 karakter.';
    }

    if (!form.nik) {
        step1Errors.value['nik'] = 'NIK wajib diisi.';
    } else if (form.nik.length > 20) {
        step1Errors.value['nik'] = 'NIK maksimal 20 karakter.';
    }

    if (!form.no_akte) {
        step1Errors.value['no_akte'] = 'No. Akte Kelahiran wajib diisi.';
    } else if (form.no_akte.length > 100) {
        step1Errors.value['no_akte'] = 'No. Akte maksimal 100 karakter.';
    }

    if ((form.penerima_bantuan as string[]).length === 0) {
        step1Errors.value['penerima_bantuan'] = 'Penerima bantuan wajib dipilih minimal satu.';
    }

    if ((form.penerima_bantuan as string[]).includes('KIP')) {
        if (!form.nomor_kip) {
            step1Errors.value['nomor_kip'] = 'Nomor KIP wajib diisi.';
        } else if (form.nomor_kip.length > 50) {
            step1Errors.value['nomor_kip'] = 'Nomor KIP maksimal 50 karakter.';
        }
    }

    if (!form.no_hp) {
        step1Errors.value['no_hp'] = 'No. HP wajib diisi.';
    } else if (form.no_hp.length > 20) {
        step1Errors.value['no_hp'] = 'No. HP maksimal 20 karakter.';
    }

    if (!form.asal_sekolah) {
        step1Errors.value['asal_sekolah'] = 'Asal sekolah wajib diisi.';
    } else if (form.asal_sekolah.length > 255) {
        step1Errors.value['asal_sekolah'] = 'Asal sekolah maksimal 255 karakter.';
    }

    if (!form.tahun_lulus) {
        step1Errors.value['tahun_lulus'] = 'Tahun lulus wajib diisi.';
    } else if (!/^\d{4}$/.test(form.tahun_lulus)) {
        step1Errors.value['tahun_lulus'] = 'Tahun lulus harus 4 digit angka.';
    } else if (parseInt(form.tahun_lulus) < 2000 || parseInt(form.tahun_lulus) > 2099) {
        step1Errors.value['tahun_lulus'] = 'Tahun lulus harus antara 2000 - 2099.';
    }

    return Object.keys(step1Errors.value).length === 0;
};

const scrollToFirstError = () => {
    setTimeout(() => {
        const errEl = document.querySelector('.doc-input-err, .cal-btn-err, .doc-err');
        if (errEl) {
            const field = errEl.closest('.doc-field') || errEl;
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 50);
};

const handleNext = () => {
    if (validateStep1()) {
        currentStep.value = 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        scrollToFirstError();
    }
};

const goPrev = () => {
    currentStep.value = 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const step2Errors = ref<Record<string, string>>({});

const validateStep2 = (): boolean => {
    step2Errors.value = {};

    if (!form.nama_ayah) step2Errors.value['nama_ayah'] = 'Nama ayah wajib diisi.';
    else if (form.nama_ayah.length > 255) step2Errors.value['nama_ayah'] = 'Nama ayah maksimal 255 karakter.';

    if (!form.nik_ayah) step2Errors.value['nik_ayah'] = 'NIK ayah wajib diisi.';
    else if (form.nik_ayah.length > 20) step2Errors.value['nik_ayah'] = 'NIK ayah maksimal 20 karakter.';

    if (!form.pendidikan_ayah) step2Errors.value['pendidikan_ayah'] = 'Pendidikan ayah wajib dipilih.';
    else if (form.pendidikan_ayah.length > 50) step2Errors.value['pendidikan_ayah'] = 'Pendidikan ayah maksimal 50 karakter.';

    if (!form.tempat_lahir_ayah) step2Errors.value['tempat_lahir_ayah'] = 'Tempat lahir ayah wajib diisi.';
    else if (form.tempat_lahir_ayah.length > 100) step2Errors.value['tempat_lahir_ayah'] = 'Tempat lahir ayah maksimal 100 karakter.';

    if (!form.pekerjaan_ayah) step2Errors.value['pekerjaan_ayah'] = 'Pekerjaan ayah wajib diisi.';
    else if (form.pekerjaan_ayah.length > 100) step2Errors.value['pekerjaan_ayah'] = 'Pekerjaan ayah maksimal 100 karakter.';

    if (!form.no_hp_ayah) step2Errors.value['no_hp_ayah'] = 'No. HP ayah wajib diisi.';
    else if (form.no_hp_ayah.length > 20) step2Errors.value['no_hp_ayah'] = 'No. HP ayah maksimal 20 karakter.';

    if (!form.nama_ibu) step2Errors.value['nama_ibu'] = 'Nama ibu wajib diisi.';
    else if (form.nama_ibu.length > 255) step2Errors.value['nama_ibu'] = 'Nama ibu maksimal 255 karakter.';

    if (!form.nik_ibu) step2Errors.value['nik_ibu'] = 'NIK ibu wajib diisi.';
    else if (form.nik_ibu.length > 20) step2Errors.value['nik_ibu'] = 'NIK ibu maksimal 20 karakter.';

    if (!form.pendidikan_ibu) step2Errors.value['pendidikan_ibu'] = 'Pendidikan ibu wajib dipilih.';
    else if (form.pendidikan_ibu.length > 50) step2Errors.value['pendidikan_ibu'] = 'Pendidikan ibu maksimal 50 karakter.';

    if (!form.tempat_lahir_ibu) step2Errors.value['tempat_lahir_ibu'] = 'Tempat lahir ibu wajib diisi.';
    else if (form.tempat_lahir_ibu.length > 100) step2Errors.value['tempat_lahir_ibu'] = 'Tempat lahir ibu maksimal 100 karakter.';

    if (!form.pekerjaan_ibu) step2Errors.value['pekerjaan_ibu'] = 'Pekerjaan ibu wajib diisi.';
    else if (form.pekerjaan_ibu.length > 100) step2Errors.value['pekerjaan_ibu'] = 'Pekerjaan ibu maksimal 100 karakter.';

    if (!form.no_hp_ibu) step2Errors.value['no_hp_ibu'] = 'No. HP ibu wajib diisi.';
    else if (form.no_hp_ibu.length > 20) step2Errors.value['no_hp_ibu'] = 'No. HP ibu maksimal 20 karakter.';

    if (!form.jalan) step2Errors.value['jalan'] = 'Jalan wajib diisi.';
    else if (form.jalan.length > 255) step2Errors.value['jalan'] = 'Jalan maksimal 255 karakter.';

    if (!form.dusun_blok) step2Errors.value['dusun_blok'] = 'Dusun/Blok wajib diisi.';
    else if (form.dusun_blok.length > 100) step2Errors.value['dusun_blok'] = 'Dusun/Blok maksimal 100 karakter.';

    if (!form.rt_rw) step2Errors.value['rt_rw'] = 'RT/RW wajib diisi.';
    else if (form.rt_rw.length > 10) step2Errors.value['rt_rw'] = 'RT/RW maksimal 10 karakter.';

    if (!form.desa) step2Errors.value['desa'] = 'Desa wajib diisi.';
    else if (form.desa.length > 100) step2Errors.value['desa'] = 'Desa maksimal 100 karakter.';

    if (!form.kecamatan) step2Errors.value['kecamatan'] = 'Kecamatan wajib diisi.';
    else if (form.kecamatan.length > 100) step2Errors.value['kecamatan'] = 'Kecamatan maksimal 100 karakter.';

    if (!form.jurusan) {
        step2Errors.value['jurusan'] = 'Jurusan wajib dipilih.';
    } else if (!['TKRO','TJKT'].includes(form.jurusan)) {
        step2Errors.value['jurusan'] = 'Jurusan tidak valid.';
    }

    if (Object.keys(step2Errors.value).length > 0) {
        scrollToFirstError();
        return false;
    }
    return true;
};

const step1Fields = [
    'nama_lengkap','jenis_kelamin','tempat_lahir','tanggal_lahir',
    'nisn','agama','anak_ke','no_kartu_keluarga','nik','no_akte',
    'penerima_bantuan','nomor_kip','no_hp','asal_sekolah','tahun_lulus',
];
const step2Fields = [
    'nama_ayah','nik_ayah','pendidikan_ayah','tempat_lahir_ayah','tanggal_lahir_ayah',
    'pekerjaan_ayah','no_hp_ayah','nama_ibu','nik_ibu','pendidikan_ibu',
    'tempat_lahir_ibu','tanggal_lahir_ibu','pekerjaan_ibu','no_hp_ibu',
    'jalan','dusun_blok','rt_rw','desa','kecamatan','jurusan',
];

const submit = () => {
    if (!validateStep2()) return;
    form.post(route('pendaftaran.store'), {
        onError: (errors) => {
            const hasStep1Error = step1Fields.some(f => errors[f]);
            if (hasStep1Error) {
                currentStep.value = 1;
                step1Fields.forEach(f => {
                    if (errors[f]) step1Errors.value[f] = errors[f];
                });
                setTimeout(() => scrollToFirstError(), 100);
            } else {
                step2Fields.forEach((f: string) => {
                    if (errors[f]) step2Errors.value[f] = errors[f];
                });
                scrollToFirstError();
            }
        },
    });
};

const agamaList      = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
const bantuanList    = ['KIP', 'KPS/KKS/PKH', 'SKTM', 'Tidak Ada'];
const pendidikanList = ['SD/Sederajat', 'SMP/Sederajat', 'SMA/SMK/Sederajat', 'D1/D2/D3', 'S1/D4', 'S2', 'S3', 'Tidak Sekolah'];
const jurusanList    = [
    { value: 'TKRO', label: 'Teknik Kendaraan Ringan Otomotif (TKRO)' },
    { value: 'TJKT', label: 'Teknik Jaringan Komputer & Telekomunikasi (TJKT)' },
];

const showKIP = computed(() => (form.penerima_bantuan as string[]).includes('KIP'));
watch(showKIP, (val) => { if (!val) form.nomor_kip = ''; });
watch(() => form.nama_lengkap, (val) => { form.nama_lengkap = val.toUpperCase(); });

const e = (field: string): string =>
    step1Errors.value[field] || step2Errors.value[field] || (form.errors as any)[field] || '';

const today = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

// ── Contoh modal ──────────────────────────────────────────────────
const showContoh = ref(false);
const contohTitle = ref('');
const contohImg   = ref('');

const contohData: Record<string, { title: string; img: string }> = {
    no_kartu_keluarga: {
        title: 'Contoh No. Kartu Keluarga',
        img: '/storage/img/pendaftaran/noKK.png',
    },
    nik: {
        title: 'Contoh NIK Siswa (KTP/KK)',
        img: '/storage/img/pendaftaran/NIK.png',
    },
    no_akte: {
        title: 'Contoh No. Akte Kelahiran',
        img: '/storage/img/pendaftaran/noakte.png',
    },
    nomor_kip: {
        title: 'Contoh Nomor KIP',
        img: '/storage/img/pendaftaran/KIP.png',
    },
};

const openContoh = (key: string) => {
    const data = contohData[key];
    if (!data) return;
    contohTitle.value = data.title;
    contohImg.value   = data.img;
    showContoh.value  = true;
};

const closeContoh = () => { showContoh.value = false; };

// ── Google Form link ──────────────────────────────────────────────
const GFORM_URL = 'https://docs.google.com/forms/d/e/1FAIpQLScO2vo5YiXj4Fh3UAvKLAKUgOqN1E2cs1m-vPFqh2S3TmrTAw/viewform';
</script>

<template>
    <Head title="Formulir Pendaftaran — SMK Assalam Gegesik" />

    <div class="root">

        <!-- ── TOP BAR ── -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-logo-wrap">
                    <img src="/storage/img/logo/logo_w.png" alt="Logo" class="topbar-logo" />
                </div>
                <div class="topbar-school">
                    <div class="topbar-name">SMK ASSALAM GEGESIK</div>
                    <div class="topbar-tagline">PENERIMAAN PESERTA DIDIK BARU 2026/2027</div>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-gform-text">Daftar via Google Form?</span>
                <a :href="GFORM_URL" target="_blank" rel="noopener noreferrer" class="topbar-gform-btn" title="Daftar via Google Form">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="topbar-gform-label">Buka</span>
                </a>
                <div class="topbar-divider"></div>
                <a href="/" class="topbar-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="topbar-back-label">Beranda</span>
                </a>
            </div>
        </header>

        <!-- ── FORM AREA ── -->
        <div class="form-area">

            <div class="form-title-row">
                <div>
                    <h1 class="form-main-title">Formulir <span class="title-accent">Pendaftaran</span></h1>
                    <p class="form-main-sub">Isi seluruh data dengan benar dan lengkap sesuai dokumen resmi.</p>
                </div>
                <div class="form-date-box">
                    <div class="date-label">TANGGAL PENDAFTARAN</div>
                    <div class="date-value">{{ today }}</div>
                </div>
            </div>



            <div class="progress-track">
                <div class="progress-fill" :style="{ width: currentStep === 1 ? '50%' : '100%' }"></div>
            </div>

            <!-- ══ STEP 1 ══ -->
            <form v-if="currentStep === 1" @submit.prevent="handleNext" class="doc-form">
                <div class="doc-section">
                    <div class="doc-section-title"><span>DATA PRIBADI CALON SISWA</span></div>

                    <div class="doc-row doc-row-full">
                        <div class="doc-field">
                            <label class="doc-label">Nama Lengkap <span class="req">*</span></label>
                            <input v-model="form.nama_lengkap" type="text" placeholder="Sesuai akte kelahiran, huruf kapital" :class="['doc-input', { 'doc-input-err': e('nama_lengkap') }]" />
                            <span v-if="e('nama_lengkap')" class="doc-err">{{ e('nama_lengkap') }}</span>
                        </div>
                    </div>

                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Jenis Kelamin <span class="req">*</span></label>
                            <div class="check-group">
                                <label v-for="jk in ['Laki-laki','Perempuan']" :key="jk" class="check-item" :class="{ 'check-item-active': form.jenis_kelamin === jk }">
                                    <input type="radio" v-model="form.jenis_kelamin" :value="jk" class="sr-only" />
                                    <div class="check-box" :class="{ 'check-box-active': form.jenis_kelamin === jk }">
                                        <svg v-if="form.jenis_kelamin === jk" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="check-label">{{ jk }}</span>
                                </label>
                            </div>
                            <span v-if="e('jenis_kelamin')" class="doc-err">{{ e('jenis_kelamin') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Tempat Lahir <span class="req">*</span></label>
                            <input v-model="form.tempat_lahir" type="text" placeholder="Kota/Kabupaten" :class="['doc-input', { 'doc-input-err': e('tempat_lahir') }]" />
                            <span v-if="e('tempat_lahir')" class="doc-err">{{ e('tempat_lahir') }}</span>
                        </div>
                    </div>

                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Tanggal Lahir <span class="req">*</span></label>
                            <div class="cal-wrap">
                                <button type="button" @click="showCalTglLahir = !showCalTglLahir; showCalTglLahirAyah = false; showCalTglLahirIbu = false" :class="['cal-btn', { 'cal-btn-err': e('tanggal_lahir') }]">
                                    <svg class="cal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span :class="tglLahir ? 'cal-value' : 'cal-placeholder'">{{ tglLahir ? formatDisplay(tglLahir) : 'Pilih tanggal lahir' }}</span>
                                    <svg class="cal-chevron" :class="{ 'cal-chevron-open': showCalTglLahir }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div v-if="showCalTglLahir" class="cal-dropdown">
                                    <DatePicker v-model="tglLahir" @dayclick="onSelectTglLahir" color="green" is-expanded />
                                </div>
                            </div>
                            <span v-if="e('tanggal_lahir')" class="doc-err">{{ e('tanggal_lahir') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Agama <span class="req">*</span></label>
                            <select v-model="form.agama" :class="['doc-input doc-select', { 'doc-input-err': e('agama') }]">
                                <option value="">Pilih agama</option>
                                <option v-for="a in agamaList" :key="a" :value="a">{{ a }}</option>
                            </select>
                            <span v-if="e('agama')" class="doc-err">{{ e('agama') }}</span>
                        </div>
                    </div>

                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">NISN <span class="req">*</span></label>
                            <input v-model="form.nisn" type="text" placeholder="10 digit NISN" maxlength="10" :class="['doc-input', { 'doc-input-err': e('nisn') }]" />
                            <span v-if="e('nisn')" class="doc-err">{{ e('nisn') }}</span>
                        </div>
                        <div class="doc-field">
                            <div class="label-row">
                                <label class="doc-label">NIK (KTP/KK) <span class="req">*</span></label>
                                <button type="button" @click="openContoh('nik')" class="btn-contoh">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Contoh
                                </button>
                            </div>
                            <input v-model="form.nik" type="text" placeholder="16 digit NIK" maxlength="16" :class="['doc-input', { 'doc-input-err': e('nik') }]" />
                            <span v-if="e('nik')" class="doc-err">{{ e('nik') }}</span>
                        </div>
                    </div>

                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Anak Ke- <span class="req">*</span></label>
                            <input v-model="form.anak_ke" type="number" min="1" max="30" placeholder="1" :class="['doc-input', { 'doc-input-err': e('anak_ke') }]" />
                            <span v-if="e('anak_ke')" class="doc-err">{{ e('anak_ke') }}</span>
                        </div>
                        <div class="doc-field">
                            <div class="label-row">
                                <label class="doc-label">No. Kartu Keluarga <span class="req">*</span></label>
                                <button type="button" @click="openContoh('no_kartu_keluarga')" class="btn-contoh">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Contoh
                                </button>
                            </div>
                            <input v-model="form.no_kartu_keluarga" type="text" placeholder="16 digit No. KK" maxlength="16" :class="['doc-input', { 'doc-input-err': e('no_kartu_keluarga') }]" />
                            <span v-if="e('no_kartu_keluarga')" class="doc-err">{{ e('no_kartu_keluarga') }}</span>
                        </div>
                    </div>

                    <div class="doc-row doc-row-full">
                        <div class="doc-field">
                            <div class="label-row">
                                <label class="doc-label">No. Akte Kelahiran <span class="req">*</span></label>
                                <button type="button" @click="openContoh('no_akte')" class="btn-contoh">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Contoh
                                </button>
                            </div>
                            <input v-model="form.no_akte" type="text" placeholder="Nomor akte kelahiran" :class="['doc-input', { 'doc-input-err': e('no_akte') }]" />
                            <span v-if="e('no_akte')" class="doc-err">{{ e('no_akte') }}</span>
                        </div>
                    </div>

                    <div class="doc-row doc-row-full">
                        <div class="doc-field">
                            <label class="doc-label">Penerima Bantuan <span class="req">*</span></label>
                            <p class="doc-label-hint">Boleh pilih lebih dari satu</p>
                            <div class="check-multi-row">
                                <label v-for="b in bantuanList" :key="b"
                                    class="check-multi-item"
                                    :class="{ 'check-multi-active': (form.penerima_bantuan as string[]).includes(b) }">
                                    <input type="checkbox" v-model="form.penerima_bantuan" :value="b" class="sr-only" />
                                    <div class="check-multi-box" :class="{ 'check-multi-box-active': (form.penerima_bantuan as string[]).includes(b) }">
                                        <svg v-if="(form.penerima_bantuan as string[]).includes(b)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <span class="check-multi-label">{{ b }}</span>
                                </label>
                            </div>
                            <span v-if="e('penerima_bantuan')" class="doc-err">{{ e('penerima_bantuan') }}</span>
                        </div>
                    </div>

                    <div v-if="showKIP" class="doc-row doc-row-full">
                        <div class="doc-field">
                            <div class="label-row">
                                <label class="doc-label">Nomor KIP <span class="req">*</span></label>
                                <button type="button" @click="openContoh('nomor_kip')" class="btn-contoh">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Contoh
                                </button>
                            </div>
                            <input v-model="form.nomor_kip" type="text" placeholder="Nomor KIP" :class="['doc-input', { 'doc-input-err': e('nomor_kip') }]" />
                            <span v-if="e('nomor_kip')" class="doc-err">{{ e('nomor_kip') }}</span>
                        </div>
                    </div>

                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">No. HP / WhatsApp <span class="req">*</span></label>
                            <input v-model="form.no_hp" type="tel" placeholder="08xx-xxxx-xxxx" maxlength="20" :class="['doc-input', { 'doc-input-err': e('no_hp') }]" />
                            <span v-if="e('no_hp')" class="doc-err">{{ e('no_hp') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Asal Sekolah <span class="req">*</span></label>
                            <input v-model="form.asal_sekolah" type="text" placeholder="Nama SMP/sederajat" :class="['doc-input', { 'doc-input-err': e('asal_sekolah') }]" />
                            <span v-if="e('asal_sekolah')" class="doc-err">{{ e('asal_sekolah') }}</span>
                        </div>
                    </div>

                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Tahun Lulus <span class="req">*</span></label>
                            <input v-model="form.tahun_lulus" type="text" placeholder="2024" maxlength="4" :class="['doc-input', { 'doc-input-err': e('tahun_lulus') }]" />
                            <span v-if="e('tahun_lulus')" class="doc-err">{{ e('tahun_lulus') }}</span>
                        </div>
                        <div class="doc-field"></div>
                    </div>
                </div>

                <div class="doc-footer">
                    <div></div>
                    <button type="submit" class="doc-btn-next">
                        Selanjutnya
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </form>

            <!-- ══ STEP 2 ══ -->
            <form v-if="currentStep === 2" @submit.prevent="submit" class="doc-form">

                <div class="doc-section">
                    <div class="doc-section-title"><span>DATA AYAH</span></div>
                    <div class="doc-row doc-row-full">
                        <div class="doc-field">
                            <label class="doc-label">Nama Lengkap Ayah <span class="req">*</span></label>
                            <input v-model="form.nama_ayah" type="text" placeholder="Nama lengkap ayah" :class="['doc-input', { 'doc-input-err': e('nama_ayah') }]" />
                            <span v-if="e('nama_ayah')" class="doc-err">{{ e('nama_ayah') }}</span>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">NIK Ayah <span class="req">*</span></label>
                            <input v-model="form.nik_ayah" type="text" placeholder="16 digit NIK" maxlength="16" :class="['doc-input', { 'doc-input-err': e('nik_ayah') }]" />
                            <span v-if="e('nik_ayah')" class="doc-err">{{ e('nik_ayah') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Pendidikan Terakhir <span class="req">*</span></label>
                            <select v-model="form.pendidikan_ayah" :class="['doc-input doc-select', { 'doc-input-err': e('pendidikan_ayah') }]">
                                <option value="">Pilih pendidikan</option>
                                <option v-for="p in pendidikanList" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <span v-if="e('pendidikan_ayah')" class="doc-err">{{ e('pendidikan_ayah') }}</span>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Tempat Lahir <span class="req">*</span></label>
                            <input v-model="form.tempat_lahir_ayah" type="text" placeholder="Kota/Kabupaten" :class="['doc-input', { 'doc-input-err': e('tempat_lahir_ayah') }]" />
                            <span v-if="e('tempat_lahir_ayah')" class="doc-err">{{ e('tempat_lahir_ayah') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Tanggal Lahir <span class="req">*</span></label>
                            <div class="cal-wrap">
                                <button type="button" @click="showCalTglLahirAyah = !showCalTglLahirAyah; showCalTglLahir = false; showCalTglLahirIbu = false" class="cal-btn">
                                    <svg class="cal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span :class="tglLahirAyah ? 'cal-value' : 'cal-placeholder'">{{ tglLahirAyah ? formatDisplay(tglLahirAyah) : 'Pilih tanggal' }}</span>
                                    <svg class="cal-chevron" :class="{ 'cal-chevron-open': showCalTglLahirAyah }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div v-if="showCalTglLahirAyah" class="cal-dropdown">
                                    <DatePicker v-model="tglLahirAyah" @dayclick="onSelectTglLahirAyah" color="green" is-expanded />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Pekerjaan <span class="req">*</span></label>
                            <input v-model="form.pekerjaan_ayah" type="text" placeholder="Pekerjaan ayah" :class="['doc-input', { 'doc-input-err': e('pekerjaan_ayah') }]" />
                            <span v-if="e('pekerjaan_ayah')" class="doc-err">{{ e('pekerjaan_ayah') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">No. HP Ayah <span class="req">*</span></label>
                            <input v-model="form.no_hp_ayah" type="tel" placeholder="08xx-xxxx-xxxx" :class="['doc-input', { 'doc-input-err': e('no_hp_ayah') }]" />
                            <span v-if="e('no_hp_ayah')" class="doc-err">{{ e('no_hp_ayah') }}</span>
                        </div>
                    </div>
                </div>

                <div class="doc-section">
                    <div class="doc-section-title"><span>DATA IBU</span></div>
                    <div class="doc-row doc-row-full">
                        <div class="doc-field">
                            <label class="doc-label">Nama Lengkap Ibu <span class="req">*</span></label>
                            <input v-model="form.nama_ibu" type="text" placeholder="Nama lengkap ibu" :class="['doc-input', { 'doc-input-err': e('nama_ibu') }]" />
                            <span v-if="e('nama_ibu')" class="doc-err">{{ e('nama_ibu') }}</span>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">NIK Ibu <span class="req">*</span></label>
                            <input v-model="form.nik_ibu" type="text" placeholder="16 digit NIK" maxlength="16" :class="['doc-input', { 'doc-input-err': e('nik_ibu') }]" />
                            <span v-if="e('nik_ibu')" class="doc-err">{{ e('nik_ibu') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Pendidikan Terakhir <span class="req">*</span></label>
                            <select v-model="form.pendidikan_ibu" :class="['doc-input doc-select', { 'doc-input-err': e('pendidikan_ibu') }]">
                                <option value="">Pilih pendidikan</option>
                                <option v-for="p in pendidikanList" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <span v-if="e('pendidikan_ibu')" class="doc-err">{{ e('pendidikan_ibu') }}</span>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Tempat Lahir <span class="req">*</span></label>
                            <input v-model="form.tempat_lahir_ibu" type="text" placeholder="Kota/Kabupaten" :class="['doc-input', { 'doc-input-err': e('tempat_lahir_ibu') }]" />
                            <span v-if="e('tempat_lahir_ibu')" class="doc-err">{{ e('tempat_lahir_ibu') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Tanggal Lahir <span class="req">*</span></label>
                            <div class="cal-wrap">
                                <button type="button" @click="showCalTglLahirIbu = !showCalTglLahirIbu; showCalTglLahir = false; showCalTglLahirAyah = false" class="cal-btn">
                                    <svg class="cal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span :class="tglLahirIbu ? 'cal-value' : 'cal-placeholder'">{{ tglLahirIbu ? formatDisplay(tglLahirIbu) : 'Pilih tanggal' }}</span>
                                    <svg class="cal-chevron" :class="{ 'cal-chevron-open': showCalTglLahirIbu }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div v-if="showCalTglLahirIbu" class="cal-dropdown">
                                    <DatePicker v-model="tglLahirIbu" @dayclick="onSelectTglLahirIbu" color="green" is-expanded />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Pekerjaan <span class="req">*</span></label>
                            <input v-model="form.pekerjaan_ibu" type="text" placeholder="Pekerjaan ibu" :class="['doc-input', { 'doc-input-err': e('pekerjaan_ibu') }]" />
                            <span v-if="e('pekerjaan_ibu')" class="doc-err">{{ e('pekerjaan_ibu') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">No. HP Ibu <span class="req">*</span></label>
                            <input v-model="form.no_hp_ibu" type="tel" placeholder="08xx-xxxx-xxxx" :class="['doc-input', { 'doc-input-err': e('no_hp_ibu') }]" />
                            <span v-if="e('no_hp_ibu')" class="doc-err">{{ e('no_hp_ibu') }}</span>
                        </div>
                    </div>
                </div>

                <div class="doc-section">
                    <div class="doc-section-title"><span>ALAMAT TEMPAT TINGGAL</span></div>
                    <div class="doc-row doc-row-full">
                        <div class="doc-field">
                            <label class="doc-label">Jalan <span class="req">*</span></label>
                            <input v-model="form.jalan" type="text" placeholder="Nama jalan / gang" :class="['doc-input', { 'doc-input-err': e('jalan') }]" />
                            <span v-if="e('jalan')" class="doc-err">{{ e('jalan') }}</span>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Dusun / Blok <span class="req">*</span></label>
                            <input v-model="form.dusun_blok" type="text" placeholder="Dusun atau blok" :class="['doc-input', { 'doc-input-err': e('dusun_blok') }]" />
                            <span v-if="e('dusun_blok')" class="doc-err">{{ e('dusun_blok') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">RT / RW <span class="req">*</span></label>
                            <input v-model="form.rt_rw" type="text" placeholder="001/002" :class="['doc-input', { 'doc-input-err': e('rt_rw') }]" />
                            <span v-if="e('rt_rw')" class="doc-err">{{ e('rt_rw') }}</span>
                        </div>
                    </div>
                    <div class="doc-row">
                        <div class="doc-field">
                            <label class="doc-label">Desa <span class="req">*</span></label>
                            <input v-model="form.desa" type="text" placeholder="Nama desa" :class="['doc-input', { 'doc-input-err': e('desa') }]" />
                            <span v-if="e('desa')" class="doc-err">{{ e('desa') }}</span>
                        </div>
                        <div class="doc-field">
                            <label class="doc-label">Kecamatan <span class="req">*</span></label>
                            <input v-model="form.kecamatan" type="text" placeholder="Nama kecamatan" :class="['doc-input', { 'doc-input-err': e('kecamatan') }]" />
                            <span v-if="e('kecamatan')" class="doc-err">{{ e('kecamatan') }}</span>
                        </div>
                    </div>
                </div>

                <div class="doc-section">
                    <div class="doc-section-title"><span>PILIHAN KOMPETENSI KEAHLIAN</span></div>
                    <div class="jurusan-grid">
                        <label v-for="j in jurusanList" :key="j.value" class="jurusan-card" :class="{ 'jurusan-active': form.jurusan === j.value }">
                            <input type="radio" v-model="form.jurusan" :value="j.value" class="sr-only" />
                            <div class="jurusan-check">
                                <div class="jurusan-check-inner" :class="{ visible: form.jurusan === j.value }"></div>
                            </div>
                            <div>
                                <div class="jurusan-code">{{ j.value }}</div>
                                <div class="jurusan-name">{{ j.label }}</div>
                            </div>
                        </label>
                    </div>
                    <span v-if="e('jurusan')" class="doc-err">{{ e('jurusan') }}</span>
                </div>

                <div class="doc-footer">
                    <button type="button" @click="goPrev" class="doc-btn-back">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </button>
                    <button type="submit" :disabled="form.processing" class="doc-btn-submit">
                        <span v-if="form.processing" class="spinner"></span>
                        <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ form.processing ? 'Menyimpan...' : 'Kirim Pendaftaran' }}
                    </button>
                </div>
            </form>

            <div class="form-bottom">
                <div class="form-bottom-address">
                    <div class="fba-title">ALAMAT SEKOLAH</div>
                    <div>Gegesik Lor, Kec. Gegesik, Kabupaten Cirebon, Jawa Barat</div>
                    <div>T: 0231 8830069 &nbsp;·&nbsp; E: info@smkassalamgegesik.sch.id</div>
                </div>
                <div class="form-bottom-note">
                    <div class="fbn-title">TERIMA KASIH ATAS PENDAFTARAN ANDA</div>
                    <div>Pastikan semua data terisi dengan benar. Pihak sekolah akan menghubungi Anda melalui nomor HP yang terdaftar.</div>
                </div>
            </div>

        </div>

        <!-- ── MODAL CONTOH ── -->
        <Teleport to="body">
            <div v-if="showContoh" class="modal-overlay-global" @click="closeContoh">
                <div class="modal-box-global" @click.stop>
                    <div class="modal-header-global">
                        <h3 class="modal-title-global">{{ contohTitle }}</h3>
                        <button type="button" @click="closeContoh" class="modal-close-global">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="modal-body-global">
                        <img :src="contohImg" :alt="contohTitle" class="modal-img-global" />
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Overlay tutup kalender -->
        <div v-if="showCalTglLahir || showCalTglLahirAyah || showCalTglLahirIbu"
            class="cal-overlay" @click="closeAllCal"></div>

    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.root { min-height: 100vh; background: #f0f0f0; font-family: 'Plus Jakarta Sans', -apple-system, sans-serif; padding-bottom: 80px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
.topbar { background: #1a2332; padding: 0 40px; height: 70px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
.topbar-left { display: flex; align-items: center; gap: 16px; flex: 1; min-width: 0; }
.topbar-logo-wrap { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
.topbar-logo { height: 36px; width: 36px; object-fit: contain; }
.topbar-school { min-width: 0; }
.topbar-name { font-size: 15px; font-weight: 800; color: white; letter-spacing: 0.05em; white-space: nowrap; }
.topbar-tagline { font-size: 10px; color: rgba(255,255,255,0.45); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
.topbar-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.topbar-divider { width: 1px; height: 24px; background: rgba(255,255,255,0.15); }
.topbar-gform-text { font-size: 12px; color: rgba(255,255,255,0.45); white-space: nowrap; }
.topbar-gform-btn { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 700; color: #4ade80; text-decoration: none; white-space: nowrap; padding: 7px 14px; border: 1px solid rgba(74,222,128,0.35); border-radius: 4px; transition: all 0.2s; }
.topbar-gform-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
.topbar-gform-btn:hover { background: rgba(74,222,128,0.1); border-color: rgba(74,222,128,0.6); color: #86efac; }
.topbar-back { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.55); text-decoration: none; padding: 7px 14px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; transition: all 0.2s; white-space: nowrap; }
.topbar-back svg { width: 15px; height: 15px; flex-shrink: 0; }
.topbar-back:hover { color: white; border-color: rgba(255,255,255,0.4); }
.form-area { max-width: 900px; margin: 40px auto 60px; background: white; box-shadow: 0 4px 40px rgba(0,0,0,0.12); padding: 48px 56px 56px; }
.form-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-bottom: 8px; flex-wrap: wrap; }
.form-main-title { font-family: 'Fraunces', Georgia, serif; font-size: clamp(28px, 4vw, 40px); font-weight: 800; color: #1a2332; line-height: 1.1; margin-bottom: 8px; }
.title-accent { color: #15803d; }
.form-main-sub { font-size: 13px; color: #6b7280; line-height: 1.6; }
.form-date-box { text-align: right; flex-shrink: 0; }
.date-label { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; color: #374151; text-transform: uppercase; margin-bottom: 6px; }
.date-value { font-size: 13px; font-weight: 600; color: #1a2332; }



.progress-track { height: 3px; background: #e5e7eb; margin: 28px 0 36px; }
.progress-fill { height: 100%; background: linear-gradient(90deg, #15803d, #22c55e); transition: width 0.5s ease; }
.doc-form { display: flex; flex-direction: column; gap: 32px; }
.doc-section { display: flex; flex-direction: column; gap: 18px; }
.doc-section-title { display: flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 800; color: #1a2332; letter-spacing: 0.1em; padding-bottom: 10px; border-bottom: 2.5px solid #1a2332; margin-bottom: 4px; }
.doc-section-title::before { content: ''; display: block; width: 4px; height: 16px; background: #15803d; flex-shrink: 0; }
.doc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.doc-row-full { grid-template-columns: 1fr; }
.doc-field { display: flex; flex-direction: column; gap: 5px; }
.doc-label { font-size: 12px; font-weight: 700; color: #374151; letter-spacing: 0.02em; }
.req { color: #dc2626; margin-left: 2px; }
.doc-input { width: 100%; padding: 9px 12px; border: none; border-bottom: 1.5px solid #d1d5db; background: transparent; font-size: 14px; color: #111827; font-family: inherit; outline: none; transition: border-color 0.2s; border-radius: 0; appearance: none; }
.doc-input:focus { border-bottom-color: #15803d; }
.doc-input-err { border-bottom-color: #dc2626; }
.doc-select { cursor: pointer; }
.doc-err { font-size: 11px; color: #dc2626; }
.check-group { display: flex; gap: 16px; padding: 4px 0; flex-wrap: wrap; }
.check-item { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 8px 14px; border: 1.5px solid #e2e8f0; transition: all 0.2s; }
.check-item-active { border-color: #15803d; background: #f0fdf4; }
.check-box { width: 18px; height: 18px; border: 2px solid #d1d5db; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.check-box-active { border-color: #15803d; background: #15803d; }
.check-box svg { width: 12px; height: 12px; color: white; }
.check-label { font-size: 14px; font-weight: 500; color: #374151; }
.check-item-active .check-label { color: #15803d; font-weight: 600; }
.cal-wrap { position: relative; }
.cal-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border: none; border-bottom: 1.5px solid #d1d5db; background: transparent; cursor: pointer; font-family: inherit; text-align: left; transition: border-color 0.2s; }
.cal-btn:hover { border-bottom-color: #15803d; }
.cal-btn-err { border-bottom-color: #dc2626; }
.cal-icon { width: 16px; height: 16px; color: #9ca3af; flex-shrink: 0; }
.cal-value { font-size: 14px; color: #111827; flex: 1; }
.cal-placeholder { font-size: 14px; color: #9ca3af; flex: 1; }
.cal-chevron { width: 14px; height: 14px; color: #9ca3af; transition: transform 0.2s; flex-shrink: 0; }
.cal-chevron-open { transform: rotate(180deg); }
.cal-dropdown { position: absolute; left: 0; top: calc(100% + 4px); z-index: 99; border: 1px solid #e2e8f0; background: white; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 12px; overflow: hidden; }
.cal-overlay { position: fixed; inset: 0; z-index: 98; background: transparent; }
.doc-label-hint { font-size: 11px; color: #9ca3af; margin-top: -3px; }
.check-multi-row { display: flex; flex-wrap: wrap; gap: 10px; padding: 4px 0; }
.check-multi-item { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: 1.5px solid #e2e8f0; cursor: pointer; transition: all 0.2s; font-size: 13px; font-weight: 600; color: #6b7280; user-select: none; }
.check-multi-active { border-color: #15803d; background: #f0fdf4; color: #15803d; }
.check-multi-box { width: 16px; height: 16px; border: 2px solid #d1d5db; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.check-multi-box-active { border-color: #15803d; background: #15803d; }
.check-multi-box svg { width: 10px; height: 10px; color: white; }
.check-multi-label { font-size: 13px; }
.jurusan-grid { display: flex; flex-direction: column; gap: 12px; }
.jurusan-card { display: flex; align-items: center; gap: 16px; padding: 16px 20px; border: 1.5px solid #e2e8f0; cursor: pointer; transition: all 0.2s; }
.jurusan-active { border-color: #15803d; background: #f0fdf4; }
.jurusan-check { width: 20px; height: 20px; border: 2px solid #d1d5db; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: border-color 0.2s; }
.jurusan-active .jurusan-check { border-color: #15803d; }
.jurusan-check-inner { width: 10px; height: 10px; background: #15803d; opacity: 0; transition: opacity 0.2s; }
.jurusan-check-inner.visible { opacity: 1; }
.jurusan-code { font-size: 11px; font-weight: 800; color: #15803d; letter-spacing: 0.08em; margin-bottom: 2px; }
.jurusan-name { font-size: 13px; font-weight: 600; color: #1a2332; }
.doc-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 20px; margin-top: 8px; }
.doc-btn-next { display: inline-flex; align-items: center; gap: 10px; background: #1a2332; color: white; border: none; padding: 12px 24px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; transition: background 0.2s; border-radius: 6px; }
.doc-btn-next:hover { background: #15803d; }
.doc-btn-next svg { width: 18px; height: 18px; }
.doc-btn-back { display: inline-flex; align-items: center; gap: 10px; background: white; color: #374151; border: 1.5px solid #e2e8f0; padding: 12px 22px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.2s; border-radius: 6px; }
.doc-btn-back:hover { border-color: #9ca3af; }
.doc-btn-back svg { width: 18px; height: 18px; }
.doc-btn-submit { display: inline-flex; align-items: center; gap: 10px; background: #15803d; color: white; border: none; padding: 12px 24px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; transition: background 0.2s; border-radius: 6px; }
.doc-btn-submit:hover:not(:disabled) { background: #166534; }
.doc-btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.doc-btn-submit svg { width: 18px; height: 18px; }
.spinner { width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.form-bottom { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 24px; border-top: 2.5px solid #1a2332; font-size: 12px; color: #6b7280; line-height: 1.7; }
.fba-title, .fbn-title { font-size: 11px; font-weight: 800; color: #1a2332; letter-spacing: 0.08em; margin-bottom: 6px; text-transform: uppercase; }
.fbn-title { color: #15803d; }
.label-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.btn-contoh { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #15803d; background: none; border: none; cursor: pointer; padding: 0; white-space: nowrap; flex-shrink: 0; transition: color 0.2s; }
.btn-contoh:hover { color: #166534; }
.btn-contoh svg { width: 13px; height: 13px; }

@media (max-width: 768px) {
    .root { background: white; padding-bottom: 0; }
    .topbar { padding: 0 14px; height: 56px; }
    .topbar-logo-wrap { width: 36px; height: 36px; }
    .topbar-logo { height: 28px; width: 28px; }
    .topbar-name { display: block; font-size: 12px; letter-spacing: 0.02em; }
    .topbar-tagline { display: none; }
    .topbar-right { gap: 8px; }
    .topbar-gform-text { display: none; }
    .topbar-gform-label { display: none; }
    .topbar-gform-btn { font-size: 11px; padding: 6px 10px; gap: 5px; }
    .topbar-back { font-size: 11px; padding: 6px 10px; gap: 5px; }
    .topbar-back-label { display: none; }
    .topbar-divider { display: none; }
    .form-area { padding: 28px 16px 48px; margin: 0; box-shadow: none; border-radius: 0; }
    .form-title-row { flex-direction: column; gap: 8px; margin-bottom: 4px; }
    .form-main-title { font-size: 22px; margin-bottom: 4px; }
    .form-main-sub { font-size: 12px; }
    .form-date-box { text-align: left; }
    .progress-track { margin: 16px 0 28px; }
    .doc-row { grid-template-columns: 1fr; gap: 16px; }
    .doc-section { gap: 16px; }
    .doc-form { gap: 28px; }
    .form-bottom { grid-template-columns: 1fr; gap: 16px; margin-top: 28px; padding-top: 20px; }
    .doc-footer { flex-direction: column-reverse; gap: 10px; align-items: stretch; padding-top: 16px; margin-top: 4px; }
    .doc-btn-next, .doc-btn-back, .doc-btn-submit { width: 100%; justify-content: center; padding: 13px 20px; font-size: 14px; }
    .check-group { gap: 8px; }
    .check-item { flex: 1; justify-content: center; padding: 10px 8px; }
    .jurusan-card { padding: 14px 16px; }
    .cal-dropdown { left: 0; right: auto; }
    .label-row { flex-wrap: wrap; gap: 4px; }


}
</style>

<style>
.modal-overlay-global { position: fixed !important; inset: 0 !important; background: rgba(0,0,0,0.65) !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 24px !important; }
.modal-box-global { background: white; border-radius: 12px; max-width: 560px; width: 100%; box-shadow: 0 24px 64px rgba(0,0,0,0.3); overflow: hidden; position: relative; z-index: 100000; }
.modal-header-global { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #e5e7eb; }
.modal-title-global { font-size: 15px; font-weight: 700; color: #1a2332; font-family: 'Plus Jakarta Sans', sans-serif; }
.modal-close-global { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border: none; border-radius: 50%; cursor: pointer; transition: background 0.2s; }
.modal-close-global:hover { background: #e5e7eb; }
.modal-close-global svg { width: 16px; height: 16px; color: #374151; }
.modal-body-global { padding: 20px 24px 24px; }
.modal-img-global { width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; display: block; margin-bottom: 12px; }
.modal-note-global { font-size: 12px; color: #6b7280; line-height: 1.6; background: #f0fdf4; padding: 10px 14px; border-radius: 6px; border-left: 3px solid #15803d; font-family: 'Plus Jakarta Sans', sans-serif; }
</style>
