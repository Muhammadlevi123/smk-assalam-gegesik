<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted, computed } from 'vue';

interface Guru {
    id: number;
    nama: string;
    nip?: string;
    mata_pelajaran?: string;
}

interface TenagaKependidikan {
    id: number;
    nama: string;
    jabatan?: string;
}

const props = defineProps<{
    guru?: Guru[];
    tenaga_kependidikan?: TenagaKependidikan[];
    tahun_ajaran?: string;
    status_tahun_ajaran?: 'berjalan' | 'akan-datang' | 'selesai' | 'tidak ada';
}>();

const tkRows = computed(() =>
    (props.tenaga_kependidikan ?? []).map((t, i) => ({
        no: i + 1,
        nama: t.nama,
        jabatan: t.jabatan || '-',
    }))
);

const guruRows = computed(() =>
    (props.guru ?? []).map((g, i) => ({
        no: (props.tenaga_kependidikan?.length ?? 0) + i + 1,
        nama: g.nama,
        jabatan: g.mata_pelajaran || '-',
    }))
);

const totalPTK = computed(() => tkRows.value.length + guruRows.value.length);

const statusLabel = computed(() => {
    switch (props.status_tahun_ajaran) {
        case 'berjalan':    return 'Sedang Berjalan';
        case 'akan-datang': return 'Akan Datang';
        case 'selesai':     return 'Telah Selesai';
        default:            return '';
    }
});

const statusColor = computed(() => {
    switch (props.status_tahun_ajaran) {
        case 'berjalan':    return 'badge-berjalan';
        case 'akan-datang': return 'badge-akan-datang';
        case 'selesai':     return 'badge-selesai';
        default:            return '';
    }
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                (entry.target as HTMLElement).classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
});
</script>

<template>
    <Head title="Tenaga Pendidik & Kependidikan - SMK Assalam Gegesik" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <!-- COVER -->
        <section class="cover-section">
            <div class="cover-bg"></div>
            <div class="cover-overlay"></div>
        </section>

        <!-- PAGE BG -->
        <div class="page-bg">
            <div class="page-wrap">

                <article class="article fade-in" style="margin-top: -70px; position: relative; z-index: 10;">

                    <!-- Breadcrumb -->
                    <nav class="bc-nav">
                        <Link href="/" class="bc-link">Beranda</Link>
                        <span class="bc-sep">›</span>
                        <span class="bc-current">Tenaga Pendidik &amp; Kependidikan</span>
                    </nav>

                    <!-- Header -->
                    <div class="article-header">
                        <div class="article-line"></div>
                        <h1 class="article-title">Tenaga Pendidik &amp; Kependidikan</h1>

                        <!-- Subtitle dinamis -->
                        <p v-if="tahun_ajaran && tahun_ajaran !== '-'" class="article-subtitle">
                            Daftar tenaga pendidik dan kependidikan aktif SMK Assalam Gegesik TA {{ tahun_ajaran }}
                        </p>

                    </div>

                    <!-- Tabel -->
                    <div v-if="tkRows.length > 0 || guruRows.length > 0" class="table-wrap fade-in">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="th-no">No</th>
                                    <th>Nama PTK</th>
                                    <th>Jabatan / Mata Pelajaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Tenaga Kependidikan -->
                                <tr v-for="row in tkRows" :key="'tk-' + row.no" class="tr-row">
                                    <td class="td-no">{{ row.no }}</td>
                                    <td class="td-name">{{ row.nama }}</td>
                                    <td class="td-jabatan">{{ row.jabatan }}</td>
                                </tr>

                                <!-- Guru -->
                                <tr v-for="row in guruRows" :key="'gr-' + row.no" class="tr-row">
                                    <td class="td-no">{{ row.no }}</td>
                                    <td class="td-name">{{ row.nama }}</td>
                                    <td class="td-jabatan">{{ row.jabatan }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty -->
                    <div v-else class="empty-state fade-in">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="empty-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p>Data belum tersedia untuk tahun ajaran ini.</p>
                    </div>

                </article>
            </div>
        </div>

        <FooterUser />
    </div>
</template>

<style scoped>
.page-root {
    --green-600: #16a34a;
    --green-700: #15803d;
    --gray-50:  #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-900: #111827;
    --font-display: 'Fraunces', Georgia, serif;
    --font-body: 'Plus Jakarta Sans', sans-serif;
    --radius: 10px;
    font-family: var(--font-body);
    color: var(--gray-900);
    min-height: 100vh;
}

.fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
.fade-in.visible { opacity: 1; transform: translateY(0); }

.cover-section { position: relative; height: 340px; overflow: visible; }
.cover-bg { position: absolute; inset: 0; background: url('/storage/img/landingpage/cover4.png') center/cover no-repeat; }
.cover-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(10,40,20,0.72) 0%, rgba(22,101,52,0.55) 100%); }
@media (max-width: 768px) { .cover-section { height: 220px; } }

