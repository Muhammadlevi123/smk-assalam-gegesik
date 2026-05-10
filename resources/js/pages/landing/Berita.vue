<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted, ref, computed } from 'vue';

interface BeritaItem {
    id: number;
    title: string;
    slug: string;
    displayDate: string;
    description: string;
    image: string;
    category: string;
}
interface Pagination {
    data: BeritaItem[];
    current_page: number;
    last_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    terbaru?: BeritaItem[];        // 5 berita terbaru — section 1
    popular?: BeritaItem[];        // 5 berita populer — section 2
    semua_berita?: Pagination;     // semua berita paginated — section 3
    kategori_list?: string[];
    aktif_kategori?: string;
    search?: string;
}>();

const searchInput = ref(props.search || '');

const isFiltered = computed(() => !!props.aktif_kategori || !!props.search);

const cariBerita = () => {
    router.get('/informasi/berita', {
        q: searchInput.value || undefined,
        kategori: props.aktif_kategori || undefined,
    }, { preserveScroll: false });
};

const filterKategori = (kat: string | null) => {
    searchInput.value = '';
    router.get('/informasi/berita', {
        kategori: kat || undefined,
    }, { preserveScroll: false });
};

const goPage = (url: string | null) => {
    if (url) router.get(url, {
        kategori: props.aktif_kategori || undefined,
        q: props.search || undefined,
    }, { preserveScroll: false });
};

// Section 1
const featured  = computed(() => props.terbaru?.[0] ?? null);
const grid4     = computed(() => props.terbaru?.slice(1, 5) ?? []);
// Section 2
const popularList = computed(() => props.popular ?? []);
// Section 3
const semuaData = computed(() => props.semua_berita?.data ?? []);

// Numbered pagination — tampil max 5 angka dengan ellipsis
const paginationPages = computed(() => {
    const total   = props.semua_berita?.last_page ?? 1;
    const current = props.semua_berita?.current_page ?? 1;
    const pages: (number | '...')[] = [];

    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
    } else {
        pages.push(1);
        if (current > 3) pages.push('...');
        const start = Math.max(2, current - 1);
        const end   = Math.min(total - 1, current + 1);
        for (let i = start; i <= end; i++) pages.push(i);
        if (current < total - 2) pages.push('...');
        pages.push(total);
    }
    return pages;
});

const goToPage = (page: number | '...') => {
    if (page === '...' || !props.semua_berita) return;
    const baseUrl = '/informasi/berita';
    router.get(baseUrl, {
        page,
        kategori: props.aktif_kategori || undefined,
        q: props.search || undefined,
    }, { preserveScroll: false });
};

onMounted(() => {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                (e.target as HTMLElement).classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.05 });
    document.querySelectorAll('.fade-in').forEach(el => obs.observe(el));
});
</script>

