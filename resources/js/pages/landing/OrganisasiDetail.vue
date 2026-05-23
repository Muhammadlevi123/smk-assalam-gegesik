<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted } from 'vue';

interface JadwalLine {
    hari: string;
    jam:  string;
}

interface OrganisasiDetail {
    id:              number;
    slug:            string;
    nama:            string;
    jenis:           string;
    deskripsi?:      string;
    pembina?:        string;
    jadwal_latihan?: string;
    jadwal_lines:    JadwalLine[];
    logo?:           string;
}

interface OrganisasiLain {
    id:    number;
    slug:  string;
    nama:  string;
    jenis: string;
    logo?: string;
}

const props = defineProps<{
    organisasi: OrganisasiDetail;
    lainnya?:   OrganisasiLain[];
}>();

onMounted(() => {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                (entry.target as HTMLElement).classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
});
</script>

<template>
    <Head :title="(organisasi?.nama ?? 'Organisasi') + ' — SMK Assalam Gegesik'" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <!-- COVER -->
        <section class="cover-section">
            <div class="cover-bg" :style="organisasi?.logo ? { backgroundImage: 'url(' + organisasi.logo + ')' } : {}"></div>
            <div class="cover-overlay"></div>
            <div class="cover-badge-wrap">
                <span class="cover-badge">{{ organisasi?.jenis }}</span>
            </div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">
                <div class="layout fade-in" style="margin-top:-70px;position:relative;z-index:10;">

                    <!-- ── KONTEN UTAMA ── -->
                    <main class="main-col">

                        <!-- Breadcrumb -->
                        <nav class="bc-nav">
                            <Link href="/" class="bc-link">Beranda</Link>
                            <span class="bc-sep">›</span>
                            <Link href="/#ekskur" class="bc-link">Ekstrakurikuler</Link>
                            <span class="bc-sep">›</span>
                            <span class="bc-current">{{ organisasi?.nama }}</span>
                        </nav>

                        <!-- Header organisasi -->
                        <div class="org-header">
                            <div class="org-logo-wrap">
                                <img v-if="organisasi?.logo" :src="organisasi.logo" :alt="organisasi.nama" class="org-logo-img" />
                                <span v-else class="org-logo-initial">{{ organisasi?.nama?.charAt(0)?.toUpperCase() }}</span>
                            </div>
                            <div class="org-header-text">
                                <span class="org-jenis-badge">{{ organisasi?.jenis }}</span>
                                <h1 class="org-nama">{{ organisasi?.nama }}</h1>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <!-- Info pembina & jadwal — 2 kolom, tanpa bg & tanpa icon -->
                        <div class="info-stack">

                            <!-- Pembina -->
                            <div v-if="organisasi?.pembina" class="info-row">
                                <p class="info-label">Pembina</p>
                                <p class="info-value">{{ organisasi.pembina }}</p>
                            </div>

                            <!-- Jadwal Latihan -->
                            <div v-if="organisasi?.jadwal_lines?.length > 0" class="info-row">
                                <p class="info-label">Jadwal Latihan</p>
                                <div class="jadwal-table">
                                    <div v-for="line in organisasi.jadwal_lines" :key="line.hari" class="jadwal-row">
                                        <span class="jadwal-hari">{{ line.hari }},</span>
                                        <span class="jadwal-jam">{{ line.jam }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="divider"></div>

                        <!-- Deskripsi -->
                        <div class="deskripsi-section">
                            <h2 class="section-label-text">Tentang {{ organisasi?.nama }}</h2>
                            <p v-if="organisasi?.deskripsi" class="deskripsi-text">{{ organisasi.deskripsi }}</p>
                            <p v-else class="deskripsi-empty">Deskripsi kegiatan ini akan segera tersedia.</p>
                        </div>

                        <!-- Tombol kembali -->
                        <div class="back-wrap">
                            <Link href="/" class="btn-back">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Kembali ke Beranda
                            </Link>
                        </div>
                    </main>

                    <!-- ── SIDEBAR ── -->
                    <aside v-if="lainnya && lainnya.length > 0" class="sidebar fade-in">
                        <div class="sidebar-box">
                            <h3 class="sidebar-title">{{ organisasi?.jenis }} Lainnya</h3>
                            <div class="lainnya-list">
                                <Link
                                    v-for="item in lainnya"
                                    :key="item.id"
                                    :href="`/profil/organisasi/${item.slug}`"
                                    class="lainnya-item"
                                >
                                    <div class="lainnya-logo-wrap">
                                        <img v-if="item.logo" :src="item.logo" :alt="item.nama" class="lainnya-logo-img" />
                                        <span v-else class="lainnya-logo-initial">{{ item.nama.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div class="lainnya-body">
                                        <span class="lainnya-jenis">{{ item.jenis }}</span>
                                        <p class="lainnya-nama">{{ item.nama }}</p>
                                    </div>
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="lainnya-arrow">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </aside>

                </div>
            </div>
        </div>

        <FooterUser />
    </div>
</template>

<style scoped>
.page-root {
    --g500:#22c55e;--g600:#16a34a;--g700:#15803d;--g800:#166534;
    --gray50:#f9fafb;--gray100:#f3f4f6;--gray200:#e5e7eb;--gray300:#d1d5db;
    --gray400:#9ca3af;--gray500:#6b7280;--gray600:#4b5563;--gray700:#374151;--gray900:#111827;
    --fd:'Fraunces',Georgia,serif;--fb:'Plus Jakarta Sans',sans-serif;
    font-family:var(--fb);color:var(--gray900);min-height:100vh;
}
.fade-in{opacity:0;transform:translateY(20px);transition:opacity .6s ease,transform .6s ease}
.fade-in.visible{opacity:1;transform:none}

/* COVER */
.cover-section{position:relative;height:300px;overflow:visible}
.cover-bg{
    position:absolute;inset:0;
    background:center/cover no-repeat;
    background-color:#1a3a2a;
    filter:blur(2px) brightness(0.7);
    transform:scale(1.04);
}
.cover-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(10,40,20,.65),rgba(22,101,52,.5))}
.cover-badge-wrap{position:absolute;bottom:24px;left:50%;transform:translateX(-50%);z-index:5}
.cover-badge{background:rgba(255,255,255,.18);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.3);color:white;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 18px;border-radius:20px}
@media(max-width:768px){.cover-section{height:220px}}

