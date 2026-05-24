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
    <Head title="Sejarah - SMK As Salam Gegesik" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="page-root">
        <NavUser />

        <section class="cover-section">
            <div class="cover-bg"></div>
            <div class="cover-overlay"></div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">

                <article class="article fade-in" style="margin-top: -70px; position: relative; z-index: 10;">

                    <!-- Breadcrumb -->
                    <nav class="bc-nav">
                        <Link href="/" class="bc-link">Beranda</Link>
                        <span class="bc-sep">&#x203A;</span>
                        <span class="bc-current">Sejarah</span>
                    </nav>

                    <!-- Header -->
                    <div class="article-header">
                        <div class="article-line"></div>
                        <h1 class="article-title">Sejarah SMK As Salam Gegesik</h1>
                        <p class="article-subtitle">Perjalanan Panjang Menuju Pendidikan Berkualitas</p>
                    </div>

                    <!-- Foto -->
                    <div class="article-img-wrap">
                        <img src="/storage/img/landingpage/cover1.png" alt="Gedung SMK As Salam Gegesik" class="article-img" />
                        <p class="article-img-cap">Gedung utama SMK As Salam Gegesik</p>
                    </div>

                    <!-- Body -->
                    <div class="article-body">

                        <!-- Lead -->
                        <p class="article-lead">
                            SMK As Salam Gegesik didirikan pada tahun 2003 oleh Yayasan As Salam Gegesik yang
                            diketuai oleh Bapak Haji Kartana (Alm) dan diusung oleh Bapak H. Andi Kusbandi (Alm),
                            Bapak H. Tohir Madiyas, dan Bapak H. Suyaka (Alm).
                        </p>

                        <!-- Awal Pendirian -->
                        <h3 class="section-title">Awal Pendirian</h3>
                        <p>
                            Awalnya Yayasan As Salam Gegesik membentuk MTs As Salam Gegesik kemudian disusul
                            membentuk Madrasah Aliyah dan TK As Salam Gegesik. Setelah itu barulah kemudian
                            membentuk SMK As Salam Gegesik.
                        </p>
                        <p>
                            SMK As Salam Gegesik pada awal mulanya berdiri hanya memiliki satu jurusan, yaitu
                            <strong>Teknik Kendaraan Ringan Otomotif</strong>. Kemudian sekitar tahun 2008 terdapat
                            penambahan jurusan, yaitu <strong>Teknik Komputer dan Jaringan</strong> hingga sampai saat ini.
                        </p>

                        <!-- KBM Awal -->
                        <h3 class="section-title">Kegiatan Belajar Mengajar Awal</h3>
                        <p>
                            Pada awal berdirinya, Kegiatan Belajar Mengajar (KBM) yang dilaksanakan di SMK As Salam
                            Gegesik pada pukul <strong>13.00 s.d. 16.30 WIB</strong> dengan Kepala Sekolah
                            Bapak H. Andi Kusbandi (Alm). Hal itu dikarenakan keterbatasan tenaga pendidik yang
                            merupakan mayoritas Pegawai Negeri Sipil (PNS) dan hanya bisa mengajar di waktu tersebut.
                        </p>
                        <p>
                            Setelah 6 tahun kemudian, yaitu pada tahun 2009, Kegiatan Belajar Mengajar (KBM) mulai
                            dilakukan di pagi hari dari pukul <strong>07.00 s.d. 13.30 WIB</strong> dengan tenaga
                            pengajar yang sudah mulai stabil.
                        </p>

                        <!-- Estafet Kepemimpinan -->
                        <h3 class="section-title">Estafet Kepemimpinan</h3>
                        <p>
                            Pada Tahun 2011 Bapak H. Andi Kusbandi wafat dan kemudian estafet Kepala Sekolah
                            SMK As Salam Gegesik dilanjutkan oleh <strong>Bapak Didiek Kusdiono, ST.</strong> hingga
                            sampai sekarang. Pada saat itu, beliau merupakan Waka Kurikulum, Pembina OSIS, dan Guru BK.
                        </p>
                        <p>
                            Bapak Didiek Kusdiono sendiri merupakan adik kandung dari Bapak H. Andi Kusbandi (Alm)
                            atau Putra ke-8 dari Bapak H. Kartana (Alm).
                        </p>

                        <!-- Timeline -->
                        <h3 class="section-title">Tonggak Sejarah</h3>
                        <div class="timeline fade-in">
                            <div class="timeline-item">
                                <div class="t-year">2003</div>
                                <div class="t-body">
                                    <h4>Pendirian SMK As Salam Gegesik</h4>
                                    <p>
                                        Didirikan oleh Yayasan As Salam Gegesik yang diketuai Bapak H. Kartana (Alm)
                                        dengan satu jurusan: Teknik Kendaraan Ringan Otomotif.
                                    </p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="t-year">2008</div>
                                <div class="t-body">
                                    <h4>Penambahan Jurusan TKJ</h4>
                                    <p>
                                        Dibuka jurusan Teknik Komputer dan Jaringan (TKJ) seiring meningkatnya
                                        kebutuhan industri teknologi informasi.
                                    </p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="t-year">2009</div>
                                <div class="t-body">
                                    <h4>KBM Pagi Hari</h4>
                                    <p>
                                        Kegiatan Belajar Mengajar dialihkan ke pagi hari pukul 07.00–13.30 WIB
                                        dengan tenaga pengajar yang sudah stabil.
                                    </p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="t-year">2011</div>
                                <div class="t-body">
                                    <h4>Pergantian Kepala Sekolah</h4>
                                    <p>
                                        Bapak Didiek Kusdiono, ST. meneruskan estafet kepemimpinan menggantikan
                                        Bapak H. Andi Kusbandi (Alm) yang wafat.
                                    </p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="t-year">Kini</div>
                                <div class="t-body">
                                    <h4>Terus Berkembang</h4>
                                    <p>
                                        SMK As Salam Gegesik terus berkembang dengan dua program keahlian unggulan
                                        dan berkomitmen mencetak lulusan yang kompeten dan berkarakter.
                                    </p>
                                </div>
                            </div>
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