<template>
    <Head title="Berita - SMK Assalam Gegesik" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <!-- COVER BERSIH -->
        <section class="hero">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">

                <!-- PAGE HEADER ala eKagoz -->
                <div class="page-header fade-in">
                    <!-- Judul halaman -->
                    <h1 class="page-title">Berita &amp; Kegiatan</h1>

                    <!-- Navbar kategori + search -->
                    <div class="news-navbar">

                        <!-- Kategori nav -->
                        <nav class="nav-cats">
                            <button
                                class="nav-cat"
                                :class="{ active: !aktif_kategori }"
                                @click="filterKategori(null)"
                            >HOME</button>
                            <button
                                v-for="kat in (kategori_list ?? [])"
                                :key="kat"
                                class="nav-cat"
                                :class="{ active: aktif_kategori === kat }"
                                @click="filterKategori(kat)"
                            >{{ kat.toUpperCase() }}</button>
                        </nav>

                        <!-- Search -->
                        <form @submit.prevent="cariBerita" class="nav-search">
                            <input
                                v-model="searchInput"
                                type="text"
                                placeholder="Cari..."
                                class="nav-search-input"
                            />
                            <button type="submit" class="nav-search-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ══ SECTION 1: 5 BERITA TERBARU — hanya tampil di mode normal ══ -->
                <div v-if="!isFiltered && terbaru && terbaru.length > 0" class="section fade-in">
                    <div class="sec-head">
                        <h2 class="sec-title">Berita Terbaru</h2>
                    </div>
                    <div class="s1-grid">
                        <!-- Featured besar kiri -->
                        <Link v-if="featured" :href="'/informasi/berita/' + featured.slug" class="feat-card">
                            <div class="feat-img-wrap">
                                <img :src="featured.image" :alt="featured.title" class="feat-img" />
                            </div>
                            <div class="feat-body">
                                <div class="feat-meta">
                                    <span class="badge">{{ featured.category }}</span>
                                    <time class="meta-date">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ featured.displayDate }}
                                    </time>
                                </div>
                                <h2 class="feat-title">{{ featured.title }}</h2>
                                <p class="feat-desc">{{ featured.description }}</p>
                                <span class="read-link">Baca Selengkapnya →</span>
                            </div>
                        </Link>
                        <!-- Grid 2×2 kanan -->
                        <div class="g4-wrap">
                            <Link v-for="item in grid4" :key="item.id"
                                :href="'/informasi/berita/' + item.slug"
                                class="g4-card">
                                <div class="g4-img-wrap">
                                    <img :src="item.image" :alt="item.title" class="g4-img" />
                                    <span class="badge-abs">{{ item.category }}</span>
                                </div>
                                <div class="g4-body">
                                    <time class="meta-date">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ item.displayDate }}
                                    </time>
                                    <h3 class="g4-title">{{ item.title }}</h3>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- ══ SECTION 2: POPULAR 5 KOLOM — hanya tampil di mode normal ══ -->
                <div v-if="!isFiltered && popularList.length > 0" class="section fade-in">
                    <div class="sec-head">
                        <h2 class="sec-title">Berita Terpopuler</h2>
                    </div>
                    <div class="pop-grid">
                        <Link v-for="item in popularList" :key="item.id"
                            :href="'/informasi/berita/' + item.slug"
                            class="pop-card">
                            <div class="pop-img-wrap">
                                <img :src="item.image" :alt="item.title" class="pop-img" />
                                <div class="pop-overlay">
                                    <span class="badge-sm">{{ item.category }}</span>
                                    <time class="pop-date">{{ item.displayDate }}</time>
                                    <h3 class="pop-title">{{ item.title }}</h3>
                                    <span class="pop-link">Baca →</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- ══ SECTION 3: SEMUA BERITA — 4 kolom × 3 baris + pagination ══ -->
                <div class="section fade-in">
                    <div class="sec-head">
                        <div>
                            <h2 class="sec-title">
                                <span v-if="isFiltered && aktif_kategori">{{ aktif_kategori }}</span>
                                <span v-else-if="isFiltered && search">Hasil: "{{ search }}"</span>
                                <span v-else>Semua Berita</span>
                            </h2>
                            <p v-if="semua_berita" class="sec-count">{{ semua_berita.total }} berita</p>
                        </div>
                    </div>

                    <div v-if="semuaData.length > 0" class="fgrid">
                        <Link v-for="item in semuaData" :key="item.id"
                            :href="'/informasi/berita/' + item.slug"
                            class="fcard">
                            <div class="fcard-img-wrap">
                                <img :src="item.image" :alt="item.title" class="fcard-img" />
                            </div>
                            <div class="fcard-body">
                                <span class="fcard-cat">{{ item.category }}</span>
                                <h3 class="fcard-title">{{ item.title }}</h3>
                                <p class="fcard-desc">{{ item.description }}</p>
                                <span class="fcard-link">SELENGKAPNYA</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination numbered -->
                    <div v-if="semua_berita && semua_berita.last_page > 1" class="pagination">
                        <!-- Sebelumnya -->
                        <button
                            class="page-btn prev-next"
                            :disabled="!semua_berita.prev_page_url"
                            @click="goPage(semua_berita.prev_page_url)"
                        >
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Sebelumnya
                        </button>

                        <!-- Nomor halaman -->
                        <div class="page-numbers">
                            <template v-for="(p, i) in paginationPages" :key="i">
                                <span v-if="p === '...'" class="page-ellipsis">...</span>
                                <button
                                    v-else
                                    class="page-num"
                                    :class="{ active: p === semua_berita.current_page }"
                                    @click="goToPage(p)"
                                >{{ p }}</button>
                            </template>
                        </div>

                        <!-- Berikutnya -->
                        <button
                            class="page-btn prev-next"
                            :disabled="!semua_berita.next_page_url"
                            @click="goPage(semua_berita.next_page_url)"
                        >
                            Berikutnya
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <div v-else-if="semuaData.length === 0" class="empty">
                        <svg width="52" height="52" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <p>Belum ada berita.</p>
                    </div>
                </div>

            </div>
        </div>

        <FooterUser />
    </div>
