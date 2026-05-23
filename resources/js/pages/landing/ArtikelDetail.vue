<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted, onUnmounted, ref, computed } from 'vue';

interface ArtikelDetail {
    id:          number;
    title:       string;
    slug:        string;
    isi:         string;
    penulis:     string;
    kategori:    string;
    image:       string;
    images?:     string[];
    displayDate: string;
}
interface ArtikelTerkait {
    id:          number;
    title:       string;
    slug:        string;
    penulis:     string;
    kategori:    string;
    displayDate: string;
    image:       string;
}

const props = defineProps<{
    artikel?: ArtikelDetail;
    terkait?: ArtikelTerkait[];
}>();

// ── Fade-in observer ──────────────────────────────────────────────
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
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

// ── Lightbox ──────────────────────────────────────────────────────
const extraImages = computed(() => props.artikel?.images ?? []);

const allImages = computed(() => {
    const list: string[] = [];
    if (props.artikel?.image) list.push(props.artikel.image);
    list.push(...extraImages.value);
    return list;
});

const lightboxOpen  = ref(false);
const lightboxIndex = ref(0);

const openLightbox = (index: number) => {
    lightboxIndex.value = index;
    lightboxOpen.value  = true;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
};

const prevImage = () => {
    lightboxIndex.value = (lightboxIndex.value - 1 + allImages.value.length) % allImages.value.length;
};

const nextImage = () => {
    lightboxIndex.value = (lightboxIndex.value + 1) % allImages.value.length;
};

const onKeydown = (e: KeyboardEvent) => {
    if (!lightboxOpen.value) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  prevImage();
    if (e.key === 'ArrowRight') nextImage();
};
</script>

<template>
    <Head :title="(artikel?.title ?? 'Artikel') + ' — SMK Assalam Gegesik'" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <!-- COVER -->
        <section class="cover-section">
            <div class="cover-bg" :style="artikel?.image ? { backgroundImage: 'url(' + artikel.image + ')' } : {}"></div>
            <div class="cover-overlay"></div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">
                <div class="layout fade-in" style="margin-top:-70px;position:relative;z-index:10;">

                    <!-- ── ARTIKEL UTAMA ── -->
                    <article class="article">

                        <!-- Breadcrumb -->
                        <nav class="bc-nav">
                            <Link href="/" class="bc-link">Beranda</Link>
                            <span class="bc-sep">&#x203A;</span>
                            <Link href="/informasi/artikel" class="bc-link">Artikel</Link>
                            <span class="bc-sep">&#x203A;</span>
                            <span class="bc-current">Detail</span>
                        </nav>

                        <template v-if="artikel">
                            <!-- Badge kategori -->
                            <span class="cat-badge">{{ artikel.kategori }}</span>

                            <!-- Judul -->
                            <h1 class="article-title">{{ artikel.title }}</h1>

                            <!-- Meta: penulis + tanggal -->
                            <div class="article-meta">
                                <div class="meta-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="author-name">{{ artikel.penulis }}</span>
                                </div>
                                <div class="meta-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <time>{{ artikel.displayDate }}</time>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="article-divider"></div>

                            <!-- Foto utama — klik untuk lightbox -->
                            <div class="article-img-wrap" @click="openLightbox(0)">
                                <img :src="artikel.image" :alt="artikel.title" class="article-img" />
                                <div class="img-overlay">
                                    <svg width="28" height="28" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Isi konten rich text -->
                            <div class="article-body" v-html="artikel.isi"></div>

                            <!-- ── GALERI FOTO TAMBAHAN ── -->
                            <div v-if="extraImages.length > 0" class="gallery-section">
                                <div class="gallery-grid">
                                    <div
                                        v-for="(url, index) in extraImages"
                                        :key="index"
                                        class="gallery-item"
                                        @click="openLightbox(artikel.image ? index + 1 : index)"
                                    >
                                        <img :src="url" :alt="`Foto ${index + 1}`" class="gallery-img" />
                                        <div class="gallery-overlay">
                                            <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol kembali -->
                            <div class="back-wrap">
                                <Link href="/informasi/artikel" class="btn-back">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Kembali ke Artikel
                                </Link>
                            </div>
                        </template>
                    </article>

                    <!-- ── SIDEBAR ── -->
                    <aside v-if="terkait && terkait.length > 0" class="sidebar fade-in">
                        <div class="sidebar-box">
                            <h3 class="sidebar-title">Artikel Terkait</h3>
                            <div class="terkait-list">
                                <Link
                                    v-for="item in terkait" :key="item.id"
                                    :href="'/informasi/artikel/' + item.slug"
                                    class="terkait-item"
                                >
                                    <div class="terkait-img-wrap">
                                        <img :src="item.image" :alt="item.title" class="terkait-img" />
                                    </div>
                                    <div class="terkait-body">
                                        <span class="terkait-cat">{{ item.kategori }}</span>
                                        <p class="terkait-title">{{ item.title }}</p>
                                        <div class="terkait-meta">
                                            <span class="terkait-author">{{ item.penulis }}</span>
                                            <time class="terkait-date">{{ item.displayDate }}</time>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </aside>

                </div>
            </div>
        </div>

        <!-- ── LIGHTBOX ── -->
        <Teleport to="body">
            <div v-if="lightboxOpen"
                class="lightbox-overlay"
                @click.self="closeLightbox">

                <!-- Tutup -->
                <button class="lightbox-close" @click="closeLightbox">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Counter -->
                <div class="lightbox-counter">
                    {{ lightboxIndex + 1 }} / {{ allImages.length }}
                </div>

                <!-- Prev -->
                <button v-if="allImages.length > 1" class="lightbox-nav lightbox-prev" @click="prevImage">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </button>

                <!-- Gambar -->
                <img :src="allImages[lightboxIndex]" :alt="`Foto ${lightboxIndex + 1}`" class="lightbox-img" />

                <!-- Next -->
                <button v-if="allImages.length > 1" class="lightbox-nav lightbox-next" @click="nextImage">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </button>

                <!-- Thumbnail strip -->
                <div v-if="allImages.length > 1" class="lightbox-thumbs">
                    <button
                        v-for="(url, i) in allImages" :key="i"
                        class="lightbox-thumb"
                        :class="{ 'lightbox-thumb-active': i === lightboxIndex }"
                        @click="lightboxIndex = i">
                        <img :src="url" :alt="`Thumb ${i + 1}`" />
                    </button>
                </div>
            </div>
        </Teleport>

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
.cover-section{position:relative;height:340px;overflow:visible}
.cover-bg{position:absolute;inset:0;background:center/cover no-repeat;background-color:#1a3a2a}
.cover-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(10,40,20,.78),rgba(22,101,52,.55))}
@media(max-width:768px){.cover-section{height:240px}}

