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
                        <span class="bc-sep">&#x203A;</span>
                        <span class="bc-current">Tenaga Pendidik &amp; Kependidikan</span>
                    </nav>

                    <!-- Header -->
                    <div class="article-header">
                        <div class="article-line"></div>
                        <h1 class="article-title">Tenaga Pendidik &amp; Kependidikan</h1>
                        <p class="article-subtitle">
                            Daftar tenaga pendidik dan kependidikan aktif SMK Assalam Gegesik
                            <template v-if="tahun_ajaran">TA {{ tahun_ajaran }}</template>
                        </p>
                    </div>

                    <!-- Tabel gabungan -->
                    <div v-if="tkRows.length > 0 || guruRows.length > 0" class="table-wrap fade-in">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="th-no">No</th>
                                    <th>Nama PTK</th>
                                    <th>Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Tenaga Kependidikan -->
                                <template v-if="tkRows.length > 0">
                                    <tr class="tr-separator">
                                        <td colspan="3">Tenaga Kependidikan</td>
                                    </tr>
                                    <tr v-for="row in tkRows" :key="'tk-' + row.no" class="tr-row">
                                        <td class="td-no">{{ row.no }}</td>
                                        <td class="td-name">{{ row.nama }}</td>
                                        <td class="td-jabatan">{{ row.jabatan }}</td>
                                    </tr>
                                </template>

                                <!-- Tenaga Pendidik / Guru -->
                                <template v-if="guruRows.length > 0">
                                    <tr v-for="row in guruRows" :key="'gr-' + row.no" class="tr-row">
                                        <td class="td-no">{{ row.no }}</td>
                                        <td class="td-name">{{ row.nama }}</td>
                                        <td class="td-jabatan">{{ row.jabatan }}</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="empty-state fade-in">
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

/* Subtitle + TA pakai font & ukuran sama */
.article-subtitle {
    font-size: 14px;
    font-family: var(--font-body);
    color: var(--gray-400);
    margin: 0;
}

/* Tabel dengan border lengkap (real table) */
.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--gray-300); }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table thead { background: var(--gray-50); }
.data-table th {
    padding: 11px 16px;
    text-align: left;
    font-size: 12px; font-weight: 700;
    color: var(--gray-600);
    letter-spacing: 0.04em;
    border: 1px solid var(--gray-300);
}
.th-no { width: 52px; text-align: center; }

.tr-separator td {
    padding: 7px 16px;
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--green-700);
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}

.tr-row { border-bottom: 1px solid var(--gray-200); }
.tr-row:last-child { border-bottom: none; }
.tr-row:hover { background: #fafafa; }
.data-table td { padding: 10px 16px; border: 1px solid var(--gray-200); vertical-align: middle; }
.td-no { text-align: center; color: var(--gray-400); font-size: 12px; }
.td-name { font-size: 13px; font-weight: 600; color: var(--gray-900); }
.td-jabatan { font-size: 13px; color: var(--gray-600); }

.empty-state { text-align: center; padding: 48px 24px; color: var(--gray-400); font-size: 14px; }
</style>