</template>

<style scoped>
.page-root{
    --g500:#22c55e;--g600:#16a34a;--g700:#15803d;--g800:#166534;
    --gray50:#f9fafb;--gray100:#f3f4f6;--gray200:#e5e7eb;--gray300:#d1d5db;
    --gray400:#9ca3af;--gray500:#6b7280;--gray700:#374151;--gray900:#111827;
    --fd:'Fraunces',Georgia,serif;--fb:'Plus Jakarta Sans',sans-serif;
    font-family:var(--fb);color:var(--gray900);min-height:100vh;background:var(--gray50);
}
.fade-in{opacity:0;transform:translateY(16px);transition:opacity .5s ease,transform .5s ease}
.fade-in.visible{opacity:1;transform:none}

/* HERO */
.hero{position:relative;height:220px;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:url('/storage/img/landingpage/cover4.png') center/cover no-repeat}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,40,20,.85),rgba(22,101,52,.65))}
@media(max-width:768px){.hero{height:130px}}

.page-bg{background:var(--gray50);padding:20px 0 80px}
.page-wrap{max-width:1120px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:32px}

/* TOP BAR */
/* PAGE HEADER */
.page-header{display:flex;flex-direction:column;gap:0}
.page-title{font-family:var(--fd);font-size:clamp(22px,3vw,32px);font-weight:700;color:var(--gray900);margin:0 0 8px;padding-bottom:8px;display:inline-block}

/* NAVBAR KATEGORI ala eKagoz */
.news-navbar{
    display:flex;align-items:center;
    background:#f3f4f6;
    border-bottom:2px solid var(--gray200);
    border-radius:0;
    overflow:hidden;
}

/* Tombol hamburger kiri */
.nav-menu-btn{
    display:flex;flex-direction:column;gap:4px;justify-content:center;
    padding:0 16px;height:46px;background:var(--g700);cursor:pointer;flex-shrink:0;
}
.nav-menu-btn span{display:block;width:18px;height:2px;background:white;border-radius:0}

/* Kategori nav */
.nav-cats{display:flex;align-items:stretch;flex:1;overflow-x:auto;scrollbar-width:none}
.nav-cats::-webkit-scrollbar{display:none}
.nav-cat{
    padding:0 18px;height:46px;
    font-size:12px;font-weight:700;letter-spacing:.05em;
    color:var(--gray500);
    background:transparent;border:none;cursor:pointer;
    white-space:nowrap;
    transition:color .2s,background .2s;
    font-family:var(--fb);
    border-radius:0;
    position:relative;
}
.nav-cat:hover{color:var(--g700);background:rgba(22,101,52,.06)}
.nav-cat.active{color:var(--g700);font-weight:800;}
/* Garis bawah aktif */
.nav-cat.active::after{
    content:'';position:absolute;bottom:0;left:0;right:0;
    height:3px;background:var(--g600);
}