.page-bg{background:var(--gray50);padding:0 24px 72px}
.page-wrap{max-width:1100px;margin:0 auto}

/* LAYOUT */
.layout{display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start}
@media(max-width:900px){.layout{grid-template-columns:1fr}}

/* ARTIKEL */
.article{background:white;box-shadow:0 4px 24px rgba(0,0,0,.10);border:1px solid var(--gray100);padding:36px 40px 48px;display:flex;flex-direction:column;gap:18px}
@media(max-width:600px){.article{padding:24px 18px 36px}}

/* Breadcrumb */
.bc-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid var(--gray100)}
.bc-link{font-size:13px;color:var(--gray400);text-decoration:none;transition:color .2s}
.bc-link:hover{color:var(--g600)}
.bc-sep{font-size:13px;color:var(--gray300)}
.bc-current{font-size:13px;font-weight:600;color:var(--g700)}

/* Badge & meta */
.cat-badge{display:inline-block;background:var(--g600);color:white;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:3px 12px;border-radius:3px}
.article-title{font-family:var(--fd);font-size:clamp(22px,3.5vw,34px);font-weight:700;color:var(--gray900);line-height:1.25;margin:0}
.article-meta{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.meta-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray500)}
.author-name{font-size:12px;font-weight:600;color:var(--g700)}
.article-divider{height:2px;background:var(--gray100);border-radius:2px}

/* Foto utama */
.article-img-wrap{position:relative;border-radius:4px;overflow:hidden;border:1px solid var(--gray100);cursor:pointer}
.article-img{width:100%;max-height:440px;object-fit:cover;display:block;transition:transform .3s}
.article-img-wrap:hover .article-img{transform:scale(1.02)}
.img-overlay{position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .25s;display:flex;align-items:center;justify-content:center}
.img-overlay svg{opacity:0;transition:opacity .25s;filter:drop-shadow(0 2px 4px rgba(0,0,0,.5))}
.article-img-wrap:hover .img-overlay{background:rgba(0,0,0,.25)}
.article-img-wrap:hover .img-overlay svg{opacity:1}

/* ── RICH TEXT — styling lengkap untuk output Tiptap ── */
.article-body{font-size:15px;line-height:1.85;color:var(--gray600)}

/* Paragraf */
.article-body :deep(p){margin:0 0 16px}
.article-body :deep(p[style*="text-align: left"]){text-align:left}
.article-body :deep(p[style*="text-align: center"]){text-align:center}
.article-body :deep(p[style*="text-align: right"]){text-align:right}
.article-body :deep(p[style*="text-align: justify"]){text-align:justify}

/* Heading */
.article-body :deep(h2){font-family:var(--fd);font-size:22px;font-weight:700;color:var(--gray900);margin:28px 0 12px;line-height:1.3}
.article-body :deep(h3){font-family:var(--fd);font-size:18px;font-weight:700;color:var(--gray900);margin:22px 0 10px;line-height:1.3}
.article-body :deep(h2[style*="text-align: center"]),.article-body :deep(h3[style*="text-align: center"]){text-align:center}
.article-body :deep(h2[style*="text-align: right"]),.article-body :deep(h3[style*="text-align: right"]){text-align:right}

/* Format teks */
.article-body :deep(strong){font-weight:700;color:var(--gray900)}
.article-body :deep(em){font-style:italic}
.article-body :deep(u){text-decoration:underline;text-underline-offset:2px}
.article-body :deep(s){text-decoration:line-through;color:var(--gray400)}

