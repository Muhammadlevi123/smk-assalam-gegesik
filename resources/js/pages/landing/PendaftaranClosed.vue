<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    belum_mulai?: boolean;
    sudah_lewat?: boolean;
}>();

const formatTanggal = (str?: string | null) => {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const pesan = computed(() => {
    if (props.belum_mulai) {
        return `Pendaftaran akan dibuka mulai tanggal ${formatTanggal(props.tanggal_mulai)}. Silakan kembali lagi pada tanggal tersebut.`;
    }
    if (props.sudah_lewat) {
        return `Pendaftaran telah ditutup sejak tanggal ${formatTanggal(props.tanggal_selesai)}. Terima kasih atas antusiasme Anda.`;
    }
    return 'Mohon maaf, jadwal pendaftaran belum diatur oleh pihak sekolah. Silakan hubungi pihak sekolah untuk informasi lebih lanjut.';
});

const judul = computed(() => {
    if (props.belum_mulai) return 'Pendaftaran Segera Dibuka';
    if (props.sudah_lewat) return 'Pendaftaran Telah Ditutup';
    return 'Pendaftaran Belum Dibuka';
});
</script>

<template>
    <Head title="Pendaftaran Belum Dibuka">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet" />
    </Head>

    <div class="page-root">
        <div class="card">
            <!-- Logo -->
            <div class="logo-wrap">
                <img src="/storage/img/logo/logo.png" alt="Logo SMK Assalam Gegesik" class="logo-img" />
            </div>

            <h1 class="title">{{ judul }}</h1>
            <p class="desc">{{ pesan }}</p>

            <!-- Periode, tampil kalau ada -->
            <div v-if="tanggal_mulai && tanggal_selesai" class="periode-box">
                <div class="periode-label">Periode Pendaftaran</div>
                <div class="periode-value">
                    {{ formatTanggal(tanggal_mulai) }} &nbsp;–&nbsp; {{ formatTanggal(tanggal_selesai) }}
                </div>
            </div>

            <div class="divider"></div>

            <!-- Nama sekolah -->
            <div class="contact-block">
                <p class="contact-name">SMK Assalam Gegesik</p>
                <p class="contact-line">Gegesik Lor, Kec. Gegesik, Kabupaten Cirebon, Jawa Barat</p>
            </div>

            <a href="/" class="btn-home">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</template>

<style scoped>
* { box-sizing: border-box; }

.page-root {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, #f0fdf4 0%, #eafaf0 100%);
    padding: 32px 20px;
    font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
}

.card {
    width: 100%;
    max-width: 460px;
    background: #ffffff;
    border-radius: 20px;
    padding: 44px 40px 36px;
    text-align: center;
    box-shadow: 0 20px 50px rgba(22, 101, 52, 0.08), 0 2px 8px rgba(0,0,0,0.03);
    border: 1px solid #ecf5ee;
}

.logo-wrap {
    width: 88px;
    height: 88px;
    margin: 0 auto 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 23px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 12px;
    letter-spacing: -0.01em;
}

.desc {
    font-size: 14.5px;
    color: #6b7280;
    line-height: 1.7;
    margin: 0 0 24px;
}

.periode-box {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 24px;
}
.periode-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #16a34a;
    margin-bottom: 4px;
}
.periode-value {
    font-size: 14px;
    font-weight: 600;
    color: #15803d;
}

.divider {
    height: 1px;
    background: #f3f4f6;
    margin: 0 0 24px;
}

.contact-block {
    margin-bottom: 28px;
}
.contact-name {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px;
}
.contact-line {
    font-size: 12.5px;
    color: #9ca3af;
    margin: 0;
    line-height: 1.5;
}

.btn-home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 28px;
    background: #16a34a;
    color: #ffffff;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: background 0.2s, transform 0.15s;
}
.btn-home:hover {
    background: #15803d;
    transform: translateY(-1px);
}

@media (max-width: 480px) {
    .card { padding: 36px 24px 28px; border-radius: 16px; }
    .title { font-size: 20px; }
}
</style>