.page-bg { background: var(--gray-50); padding: 0 24px 72px; }
.page-wrap { max-width: 960px; margin: 0 auto; }

.article { background: white; box-shadow: 0 4px 24px rgba(0,0,0,0.10); border: 1px solid var(--gray-100); padding: 36px 44px 52px; }
@media (max-width: 600px) { .article { padding: 24px 18px 40px; } }

.bc-nav { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--gray-100); }
.bc-link { font-size: 13px; color: var(--gray-400); text-decoration: none; transition: color 0.2s; }
.bc-link:hover { color: var(--green-600); }
.bc-sep { font-size: 13px; color: var(--gray-300); }
.bc-current { font-size: 13px; font-weight: 600; color: var(--green-700); }

.article-header { margin-bottom: 24px; }
.article-line { width: 40px; height: 3px; background: var(--green-600); border-radius: 2px; margin-bottom: 16px; }
.article-title { font-family: var(--font-display); font-size: clamp(20px, 3vw, 32px); font-weight: 700; color: var(--gray-900); line-height: 1.2; margin: 0 0 8px; }
.article-subtitle { font-size: 14px; color: var(--gray-400); margin: 0 0 12px; }

/* TA Info row */
.ta-info { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
.ta-label { font-size: 13px; font-weight: 600; color: var(--gray-700); }
.ta-count { font-size: 12px; color: var(--gray-400); }

/* Badge status */
.ta-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; }
.badge-berjalan    { background: #dcfce7; color: #15803d; }
.badge-akan-datang { background: #dbeafe; color: #1d4ed8; }
.badge-selesai     { background: var(--gray-100); color: var(--gray-600); }

.pulse-dot { width: 7px; height: 7px; background: #16a34a; border-radius: 50%; display: inline-block; animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

/* Notice akan datang */
.ta-notice { display: flex; align-items: flex-start; gap: 8px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; font-size: 13px; color: #1d4ed8; line-height: 1.5; }
.ta-notice svg { flex-shrink: 0; margin-top: 1px; }

/* Tabel */
.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--gray-300); }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table thead { background: var(--gray-50); }
.data-table th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 700; color: var(--gray-600); letter-spacing: 0.04em; border: 1px solid var(--gray-300); }
.th-no { width: 52px; text-align: center; }
.tr-row { border-bottom: 1px solid var(--gray-200); }
.tr-row:last-child { border-bottom: none; }
.tr-row:hover { background: #fafafa; }
.data-table td { padding: 10px 16px; border: 1px solid var(--gray-200); vertical-align: middle; }
.td-no { text-align: center; color: var(--gray-400); font-size: 12px; }
.td-name { font-size: 13px; font-weight: 600; color: var(--gray-900); }
.td-jabatan { font-size: 13px; color: var(--gray-600); }

/* Empty */
.empty-state { text-align: center; padding: 60px 24px; color: var(--gray-400); }
.empty-icon { margin: 0 auto 16px; display: block; }
.empty-state p { font-size: 14px; }
</style>
