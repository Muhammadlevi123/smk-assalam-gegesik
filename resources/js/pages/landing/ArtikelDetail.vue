<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted } from 'vue';

interface ArtikelDetail {
    id: number;
    title: string;
    slug: string;
    isi: string;
    penulis: string;
    kategori: string;
    image: string;
    displayDate: string;
}
interface ArtikelTerkait {
    id: number;
    title: string;
    slug: string;
    penulis: string;
    kategori: string;
    displayDate: string;
    image: string;
}

const props = defineProps<{
    artikel?: ArtikelDetail;
    terkait?: ArtikelTerkait[];
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
    <Head :title="(artikel?.title ?? 'Artikel') + ' - SMK Assalam Gegesik'" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <section class="cover-section">
            <div
                class="cover-bg"
                :style="artikel?.image ? { backgroundImage: 'url(' + artikel.image + ')' } : {}"
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
                            <span class="bc-sep">›</span>
                            <Link href="/informasi/artikel" class="bc-link">Artikel</Link>
                            <span class="bc-sep">›</span>
                            <span class="bc-current">Detail</span>
                        </nav>

                        <template v-if="artikel">
                            <!-- Kategori badge -->
                            <span class="cat-badge">{{ artikel.kategori }}</span>

                            <!-- Judul -->
                            <h1 class="article-title">{{ artikel.title }}</h1>

                            <!-- Meta: penulis + tanggal -->
                            <div class="article-meta">
                                <div class="meta-item">
                                    <div>
                                        <p class="author-name">{{ artikel.penulis }}</p>
                                        <time class="author-date">{{ artikel.displayDate }}</time>
                                    </div>
                                </div>
                            </div>

                            <div class="article-divider"></div>

                            <!-- Foto -->
                            <div class="article-img-wrap">
                                <img :src="artikel.image" :alt="artikel.title" class="article-img" />
                            </div>

                            <!-- Isi -->
                            <div class="article-body" v-html="artikel.isi"></div>


                            <!-- Kembali -->
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

                    <!-- SIDEBAR -->
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

        <FooterUser />
    </div>
</template>

<style scoped>
.page-root{
    --g500:#22c55e;--g600:#16a34a;--g700:#15803d;--g800:#166534;
    --gray50:#f9fafb;--gray100:#f3f4f6;--gray200:#e5e7eb;--gray300:#d1d5db;
    --gray400:#9ca3af;--gray500:#6b7280;--gray600:#4b5563;--gray700:#374151;--gray900:#111827;
    --fd:'Fraunces',Georgia,serif;--fb:'Plus Jakarta Sans',sans-serif;
    font-family:var(--fb);color:var(--gray900);min-height:100vh;
}
.fade-in{opacity:0;transform:translateY(20px);transition:opacity .6s ease,transform .6s ease}
.fade-in.visible{opacity:1;transform:none}

.cover-section{position:relative;height:340px;overflow:visible}
.cover-bg{position:absolute;inset:0;background:center/cover no-repeat;background-color:#1a3a2a}
.cover-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(10,40,20,.78),rgba(22,101,52,.55))}
@media(max-width:768px){.cover-section{height:240px}}

.page-bg{background:var(--gray50);padding:0 24px 72px}
.page-wrap{max-width:1100px;margin:0 auto}

.layout{display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start}
@media(max-width:900px){.layout{grid-template-columns:1fr}}

.article{background:white;box-shadow:0 4px 24px rgba(0,0,0,.10);border:1px solid var(--gray100);padding:36px 40px 48px;display:flex;flex-direction:column;gap:18px}
@media(max-width:600px){.article{padding:24px 18px 36px}}

.bc-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid var(--gray100)}
.bc-link{font-size:13px;color:var(--gray400);text-decoration:none;transition:color .2s}
.bc-link:hover{color:var(--g600)}.bc-sep{font-size:13px;color:var(--gray300)}
.bc-current{font-size:13px;font-weight:600;color:var(--g700)}