/* Search di kanan */
.nav-search{display:flex;align-items:center;flex-shrink:0;margin-left:auto;border-left:1px solid var(--gray200)}
.nav-search-input{
    background:transparent;border:none;outline:none;
    color:var(--gray700);font-size:12px;font-family:var(--fb);
    padding:8px 12px;width:130px;
}
.nav-search-input::placeholder{color:var(--gray400)}
.nav-search-btn{
    background:transparent;border:none;cursor:pointer;
    color:var(--gray400);padding:10px 12px;
    transition:color .2s;border-left:1px solid var(--gray200);
}
.nav-search-btn:hover{color:var(--g700);background:rgba(22,101,52,.06)}

/* SECTION wrapper */
.section{display:flex;flex-direction:column;gap:16px}
.sec-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}
.sec-title{font-family:var(--fd);font-size:18px;font-weight:700;color:var(--gray900);margin:0;padding-left:10px;border-left:3px solid var(--g600)}
.sec-count{font-size:11px;color:var(--gray400);margin:4px 0 0 13px}
.clear-filter{padding:6px 14px;font-size:11px;font-weight:600;border:1.5px solid var(--gray200);border-radius:0;background:white;color:var(--gray500);cursor:pointer;transition:all .2s;font-family:var(--fb);white-space:nowrap}
.clear-filter:hover{border-color:var(--g600);color:var(--g700)}

/* SHARED */
.badge{display:inline-block;background:var(--g600);color:white;font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:3px 10px;border-radius:0}
.badge-abs{position:absolute;top:8px;left:8px;background:var(--g600);color:white;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 8px;border-radius:0}
.badge-sm{display:inline-block;background:var(--g600);color:white;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 8px;border-radius:0}
.meta-date{display:inline-flex;align-items:center;gap:4px;font-size:10px;color:var(--gray400)}
.read-link{font-size:12px;font-weight:700;color:var(--g700)}

/* ══ SECTION 1: featured + 2×2 ══ */
.s1-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:800px){.s1-grid{grid-template-columns:1fr}}

.feat-card{display:flex;flex-direction:column;border-radius:0;overflow:hidden;text-decoration:none;background:white;box-shadow:0 2px 10px rgba(0,0,0,.07);transition:box-shadow .2s,transform .2s}
.feat-card:hover{box-shadow:0 8px 28px rgba(0,0,0,.13);transform:translateY(-2px)}
.feat-img-wrap{height:260px;overflow:hidden}
.feat-img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s}
.feat-card:hover .feat-img{transform:scale(1.04)}
.feat-body{padding:18px 20px;display:flex;flex-direction:column;gap:8px}
.feat-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.feat-title{font-family:var(--fd);font-size:clamp(15px,2vw,20px);font-weight:700;color:var(--gray900);line-height:1.3;margin:0}
.feat-desc{font-size:12px;color:var(--gray500);line-height:1.7;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;overflow:hidden}

.g4-wrap{display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:12px}
.g4-card{display:flex;flex-direction:column;border-radius:0;overflow:hidden;text-decoration:none;background:white;box-shadow:0 2px 8px rgba(0,0,0,.07);transition:box-shadow .2s,transform .2s}
.g4-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.12);transform:translateY(-2px)}
.g4-img-wrap{position:relative;height:110px;overflow:hidden;border-radius:0}
.g4-img{width:100%;height:100%;object-fit:cover;transition:transform .4s;display:block}
.g4-card:hover .g4-img{transform:scale(1.06)}
.g4-body{padding:10px 12px;display:flex;flex-direction:column;gap:4px}
.g4-title{font-family:var(--fd);font-size:12px;font-weight:700;color:var(--gray900);line-height:1.35;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}

