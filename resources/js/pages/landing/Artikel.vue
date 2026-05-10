<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted, ref, computed } from 'vue';

interface ArtikelItem {
    id: number;
    title: string;
    slug: string;
    penulis: string;
    kategori: string;
    displayDate: string;
    description: string;
    image: string;
}
interface Pagination {
    data: ArtikelItem[];
    current_page: number;
    last_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    artikel?: Pagination;
    kategori_list?: string[];
    aktif_kategori?: string;
    search?: string;
}>();

const searchInput = ref(props.search || '');
const isFiltered  = computed(() => !!props.aktif_kategori || !!props.search);

const cariArtikel = () => {
    router.get('/informasi/artikel', {
        q: searchInput.value || undefined,
        kategori: props.aktif_kategori || undefined,
    }, { preserveScroll: false });
};

const filterKategori = (kat: string | null) => {
    searchInput.value = '';
    router.get('/informasi/artikel', {
        kategori: kat || undefined,
    }, { preserveScroll: false });
};

const goPage = (url: string | null) => {
    if (url) router.get(url, {
        kategori: props.aktif_kategori || undefined,
        q: props.search || undefined,
    }, { preserveScroll: false });
};

const goToPage = (page: number | '...') => {
    if (page === '...') return;
    router.get('/informasi/artikel', {
        page,
        kategori: props.aktif_kategori || undefined,
        q: props.search || undefined,
    }, { preserveScroll: false });
};

const paginationPages = computed(() => {
    const total   = props.artikel?.last_page ?? 1;
    const current = props.artikel?.current_page ?? 1;
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

const allData    = computed(() => props.artikel?.data ?? []);
const featured   = computed(() => allData.value[0] ?? null);
const gridItems  = computed(() => allData.value.slice(1));

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
    <Head title="Artikel - SMK Assalam Gegesik" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <section class="hero">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">

                <!-- HEADER + NAVBAR KATEGORI -->
                <div class="page-header fade-in">
                    <h1 class="page-title">Artikel</h1>
                    <div class="news-navbar">
                        <nav class="nav-cats">
                            <button class="nav-cat" :class="{ active: !aktif_kategori }" @click="filterKategori(null)">HOME</button>
                            <button
                                v-for="kat in (kategori_list ?? [])" :key="kat"
                                class="nav-cat" :class="{ active: aktif_kategori === kat }"
                                @click="filterKategori(kat)"
                            >{{ kat.toUpperCase() }}</button>
                        </nav>
                        <form @submit.prevent="cariArtikel" class="nav-search">
                            <input v-model="searchInput" type="text" placeholder="Cari artikel..." class="nav-search-input" />
                            <button type="submit" class="nav-search-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>



                <template v-if="allData.length > 0">

                    <!-- FEATURED — artikel pertama besar (hanya mode normal) -->
                    <div v-if="!isFiltered && featured && (artikel?.current_page ?? 1) === 1" class="sec-head fade-in">
                        <div class="sec-title-wrap">
                            <span class="sec-bar"></span>
                            <div>
                                <h2 class="sec-title">Semua Artikel</h2>
                                <p class="sec-count">{{ artikel?.total ?? 0 }} artikel tersedia</p>
                            </div>
                        </div>
                    </div>

                    <Link
                        v-if="!isFiltered && featured && (artikel?.current_page ?? 1) === 1"
                        :href="'/informasi/artikel/' + featured.slug"
                        class="featured fade-in"
                    >
                        <div class="feat-img-wrap">
                            <img :src="featured.image" :alt="featured.title" class="feat-img" />
                            <span class="badge">{{ featured.kategori }}</span>
                        </div>
                        <div class="feat-body">
                            <div class="feat-meta">
                                <span class="feat-author">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ featured.penulis }}
                                </span>
                                <time class="feat-date">
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

                    <!-- Section label untuk mode filter -->
                    <div v-if="isFiltered" class="sec-head fade-in">
                        <div class="sec-title-wrap">
                            <span class="sec-bar"></span>
                            <div>
                                <h2 class="sec-title">
                                    <span v-if="aktif_kategori">{{ aktif_kategori }}</span>
                                    <span v-else-if="search">Hasil: "{{ search }}"</span>
                                </h2>
                                <p class="sec-count">{{ artikel?.total ?? 0 }} artikel ditemukan</p>
                            </div>
                        </div>
                        <button class="clear-filter" @click="filterKategori(null)">✕ Hapus filter</button>
                    </div>

                    <!-- GRID artikel -->
                    <div class="grid fade-in">
                        <Link
                            v-for="item in (isFiltered ? allData : gridItems)"
                            :key="item.id"
                            :href="'/informasi/artikel/' + item.slug"
                            class="card"
                        >
                            <div class="card-img-wrap">
                                <img :src="item.image" :alt="item.title" class="card-img" />
                                <span class="badge-abs">{{ item.kategori }}</span>
                            </div>
                            <div class="card-body">
                                <div class="card-meta">
                                    <span class="card-author">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ item.penulis }}
                                    </span>
                                    <time class="card-date">{{ item.displayDate }}</time>
                                </div>
                                <h3 class="card-title">{{ item.title }}</h3>
                                <p class="card-desc">{{ item.description }}</p>
                                <span class="card-link">Baca →</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination numbered -->
                    <div v-if="artikel && artikel.last_page > 1" class="pagination fade-in">
                        <button class="page-btn prev-next" :disabled="!artikel.prev_page_url" @click="goPage(artikel.prev_page_url)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Sebelumnya
                        </button>
                        <div class="page-numbers">
                            <template v-for="(p, i) in paginationPages" :key="i">
                                <span v-if="p === '...'" class="page-ellipsis">...</span>
                                <button v-else class="page-num" :class="{ active: p === artikel.current_page }" @click="goToPage(p)">{{ p }}</button>
                            </template>
                        </div>
                        <button class="page-btn prev-next" :disabled="!artikel.next_page_url" @click="goPage(artikel.next_page_url)">
                            Berikutnya
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                </template>

                <div v-else class="empty fade-in">
                    <svg width="52" height="52" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p>Belum ada artikel yang tersedia.</p>
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