.cat-badge{display:inline-block;background:var(--g600);color:white;font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:3px 12px;border-radius:0}
.article-title{font-family:var(--fd);font-size:clamp(22px,3.5vw,34px);font-weight:700;color:var(--gray900);line-height:1.25;margin:0}

/* Meta penulis */
.article-meta{display:flex;align-items:center}
.meta-item{display:flex;align-items:center;gap:12px}
.author-avatar{width:40px;height:40px;border-radius:50%;background:var(--g700);color:white;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.author-name{font-size:13px;font-weight:700;color:var(--gray900);margin:0}
.author-date{font-size:11px;color:var(--gray400)}

.article-divider{height:2px;background:var(--gray100)}
.article-img-wrap{border-radius:0;overflow:hidden;border:1px solid var(--gray100)}
.article-img{width:100%;max-height:440px;object-fit:cover;display:block}

/* Isi */
.article-body{font-size:15px;line-height:1.85;color:var(--gray600,#4b5563)}
.article-body :deep(p){margin:0 0 16px;text-align:justify}
.article-body :deep(h2){font-family:var(--fd);font-size:22px;font-weight:700;color:var(--gray900);margin:28px 0 12px}
.article-body :deep(h3){font-family:var(--fd);font-size:18px;font-weight:700;color:var(--gray900);margin:22px 0 10px}
.article-body :deep(ul),.article-body :deep(ol){margin:0 0 16px;padding-left:24px}
.article-body :deep(li){margin-bottom:6px}
.article-body :deep(img){max-width:100%;border-radius:0;margin:12px 0}
.article-body :deep(a){color:var(--g700);text-decoration:underline}
.article-body :deep(blockquote){border-left:4px solid var(--g500);background:#f0fdf4;padding:14px 18px;margin:16px 0;font-style:italic;color:var(--g800)}

/* Author box bawah */
.author-box{display:flex;align-items:center;gap:14px;padding:16px 20px;background:var(--gray50);border:1px solid var(--gray200)}
.author-box-avatar{width:44px;height:44px;border-radius:50%;background:var(--g700);color:white;font-size:18px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.author-box-label{font-size:11px;color:var(--gray400);margin:0}
.author-box-name{font-size:14px;font-weight:700;color:var(--gray900);margin:2px 0 0}

.back-wrap{padding-top:8px;border-top:1px solid var(--gray100)}
.btn-back{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:1.5px solid var(--gray200);border-radius:0;font-size:13px;font-weight:600;color:var(--gray700);text-decoration:none;transition:all .2s}
.btn-back:hover{border-color:var(--g600);color:var(--g700);background:var(--gray50)}

/* SIDEBAR */
.sidebar{position:sticky;top:100px}
.sidebar-box{background:white;border-radius:0;box-shadow:0 2px 12px rgba(0,0,0,.07);border:1px solid var(--gray100);overflow:hidden}
.sidebar-title{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g700);padding:14px 16px 12px;border-bottom:1px solid var(--gray100);margin:0}
.terkait-list{display:flex;flex-direction:column}
.terkait-item{display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid var(--gray100);text-decoration:none;transition:background .15s}
.terkait-item:last-child{border-bottom:none}
.terkait-item:hover{background:var(--gray50)}
.terkait-img-wrap{width:72px;height:56px;flex-shrink:0;overflow:hidden;border-radius:0}
.terkait-img{width:100%;height:100%;object-fit:cover}
.terkait-body{display:flex;flex-direction:column;gap:3px;min-width:0}
.terkait-cat{font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--g600)}
.terkait-title{font-size:12px;font-weight:600;color:var(--gray900);line-height:1.4;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.terkait-meta{display:flex;align-items:center;justify-content:space-between;gap:4px;flex-wrap:wrap}
.terkait-author{font-size:10px;color:var(--g700);font-weight:600}
.terkait-date{font-size:10px;color:var(--gray400)}
</style>