/* List */
.article-body :deep(ul){list-style:disc;margin:0 0 16px;padding-left:24px}
.article-body :deep(ol){list-style:decimal;margin:0 0 16px;padding-left:24px}
.article-body :deep(li){margin-bottom:6px;line-height:1.7}
.article-body :deep(li p){margin:0}

/* Link */
.article-body :deep(a){color:var(--g700);text-decoration:underline;text-underline-offset:2px;transition:color .15s}
.article-body :deep(a:hover){color:var(--g600)}

/* Blockquote */
.article-body :deep(blockquote){border-left:4px solid var(--g500);background:#f0fdf4;padding:14px 18px;border-radius:0 4px 4px 0;margin:16px 0;font-style:italic;color:var(--g800)}
.article-body :deep(blockquote p){margin:0}

/* Gambar di konten */
.article-body :deep(img){max-width:100%;border-radius:4px;margin:12px 0;display:block}

/* Horizontal rule */
.article-body :deep(hr){border:none;border-top:2px solid var(--gray100);margin:24px 0}

/* ── GALERI FOTO TAMBAHAN ── */
.gallery-section{padding-top:4px}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px}
@media(max-width:600px){.gallery-grid{grid-template-columns:repeat(auto-fill,minmax(100px,1fr))}}
.gallery-item{position:relative;aspect-ratio:1;border-radius:6px;overflow:hidden;cursor:pointer;border:1px solid var(--gray200)}
.gallery-img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.gallery-item:hover .gallery-img{transform:scale(1.06)}
.gallery-overlay{position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .2s;display:flex;align-items:center;justify-content:center}
.gallery-overlay svg{opacity:0;transition:opacity .2s;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5))}
.gallery-item:hover .gallery-overlay{background:rgba(0,0,0,.3)}
.gallery-item:hover .gallery-overlay svg{opacity:1}

/* Kembali */
.back-wrap{padding-top:30px;border-top:1px solid var(--gray100);margin-top:12px}
.btn-back{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:1.5px solid var(--gray200);border-radius:4px;font-size:13px;font-weight:600;color:var(--gray700);text-decoration:none;transition:all .2s}
.btn-back:hover{border-color:var(--g600);color:var(--g700);background:var(--gray50)}

/* SIDEBAR */
.sidebar{position:sticky;top:100px}
.sidebar-box{background:white;border-radius:4px;box-shadow:0 2px 12px rgba(0,0,0,.07);border:1px solid var(--gray100);overflow:hidden}
.sidebar-title{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g700);padding:14px 16px 12px;border-bottom:1px solid var(--gray100);margin:0}
.terkait-list{display:flex;flex-direction:column}
.terkait-item{display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid var(--gray100);text-decoration:none;transition:background .15s}
.terkait-item:last-child{border-bottom:none}
.terkait-item:hover{background:var(--gray50)}
.terkait-img-wrap{width:72px;height:56px;flex-shrink:0;border-radius:3px;overflow:hidden}
.terkait-img{width:100%;height:100%;object-fit:cover}
.terkait-body{display:flex;flex-direction:column;gap:3px;min-width:0}
.terkait-cat{font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--g600)}
.terkait-title{font-size:12px;font-weight:600;color:var(--gray900);line-height:1.4;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.terkait-meta{display:flex;align-items:center;justify-content:space-between;gap:4px;flex-wrap:wrap}
.terkait-author{font-size:10px;color:var(--g700);font-weight:600}
.terkait-date{font-size:10px;color:var(--gray400)}

/* ── LIGHTBOX ── */
.lightbox-overlay{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.92);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center}
.lightbox-close{position:absolute;top:16px;right:16px;z-index:10;background:rgba(255,255,255,.12);border:none;color:white;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s}
.lightbox-close:hover{background:rgba(255,255,255,.25)}
.lightbox-counter{position:absolute;top:20px;left:50%;transform:translateX(-50%);z-index:10;background:rgba(0,0,0,.5);color:white;font-size:13px;font-weight:600;padding:4px 14px;border-radius:20px}
.lightbox-nav{position:absolute;z-index:10;background:rgba(255,255,255,.12);border:none;color:white;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s}
.lightbox-nav:hover{background:rgba(255,255,255,.25)}
.lightbox-prev{left:16px}
.lightbox-next{right:16px}
.lightbox-img{max-height:80vh;max-width:88vw;object-fit:contain;border-radius:8px;box-shadow:0 24px 64px rgba(0,0,0,.5);user-select:none}
.lightbox-thumbs{position:absolute;bottom:16px;left:50%;transform:translateX(-50%);display:flex;gap:8px;padding:0 16px;overflow-x:auto;max-width:90vw}
.lightbox-thumb{flex-shrink:0;width:52px;height:52px;border-radius:6px;overflow:hidden;border:2px solid transparent;opacity:.5;cursor:pointer;transition:all .2s;background:none;padding:0}
.lightbox-thumb:hover{opacity:.8}
.lightbox-thumb-active{border-color:white;opacity:1}
.lightbox-thumb img{width:100%;height:100%;object-fit:cover}
</style>
