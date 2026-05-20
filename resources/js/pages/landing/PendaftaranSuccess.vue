<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Pendaftaran Berhasil — SMK Assalam Gegesik" />

    <div class="root">
        <!-- Header sama dengan Pendaftaran.vue -->
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
                <a href="/" class="topbar-back">← Beranda</a>
            </div>
        </header>

        <!-- Konten sukses -->
        <div class="success-body">
            <div class="success-card">

                <!-- Icon centang -->
                <div class="icon-wrap">
                    <div class="icon-circle">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <h1 class="success-title">Pendaftaran Berhasil!</h1>
                <p class="success-sub">
                    Terima kasih telah mendaftar di <strong>SMK Assalam Gegesik</strong>.<br>
                    Data pendaftaran kamu telah kami terima dan akan segera diproses.
                </p>

                <!-- Info -->
                <div class="info-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>Pihak sekolah akan menghubungi kamu melalui nomor HP yang telah didaftarkan untuk pemberitahuan jadwal seleksi dan kelengkapan berkas.</p>
                </div>


                <!-- Kontak -->
                <div class="contact-row">
                    <a href="tel:02318830069" class="contact-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z"/>
                        </svg>
                        0231 8830069
                    </a>
                    <a href="mailto:info@smkassalamgegesik.sch.id" class="contact-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        info@smkassalamgegesik.sch.id
                    </a>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.root {
    min-height: 100vh;
    background: #f0f0f0;
    font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
}

/* Topbar sama persis dengan Pendaftaran.vue */
.topbar {
    background: #1a2332;
    padding: 0 40px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}
.topbar-left { display: flex; align-items: center; gap: 16px; }
.topbar-logo-wrap {
    width: 48px; height: 48px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
}
.topbar-logo { height: 36px; width: 36px; object-fit: contain; }
.topbar-name { font-size: 15px; font-weight: 800; color: white; letter-spacing: 0.05em; }
.topbar-tagline { font-size: 10px; color: rgba(255,255,255,0.45); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
.topbar-back {
    font-size: 13px; font-weight: 600;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    padding: 8px 16px;
    border: 1px solid rgba(255,255,255,0.15);
    transition: all 0.2s;
}
.topbar-back:hover { color: white; border-color: rgba(255,255,255,0.4); }

/* Body */
.success-body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 70px);
    padding: 40px 24px;
}

/* Card */
.success-card {
    background: white;
    max-width: 520px;
    width: 100%;
    padding: 48px 40px;
    box-shadow: 0 4px 40px rgba(0,0,0,0.12);
    text-align: center;
}

/* Icon */
.icon-wrap { display: flex; justify-content: center; margin-bottom: 28px; }
.icon-circle {
    width: 88px; height: 88px;
    border-radius: 50%;
    background: linear-gradient(135deg, #14532d, #22c55e);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 32px rgba(21,128,61,0.3);
}
.icon-circle svg { width: 44px; height: 44px; color: white; }

/* Text */
.success-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(24px, 4vw, 32px);
    font-weight: 700;
    color: #1a2332;
    margin-bottom: 12px;
}
.success-sub {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 28px;
}
.success-sub strong { color: #1a2332; }

/* Info box */
.info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-left: 4px solid #15803d;
    padding: 14px 16px;
    text-align: left;
    margin-bottom: 32px;
}
.info-box svg { width: 18px; height: 18px; color: #15803d; flex-shrink: 0; margin-top: 1px; }
.info-box p { font-size: 13px; color: #374151; line-height: 1.6; }

/* Kontak */
.contact-row {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    padding-top: 20px;
    border-top: 1px solid #f3f4f6;
}
.contact-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
    color: #9ca3af; text-decoration: none;
    transition: color 0.2s;
}
.contact-link:hover { color: #15803d; }
.contact-link svg { width: 14px; height: 14px; }

@media (max-width: 480px) {
    .topbar { padding: 0 16px; }
    .topbar-tagline { display: none; }
    .success-card { padding: 32px 20px; }
    .contact-row { flex-direction: column; align-items: center; gap: 10px; }
}
</style>