/* PAGE */
.page-bg{background:var(--gray50);padding:0 24px 72px}
.page-wrap{max-width:1100px;margin:0 auto}

/* LAYOUT 2 kolom */
.layout{display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start}
@media(max-width:900px){.layout{grid-template-columns:1fr}}

/* MAIN */
.main-col{background:white;box-shadow:0 4px 24px rgba(0,0,0,.09);border:1px solid var(--gray100);padding:36px 40px 48px;display:flex;flex-direction:column;gap:24px}
@media(max-width:600px){.main-col{padding:24px 18px 36px}}

/* BREADCRUMB */
.bc-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.bc-link{font-size:13px;color:var(--gray400);text-decoration:none;transition:color .2s}
.bc-link:hover{color:var(--g600)}
.bc-sep{font-size:13px;color:var(--gray300)}
.bc-current{font-size:13px;font-weight:600;color:var(--g700)}

/* ORG HEADER */
.org-header{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.org-logo-wrap{width:72px;height:72px;border-radius:16px;overflow:hidden;border:2px solid var(--gray200);background:var(--gray100);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.org-logo-img{width:100%;height:100%;object-fit:cover}
.org-logo-initial{font-family:var(--fd);font-size:28px;font-weight:700;color:var(--g700)}
.org-header-text{display:flex;flex-direction:column;gap:6px}
.org-jenis-badge{display:inline-block;background:var(--g600);color:white;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:3px 12px;border-radius:3px;width:fit-content}
.org-nama{font-family:var(--fd);font-size:clamp(22px,3.5vw,32px);font-weight:700;color:var(--gray900);line-height:1.2;margin:0}

/* DIVIDER */
.divider{height:1px;background:var(--gray100)}

/* INFO STACK — 2 kolom 50/50, tanpa bg, tanpa icon */
.info-stack{display:grid;grid-template-columns:1fr 1fr;gap:24px}
@media(max-width:600px){.info-stack{grid-template-columns:1fr}}
.info-row{display:flex;flex-direction:column;gap:6px}
.info-label{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--gray400);margin:0}
.info-value{font-size:14px;font-weight:600;color:var(--gray900);margin:0;word-break:break-word}

/* JADWAL TABLE */
.jadwal-table{display:flex;flex-direction:column;gap:5px}
.jadwal-row{display:flex;align-items:center;gap:4px}
.jadwal-hari{font-size:13px;font-weight:700;color:var(--gray900)}
.jadwal-jam{font-size:13px;color:var(--gray600)}

/* DESKRIPSI */
.section-label-text{font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--g700);margin:0 0 12px}
.deskripsi-text{font-size:15px;line-height:1.85;color:var(--gray600);margin:0;white-space:pre-wrap;text-align:justify}
.deskripsi-empty{font-size:14px;color:var(--gray400);font-style:italic;margin:0}

/* BACK */
.back-wrap{padding-top:30px;border-top:1px solid var(--gray100);margin-top:12px}
.btn-back{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:1.5px solid var(--gray200);border-radius:4px;font-size:13px;font-weight:600;color:var(--gray700);text-decoration:none;transition:all .2s}
.btn-back:hover{border-color:var(--g600);color:var(--g700);background:var(--gray50)}

/* SIDEBAR */
.sidebar{position:sticky;top:100px}
.sidebar-box{background:white;border-radius:4px;box-shadow:0 2px 12px rgba(0,0,0,.07);border:1px solid var(--gray100);overflow:hidden}
.sidebar-title{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g700);padding:14px 16px 12px;border-bottom:1px solid var(--gray100);margin:0}
.lainnya-list{display:flex;flex-direction:column}
.lainnya-item{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid var(--gray100);text-decoration:none;transition:background .15s}
.lainnya-item:last-child{border-bottom:none}
.lainnya-item:hover{background:var(--gray50)}
.lainnya-logo-wrap{width:40px;height:40px;border-radius:8px;overflow:hidden;background:var(--gray100);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--gray200)}
.lainnya-logo-img{width:100%;height:100%;object-fit:cover}
.lainnya-logo-initial{font-size:16px;font-weight:700;color:var(--g700)}
.lainnya-body{flex:1;min-width:0}
.lainnya-jenis{font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--g600)}
.lainnya-nama{font-size:12px;font-weight:600;color:var(--gray900);margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lainnya-arrow{color:var(--gray300);flex-shrink:0;transition:transform .2s}
.lainnya-item:hover .lainnya-arrow{transform:translateX(3px);color:var(--g600)}
</style>