.page-bg { background: var(--gray-50); padding: 0 24px 72px; }
.page-wrap { max-width: 860px; margin: 0 auto; }

/* ARTIKEL */
.article { background: white; box-shadow: 0 4px 24px rgba(0,0,0,0.10); border: 1px solid var(--gray-100); padding: 36px 44px 52px; }
@media (max-width: 600px) { .article { padding: 24px 18px 40px; } }

/* BREADCRUMB */
.bc-nav { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--gray-100); }
.bc-link { font-size: 13px; color: var(--gray-400); text-decoration: none; transition: color 0.2s; }
.bc-link:hover { color: var(--green-600); }
.bc-sep { font-size: 13px; color: var(--gray-300); }
.bc-current { font-size: 13px; font-weight: 600; color: var(--green-700); }

/* HEADER */
.article-header { margin-bottom: 24px; }
.article-line { width: 40px; height: 3px; background: var(--green-600); border-radius: 2px; margin-bottom: 16px; }
.article-title { font-family: var(--font-display); font-size: clamp(22px, 3.5vw, 34px); font-weight: 700; color: var(--gray-900); line-height: 1.2; margin: 0 0 8px; }
.article-subtitle { font-size: 14px; color: var(--gray-400); margin: 0; }

/* FOTO */
.article-img-wrap { margin-bottom: 28px; border-radius: var(--radius); overflow: hidden; border: 1px solid var(--gray-100); }
.article-img { width: 100%; height: 320px; object-fit: cover; display: block; }
.article-img-cap { font-size: 12px; color: var(--gray-400); text-align: center; padding: 9px 14px; background: var(--gray-50); margin: 0; }

/* BODY */
.article-body { display: flex; flex-direction: column; gap: 16px; }
.article-lead { font-size: 15px; line-height: 1.85; color: var(--gray-700); border-left: 3px solid var(--green-500); padding-left: 16px; font-weight: 500; margin: 0; }
.article-body p { font-size: 14px; line-height: 1.85; color: var(--gray-600); margin: 0; text-align: justify; }
.article-body strong { color: var(--gray-900); font-weight: 700; }
.section-title { font-family: var(--font-display); font-size: 19px; font-weight: 700; color: var(--gray-900); margin: 8px 0 0; padding-top: 12px; border-top: 1px solid var(--gray-100); }

/* TIMELINE */
.timeline { display: flex; flex-direction: column; position: relative; padding-top: 4px; }
.timeline::before { content: ''; position: absolute; left: 50px; top: 0; bottom: 0; width: 2px; background: var(--gray-200); }
.timeline-item { display: grid; grid-template-columns: 100px 1fr; gap: 20px; align-items: flex-start; padding-bottom: 24px; position: relative; }
.timeline-item:last-child { padding-bottom: 0; }
.t-year { font-size: 12px; font-weight: 800; color: var(--green-700); background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 100px; padding: 4px 10px; text-align: center; position: relative; z-index: 1; white-space: nowrap; }
.t-body h4 { font-size: 14px; font-weight: 700; color: var(--gray-900); margin: 2px 0 4px; }
.t-body p { font-size: 13px !important; color: var(--gray-500) !important; line-height: 1.6 !important; margin: 0 !important; text-align: left !important; }

@media (max-width: 540px) {
    .timeline-item { grid-template-columns: 76px 1fr; gap: 14px; }
    .timeline::before { left: 38px; }
    .t-year { font-size: 11px; padding: 3px 8px; }
}
</style>