.hero{position:relative;height:220px;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:url('/storage/img/landingpage/cover4.png') center/cover no-repeat}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,40,20,.85),rgba(22,101,52,.65))}
@media(max-width:768px){.hero{height:130px}}

.page-bg{background:var(--gray50);padding:20px 0 80px}
.page-wrap{max-width:1120px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:28px}

/* HEADER */
.page-header{display:flex;flex-direction:column;gap:0}
.page-title{font-family:var(--fd);font-size:clamp(22px,3vw,32px);font-weight:700;color:var(--gray900);margin:0 0 8px}
.news-navbar{display:flex;align-items:center;background:#f3f4f6;border-bottom:2px solid var(--gray200);overflow:hidden}
.nav-cats{display:flex;align-items:stretch;flex:1;overflow-x:auto;scrollbar-width:none}
.nav-cats::-webkit-scrollbar{display:none}
.nav-cat{padding:0 18px;height:46px;font-size:12px;font-weight:700;letter-spacing:.05em;color:var(--gray500);background:transparent;border:none;cursor:pointer;white-space:nowrap;transition:color .2s,background .2s;font-family:var(--fb);border-radius:0;position:relative}
.nav-cat:hover{color:var(--g700);background:rgba(22,101,52,.06)}
.nav-cat.active{color:var(--g700);font-weight:800}
.nav-cat.active::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:var(--g600)}
.nav-search{display:flex;align-items:center;flex-shrink:0;margin-left:auto;border-left:1px solid var(--gray200)}
.nav-search-input{background:transparent;border:none;outline:none;color:var(--gray700);font-size:12px;font-family:var(--fb);padding:8px 12px;width:140px}
.nav-search-input::placeholder{color:var(--gray400)}
.nav-search-btn{background:transparent;border:none;cursor:pointer;color:var(--gray400);padding:10px 12px;transition:color .2s;border-left:1px solid var(--gray200)}
.nav-search-btn:hover{color:var(--g700);background:rgba(22,101,52,.06)}

/* FILTER INFO */
.clear-filter{padding:5px 12px;font-size:11px;font-weight:600;border:1.5px solid var(--gray200);border-radius:0;background:white;color:var(--gray500);cursor:pointer;transition:all .2s;font-family:var(--fb)}
.clear-filter:hover{border-color:var(--g600);color:var(--g700)}

