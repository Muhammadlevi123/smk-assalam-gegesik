<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted } from 'vue';

interface BeritaDetail {
    id: number;
    title: string;
    slug: string;
    isi: string;
    image: string;
    displayDate: string;
    category: string;
}
interface BeritaTerkait {
    id: number;
    title: string;
    slug: string;
    displayDate: string;
    image: string;
    category: string;
}

const props = defineProps<{
    berita?: BeritaDetail;
    terkait?: BeritaTerkait[];
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
    <Head :title="(berita?.title ?? 'Berita') + ' - SMK Assalam Gegesik'" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <!-- COVER — foto berita sebagai background -->
        <section class="cover-section">
            <div
                class="cover-bg"
                :style="berita?.image ? { backgroundImage: 'url(' + berita.image + ')' } : {}"
            ></div>
            <div class="cover-overlay"></div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">

                <div class="layout fade-in" style="margin-top:-70px;position:relative;z-index:10;">

                    <!-- ARTIKEL UTAMA -->
                    <article class="article">

                        <!-- Breadcrumb -->
                        <nav class="bc-nav">
                            <Link href="/" class="bc-link">Beranda</Link>
                            <span class="bc-sep">&#x203A;</span>
                            <Link href="/informasi/berita" class="bc-link">Berita</Link>
                            <span class="bc-sep">&#x203A;</span>
                            <span class="bc-current">Detail</span>
                        </nav>

                        <template v-if="berita">
                            <!-- Badge kategori -->
                            <span class="cat-badge">{{ berita.category }}</span>

                            <!-- Judul -->
                            <h1 class="article-title">{{ berita.title }}</h1>

                            <!-- Meta -->
                            <div class="article-meta">
                                <div class="meta-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <time>{{ berita.displayDate }}</time>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="article-divider"></div>

                            <!-- Foto utama -->
                            <div class="article-img-wrap">
                                <img :src="berita.image" :alt="berita.title" class="article-img" />
                            </div>

                            <!-- Isi konten rich text -->
                            <div class="article-body" v-html="berita.isi"></div>

                            <!-- Tombol kembali -->
                            <div class="back-wrap">
                                <Link href="/informasi/berita" class="btn-back">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Kembali ke Berita
                                </Link>
                            </div>
                        </template>
                    </article>

                    <!-- SIDEBAR BERITA TERKAIT -->
                    <aside v-if="terkait && terkait.length > 0" class="sidebar fade-in">
                        <div class="sidebar-box">
                            <h3 class="sidebar-title">Berita Terkait</h3>
                            <div class="terkait-list">
                                <Link
                                    v-for="item in terkait"
                                    :key="item.id"
                                    :href="'/informasi/berita/' + item.slug"
                                    class="terkait-item"
                                >
                                    <div class="terkait-img-wrap">
                                        <img :src="item.image" :alt="item.title" class="terkait-img" />
                                    </div>
                                    <div class="terkait-body">
                                        <span class="terkait-cat">{{ item.category }}</span>
                                        <p class="terkait-title">{{ item.title }}</p>
                                        <time class="terkait-date">{{ item.displayDate }}</time>
                                    </div>
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
.cover-section{position:relative;height:340px;overflow:visible}
.cover-bg{position:absolute;inset:0;background:center/cover no-repeat;background-color:#1a3a2a}
.cover-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(10,40,20,.78),rgba(22,101,52,.55))}
@media(max-width:768px){.cover-section{height:240px}}

.page-bg{background:var(--gray50);padding:0 24px 72px}
.page-wrap{max-width:1100px;margin:0 auto}

/* LAYOUT 2 kolom */
.layout{display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start}
@media(max-width:900px){.layout{grid-template-columns:1fr}}

/* ARTIKEL */
.article{background:white;box-shadow:0 4px 24px rgba(0,0,0,.10);border:1px solid var(--gray100);padding:36px 40px 48px;display:flex;flex-direction:column;gap:18px}
@media(max-width:600px){.article{padding:24px 18px 36px}}

.bc-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid var(--gray100)}
.bc-link{font-size:13px;color:var(--gray400);text-decoration:none;transition:color .2s}
.bc-link:hover{color:var(--g600)}.bc-sep{font-size:13px;color:var(--gray300)}
.bc-current{font-size:13px;font-weight:600;color:var(--g700)}

.cat-badge{display:inline-block;background:var(--g600);color:white;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:3px 12px;border-radius:3px}
.article-title{font-family:var(--fd);font-size:clamp(22px,3.5vw,34px);font-weight:700;color:var(--gray900);line-height:1.25;margin:0}
.article-meta{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.meta-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray500)}
.article-divider{height:2px;background:var(--gray100);border-radius:2px}
.article-img-wrap{border-radius:4px;overflow:hidden;border:1px solid var(--gray100)}
.article-img{width:100%;max-height:440px;object-fit:cover;display:block}

/* Isi artikel rich text */
.article-body{font-size:15px;line-height:1.85;color:var(--gray600,#4b5563)}
.article-body :deep(p){margin:0 0 16px;text-align:justify}
.article-body :deep(h2){font-family:var(--fd);font-size:22px;font-weight:700;color:var(--gray900);margin:28px 0 12px}
.article-body :deep(h3){font-family:var(--fd);font-size:18px;font-weight:700;color:var(--gray900);margin:22px 0 10px}
.article-body :deep(ul),.article-body :deep(ol){margin:0 0 16px;padding-left:24px}
.article-body :deep(li){margin-bottom:6px}
.article-body :deep(img){max-width:100%;border-radius:4px;margin:12px 0}
.article-body :deep(a){color:var(--g700);text-decoration:underline}
.article-body :deep(blockquote){border-left:4px solid var(--g500);background:#f0fdf4;padding:14px 18px;border-radius:0 4px 4px 0;margin:16px 0;font-style:italic;color:var(--g800)}

.back-wrap{padding-top:8px;border-top:1px solid var(--gray100)}
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
.terkait-date{font-size:11px;color:var(--gray400)}
</style>
