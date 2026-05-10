<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted } from 'vue';

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                (entry.target as HTMLElement).classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
});
</script>

<template>
    <Head title="Visi & Misi - SMK Assalam Gegesik" />

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
                        <span class="bc-current">Visi &amp; Misi</span>
                    </nav>

                    <!-- Header -->
                    <div class="article-header">
                        <div class="article-line"></div>
                        <h1 class="article-title">Visi &amp; Misi</h1>
                        <p class="article-subtitle">Arah dan Tujuan Pendidikan SMK Assalam Gegesik</p>
                    </div>

                    <!-- Body -->
                    <div class="article-body">

                        <!-- VISI -->
                        <div class="vm-section fade-in">
                            <h2 class="vm-heading">Visi</h2>

                            <p class="article-lead">
                                Visi dikonsepsikan sebagai gambaran menantang tentang keadaan masa depan berisi
                                cita dan citra yang ingin diwujudkan oleh organisasi/lembaga.
                            </p>

                        </div>

                        <!-- Divider -->
                        <div class="vm-divider"></div>

                        <!-- MISI -->
                        <div class="vm-section fade-in">
                            <h2 class="vm-heading">Misi</h2>

                            <p class="vm-misi-intro">
                                Untuk mewujudkan visi tersebut, SMK Assalam Gegesik menjalankan misi sebagai berikut:
                            </p>

                            <ol class="vm-misi-list">
                                <li class="vm-misi-item">
                                    <div class="vm-misi-num">1</div>
                                    <div class="vm-misi-text">
                                        Bimbingan dan konseling di sekolah merupakan komponen atau bagian dari
                                        sistem pendidikan formal di sekolah.
                                    </div>
                                </li>
                                <li class="vm-misi-item">
                                    <div class="vm-misi-num">2</div>
                                    <div class="vm-misi-text">
                                        Program BK merupakan bagian dari program sekolah yang terintegrasi
                                        dengan keseluruhan kegiatan pembelajaran.
                                    </div>
                                </li>
                                <li class="vm-misi-item">
                                    <div class="vm-misi-num">3</div>
                                    <div class="vm-misi-text">
                                        Masing-masing sekolah mempunyai tujuan institusi yang sama sebagai
                                        acuan umum pembangunan dan pengembangan sekolah.
                                    </div>
                                </li>
                                <li class="vm-misi-item">
                                    <div class="vm-misi-num">4</div>
                                    <div class="vm-misi-text">
                                        Masing-masing sekolah memiliki tingkat kualitas, visi, misi, serta
                                        renstra yang disesuaikan dengan kebutuhan dan potensi lokal.
                                    </div>
                                </li>
                                <li class="vm-misi-item">
                                    <div class="vm-misi-num">5</div>
                                    <div class="vm-misi-text">
                                        Dalam merencanakan program BK di sekolah harus mengacu kepada tujuan
                                        institusi, visi, misi, serta renstra sekolah yang telah ditetapkan.
                                    </div>
                                </li>
                            </ol>
                        </div>

                    </div>
                </article>

            </div>
        </div>

        <FooterUser />
    </div>
</template>

<style scoped>
.page-root {
    --green-500: #22c55e;
    --green-600: #16a34a;
    --green-700: #15803d;
    --green-800: #166534;
    --gray-50:  #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-900: #111827;
    --font-display: 'Fraunces', Georgia, serif;
    --font-body: 'Plus Jakarta Sans', sans-serif;
    --radius: 10px;
    --shadow: 0 2px 16px rgba(0,0,0,0.07);
    font-family: var(--font-body);
    color: var(--gray-900);
    min-height: 100vh;
}

.fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
.fade-in.visible { opacity: 1; transform: translateY(0); }

/* COVER */
.cover-section { position: relative; height: 340px; overflow: visible; }
.cover-bg { position: absolute; inset: 0; background: url('/storage/img/landingpage/cover4.png') center/cover no-repeat; }
.cover-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(10,40,20,0.72) 0%, rgba(22,101,52,0.55) 100%); }
@media (max-width: 768px) { .cover-section { height: 220px; } }

/* PAGE BG */
.page-bg { background: var(--gray-50); padding: 0 24px 72px; }
.page-wrap { max-width: 860px; margin: 0 auto; }

/* ARTIKEL */
.article {
    background: white;
    box-shadow: 0 4px 24px rgba(0,0,0,0.10);
    border: 1px solid var(--gray-100);
    padding: 36px 44px 52px;
}
@media (max-width: 600px) { .article { padding: 24px 18px 40px; } }

/* BREADCRUMB */
.bc-nav { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--gray-100); }
.bc-link { font-size: 13px; color: var(--gray-400); text-decoration: none; transition: color 0.2s; }
.bc-link:hover { color: var(--green-600); }
.bc-sep { font-size: 13px; color: var(--gray-300); }
.bc-current { font-size: 13px; font-weight: 600; color: var(--green-700); }

/* HEADER */
.article-header { margin-bottom: 32px; }
.article-line { width: 40px; height: 3px; background: var(--green-600); border-radius: 2px; margin-bottom: 16px; }
.article-title { font-family: var(--font-display); font-size: clamp(22px, 3.5vw, 34px); font-weight: 700; color: var(--gray-900); line-height: 1.2; margin: 0 0 8px; }
.article-subtitle { font-size: 14px; color: var(--gray-400); font-style: italic; margin: 0; }

/* BODY */
.article-body { display: flex; flex-direction: column; gap: 28px; }
.article-lead { font-size: 14px; line-height: 1.85; color: var(--gray-600); border-left: 3px solid var(--green-500); padding-left: 16px; margin: 0; }

/* SECTION HEADING — tanpa icon */
.vm-section { display: flex; flex-direction: column; gap: 18px; }
.vm-heading {
    font-family: var(--font-display);
    font-size: 22px; font-weight: 700;
    color: var(--gray-900); margin: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--green-500);
    display: inline-block;
}

/* VISI BOX */
.vm-visi-box {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1px solid #bbf7d0;
    border-left: 4px solid var(--green-600);
    border-radius: 0 10px 10px 0;
    padding: 22px 24px;
}
.vm-visi-text {
    font-family: var(--font-display); font-style: italic;
    font-size: 17px; color: var(--green-800);
    line-height: 1.75; margin: 0; font-weight: 600;
}

/* DIVIDER */
.vm-divider { height: 1px; background: var(--gray-100); margin: 4px 0; }

/* MISI LIST */
.vm-misi-intro { font-size: 14px; color: var(--gray-600); line-height: 1.75; margin: 0; }
.vm-misi-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.vm-misi-item { display: flex; align-items: flex-start; gap: 14px; }
.vm-misi-num {
    width: 28px; height: 28px; flex-shrink: 0;
    background: var(--green-600); color: white;
    border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 800;
    margin-top: 2px;
}
.vm-misi-text { font-size: 14px; color: var(--gray-600); line-height: 1.8; padding-top: 4px; }
</style>