/* SECTION HEADER — garis hijau + judul + count */
.sec-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.sec-title-wrap{display:flex;align-items:center;gap:14px}
.sec-bar{display:block;width:4px;height:36px;background:var(--g600);border-radius:0;flex-shrink:0}
.sec-title{font-family:var(--fd);font-size:clamp(18px,2.5vw,24px);font-weight:700;color:var(--gray900);margin:0;line-height:1.2}
.sec-count{font-size:12px;color:var(--gray400);margin:4px 0 0;font-weight:400}

/* SHARED */
.badge{position:absolute;top:12px;left:12px;background:var(--g600);color:white;font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:3px 10px;border-radius:0}
.badge-abs{position:absolute;top:8px;left:8px;background:var(--g600);color:white;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 8px;border-radius:0}

/* FEATURED */
.featured{display:grid;grid-template-columns:1.1fr 1fr;border-radius:0;overflow:hidden;text-decoration:none;background:white;box-shadow:0 3px 16px rgba(0,0,0,.08);transition:box-shadow .25s,transform .25s}
.featured:hover{box-shadow:0 10px 36px rgba(0,0,0,.14);transform:translateY(-3px)}
@media(max-width:720px){.featured{grid-template-columns:1fr}}
.feat-img-wrap{position:relative;min-height:280px;overflow:hidden}
.feat-img{width:100%;height:100%;object-fit:cover;transition:transform .5s;display:block}
.featured:hover .feat-img{transform:scale(1.04)}
.feat-body{padding:28px;display:flex;flex-direction:column;gap:10px}
.feat-meta{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.feat-author{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:var(--g700);font-weight:600}
.feat-date{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:var(--gray400)}
.feat-title{font-family:var(--fd);font-size:clamp(16px,2.2vw,24px);font-weight:700;color:var(--gray900);line-height:1.3;margin:0}
.feat-desc{font-size:13px;color:var(--gray500);line-height:1.75;margin:0;flex:1;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:4;overflow:hidden}
.read-link{font-size:12px;font-weight:700;color:var(--g700);margin-top:auto}

/* GRID 4 KOLOM */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
@media(max-width:900px){.grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:620px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:400px){.grid{grid-template-columns:1fr}}

.card{display:flex;flex-direction:column;border-radius:0;overflow:hidden;text-decoration:none;background:white;box-shadow:0 2px 10px rgba(0,0,0,.07);transition:box-shadow .2s,transform .2s}
.card:hover{box-shadow:0 10px 32px rgba(0,0,0,.13);transform:translateY(-3px)}
.card-img-wrap{position:relative;height:160px;overflow:hidden}
.card-img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .45s}
.card:hover .card-img{transform:scale(1.06)}
.card-body{padding:14px 16px;display:flex;flex-direction:column;gap:6px;flex:1;border-top:2px solid var(--gray100)}
.card-meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.card-author{display:inline-flex;align-items:center;gap:3px;font-size:10px;color:var(--g700);font-weight:600}
.card-date{font-size:10px;color:var(--gray400);margin-left:auto}
.card-title{font-family:var(--fd);font-size:13px;font-weight:700;color:var(--gray900);line-height:1.35;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.card-desc{font-size:11px;color:var(--gray500);line-height:1.65;margin:0;flex:1;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.card-link{font-size:10px;font-weight:700;color:var(--g700);margin-top:2px}

/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap}
.page-numbers{display:flex;align-items:center;gap:4px}
.prev-next{display:inline-flex;align-items:center;gap:5px;padding:8px 16px;border:1.5px solid var(--gray200);border-radius:0;font-size:12px;font-weight:600;color:var(--gray700);background:white;cursor:pointer;transition:all .2s;font-family:var(--fb);white-space:nowrap}
.prev-next:hover:not(:disabled){border-color:var(--g600);color:var(--g700);background:#f0fdf4}
.prev-next:disabled{opacity:.4;cursor:not-allowed}
.page-num{min-width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid var(--gray200);border-radius:0;font-size:12px;font-weight:600;color:var(--gray700);background:white;cursor:pointer;transition:all .2s;font-family:var(--fb);padding:0 4px}
.page-num:hover{border-color:var(--g600);color:var(--g700);background:#f0fdf4}
.page-num.active{background:var(--g700);border-color:var(--g700);color:white;font-weight:700}
.page-ellipsis{min-width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;color:var(--gray400);border:1.5px solid transparent}

.empty{text-align:center;padding:60px 0;color:var(--gray400);display:flex;flex-direction:column;align-items:center;gap:12px}
.empty p{font-size:13px}
</style>