/* ══ SECTION 2: popular 5 kolom ══ */
.pop-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
@media(max-width:860px){.pop-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:520px){.pop-grid{grid-template-columns:repeat(2,1fr)}}
.pop-card{display:block;border-radius:0;overflow:hidden;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,.09);transition:box-shadow .2s,transform .2s}
.pop-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.15);transform:translateY(-3px)}
.pop-img-wrap{position:relative;height:180px;overflow:hidden}
.pop-img{width:100%;height:100%;object-fit:cover;transition:transform .4s;display:block}
.pop-card:hover .pop-img{transform:scale(1.06)}
.pop-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85) 0%,rgba(0,0,0,.1) 55%,transparent 100%);padding:10px;display:flex;flex-direction:column;justify-content:flex-end;gap:3px}
.pop-date{font-size:9px;color:rgba(255,255,255,.65)}
.pop-title{font-family:var(--fd);font-size:12px;font-weight:700;color:white;line-height:1.3;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.pop-link{font-size:10px;font-weight:700;color:#86efac}

/* ══ SECTION 3 / FILTER MODE: grid 4 kolom ══ */
.fgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
@media(max-width:900px){.fgrid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:620px){.fgrid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:400px){.fgrid{grid-template-columns:1fr}}

.fcard{display:flex;flex-direction:column;border-radius:0;overflow:hidden;text-decoration:none;background:white;box-shadow:0 2px 10px rgba(0,0,0,.07);transition:box-shadow .2s,transform .2s}
.fcard:hover{box-shadow:0 10px 32px rgba(0,0,0,.13);transform:translateY(-3px)}
.fcard-img-wrap{height:155px;overflow:hidden}
.fcard-img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .45s}
.fcard:hover .fcard-img{transform:scale(1.06)}
.fcard-body{padding:12px 14px;display:flex;flex-direction:column;gap:5px;flex:1;border-top:2px solid var(--gray100)}
.fcard-cat{font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--g600)}
.fcard-title{font-family:var(--fd);font-size:13px;font-weight:700;color:var(--gray900);line-height:1.3;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.fcard-desc{font-size:11px;color:var(--gray500);line-height:1.65;margin:0;flex:1;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.fcard-link{font-size:9px;font-weight:700;color:var(--gray400);letter-spacing:.1em;margin-top:2px;text-transform:uppercase;transition:color .2s}
.fcard:hover .fcard-link{color:var(--g600)}

/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap}
.page-numbers{display:flex;align-items:center;gap:4px}

/* Tombol Sebelumnya / Berikutnya */
.prev-next{
    display:inline-flex;align-items:center;gap:5px;
    padding:8px 16px;
    border:1.5px solid var(--gray200);border-radius:0;
    font-size:12px;font-weight:600;color:var(--gray700);
    background:white;cursor:pointer;transition:all .2s;font-family:var(--fb);
    white-space:nowrap;
}
.prev-next:hover:not(:disabled){border-color:var(--g600);color:var(--g700);background:#f0fdf4}
.prev-next:disabled{opacity:.4;cursor:not-allowed}

/* Nomor halaman */
.page-num{
    min-width:36px;height:36px;
    display:inline-flex;align-items:center;justify-content:center;
    border:1.5px solid var(--gray200);border-radius:0;
    font-size:12px;font-weight:600;color:var(--gray700);
    background:white;cursor:pointer;transition:all .2s;font-family:var(--fb);
    padding:0 4px;
}
.page-num:hover{border-color:var(--g600);color:var(--g700);background:#f0fdf4}
.page-num.active{background:var(--g700);border-color:var(--g700);color:white;font-weight:700}

/* Ellipsis */
.page-ellipsis{
    min-width:36px;height:36px;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:13px;color:var(--gray400);
    border:1.5px solid transparent;
}

/* EMPTY */
.empty{text-align:center;padding:48px 0;color:var(--gray400);display:flex;flex-direction:column;align-items:center;gap:12px}
.empty p{font-size:13px}
</style>
