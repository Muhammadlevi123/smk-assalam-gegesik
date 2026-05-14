<script setup lang="ts">
import { logout } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Guru {
    id: number;
    nama: string;
    mata_pelajaran?: string;
    foto?: string;
}
interface TenagaKependidikan {
    id: number;
    nama: string;
    jabatan?: string;
    foto?: string;
}
interface Berita {
    id: number;
    title: string;
    date: string;
    displayDate: string;
    description: string;
    image: string;
    category: string;
    slug: string;
}
interface Ekstrakurikuler {
    id: number;
    nama: string;
    type: string;
    jenis: string;
    deskripsi?: string;
    logo?: string;
    color: { from: string; to: string; text: string };
}
interface Prestasi {
    id: number;
    nama: string;
    tingkat?: string;
}
interface PrestasiStats {
    internasional: number;
    nasional: number;
    provinsi: number;
    kabupaten: number;
}
interface Statistik {
    total_guru: number;
    total_tenaga_kependidikan: number;
    total_berita: number;
    total_ekstrakurikuler: number;
    total_prestasi: number;
    tahun_ajaran_terbaru: string;
    prestasi_stats: PrestasiStats;
}
interface Teacher {
    id: string;
    name: string;
    position: string;
    image: string;
}

const props = defineProps<{
    guru?: Guru[];
    tenaga_kependidikan?: TenagaKependidikan[];
    berita?: Berita[];
    ekstrakurikuler?: Ekstrakurikuler[];
    prestasi?: Prestasi[];
    statistik?: Statistik;
}>();

// ─── Contact Form ─────────────────────────────────────────────────────────────
const contactForm = useForm({ name: '', email: '', phone: '', message: '' });
const contactFormErrors = ref<Record<string, string[]>>({});
const showSuccessNotification = ref(false);

const scrollToAbout = () => {
    document.getElementById('sambutan-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const submitContactForm = () => {
    contactForm.post('/contact-message', {
        preserveScroll: true,
        onSuccess: () => {
            contactForm.reset();
            contactFormErrors.value = {};
            showSuccessNotification.value = true;
        },
        onError: (errors: any) => { contactFormErrors.value = errors; }
    });
};

const closeSuccessNotification = () => { showSuccessNotification.value = false; };
const getErrorMessage = (field: string): string => {
    const err = contactFormErrors.value[field];
    if (!err) return '';
    return Array.isArray(err) ? err[0] : String(err);
};
const hasError = (field: string): boolean => {
    const err = contactFormErrors.value[field];
    if (!err) return false;
    const msg = Array.isArray(err) ? err[0] : String(err);
    return !!msg;
};

// ─── Hero Carousel ────────────────────────────────────────────────────────────
const schoolInfo = ref({
    name: "SMK Assalam Gegesik",
    tagline: "Membentuk Generasi Unggul, Berkarakter, dan Berdaya Saing Global"
});

const heroImages = ref([
    { url: '/storage/img/landingpage/cover1.png' },
    { url: '/storage/img/landingpage/cover2.png' },
    { url: '/storage/img/landingpage/cover3.png' },
    { url: '/storage/img/landingpage/cover4.png' },
]);

const currentImageIndex = ref(0);
let heroInterval: number | null = null;

const startHeroCarousel = () => {
    heroInterval = setInterval(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % heroImages.value.length;
    }, 5000);
};
const stopHeroCarousel = () => {
    if (heroInterval) { clearInterval(heroInterval); heroInterval = null; }
};

// ─── Teacher Carousel ─────────────────────────────────────────────────────────
const currentTeacherIndex = ref(0);
const isDragging = ref(false);
const startX = ref(0);
const currentTranslateX = ref(0);
const dragOffset = ref(0);
const containerWidth = ref(0);
let teacherCarouselInterval: number | null = null;

const teachers = computed((): Teacher[] => {
    const list: Teacher[] = [];
    props.guru?.forEach(g => list.push({
        id: `guru-${g.id}`,
        name: g.nama,
        position: g.mata_pelajaran?.toUpperCase() || 'GURU',
        image: g.foto ? `/storage/${g.foto}` : `/storage/img/logo/logo.png`
    }));
    props.tenaga_kependidikan?.forEach(t => list.push({
        id: `tenaga-${t.id}`,
        name: t.nama,
        position: t.jabatan?.toUpperCase() || 'STAF',
        image: t.foto ? `/storage/${t.foto}` : `/storage/img/logo/logo.png`
    }));
    return list;
});

const isMobile = () => window.innerWidth < 768;
const slidesVisible = () => isMobile() ? 1 : 4;
const slidePercent = () => 100 / slidesVisible();
const maxTeacherIndex = computed(() => Math.max(0, teachers.value.length - slidesVisible()));

const updateCurrentTranslateX = () => {
    currentTranslateX.value = currentTeacherIndex.value * -slidePercent();
};
const clampTranslate = (val: number) => {
    const min = maxTeacherIndex.value * -slidePercent();
    return Math.max(min, Math.min(0, val));
};
const teacherTransform = computed(() => `translateX(${currentTranslateX.value + dragOffset.value}%)`);

const goToPreviousTeacher = () => {
    currentTeacherIndex.value = Math.max(0, currentTeacherIndex.value - 1);
    updateCurrentTranslateX();
};
const goToNextTeacher = () => {
    currentTeacherIndex.value = Math.min(maxTeacherIndex.value, currentTeacherIndex.value + 1);
    updateCurrentTranslateX();
};

const startTeacherCarousel = () => {
    if (teachers.value.length <= slidesVisible()) return;
    teacherCarouselInterval = setInterval(() => {
        if (!isDragging.value) {
            currentTeacherIndex.value = currentTeacherIndex.value >= maxTeacherIndex.value
                ? 0 : currentTeacherIndex.value + 1;
            updateCurrentTranslateX();
        }
    }, 6000);
};
const stopTeacherCarousel = () => {
    if (teacherCarouselInterval) { clearInterval(teacherCarouselInterval); teacherCarouselInterval = null; }
};

const handleDragStart = (e: MouseEvent | TouchEvent) => {
    isDragging.value = true;
    stopTeacherCarousel();
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    startX.value = clientX;
    dragOffset.value = 0;
    const container = (e.target as HTMLElement).closest('.teacher-overflow');
    if (container) containerWidth.value = container.clientWidth;
    e.preventDefault();
};
const handleDragMove = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const deltaPct = ((clientX - startX.value) / containerWidth.value) * 100 * slidesVisible();
    dragOffset.value = clampTranslate(currentTranslateX.value + deltaPct) - currentTranslateX.value;
    e.preventDefault();
};
const handleDragEnd = () => {
    if (!isDragging.value) return;
    const finalTranslate = currentTranslateX.value + dragOffset.value;
    const slideIndex = Math.round(Math.abs(finalTranslate) / slidePercent());
    currentTeacherIndex.value = Math.max(0, Math.min(maxTeacherIndex.value, slideIndex));
    dragOffset.value = 0;
    updateCurrentTranslateX();
    isDragging.value = false;
    startTeacherCarousel();
};

const handleResize = () => {
    if (currentTeacherIndex.value > maxTeacherIndex.value)
        currentTeacherIndex.value = maxTeacherIndex.value;
    updateCurrentTranslateX();
};

// ─── Scroll Reveal ────────────────────────────────────────────────────────────
const setupScrollReveal = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target as HTMLElement;
                el.classList.add('revealed');

                // Stagger children jika parent punya class stagger-children
                if (el.classList.contains('stagger-children')) {
                    const children = el.querySelectorAll(':scope > *');
                    children.forEach((child, i) => {
                        (child as HTMLElement).style.setProperty('--stagger-i', String(i));
                        (child as HTMLElement).classList.add('stagger-item');
                    });
                }

                observer.unobserve(el);
            }
        });
    }, { threshold: 0.06, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll(
        '.reveal, .reveal-left, .reveal-right, .reveal-up, .reveal-zoom, .stagger-children'
    ).forEach(el => observer.observe(el));
};

onMounted(() => {
    startHeroCarousel();
    startTeacherCarousel();
    updateCurrentTranslateX();
    window.addEventListener('resize', handleResize);
    window.addEventListener('mouseup', handleDragEnd);
    setTimeout(setupScrollReveal, 100);
});
onUnmounted(() => {
    stopHeroCarousel();
    stopTeacherCarousel();
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('mouseup', handleDragEnd);
});

const formatPosition = (pos: string) => pos.length > 30 ? pos.substring(0, 30) + '…' : pos;

// ─── Ekskul color palette (untuk border top saja) ────────────────────────────
const ekskurPalette = [
    '#16a34a', '#0d9488', '#2563eb', '#9333ea',
    '#dc2626', '#d97706', '#0891b2', '#65a30d',
    '#e11d48', '#7c3aed',
];
const getEkskurColor = (index: number) => ekskurPalette[index % ekskurPalette.length];
</script>

<template>
    <Head :title="`${schoolInfo.name} - Home`">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />
    </Head>

    <div class="smk-root">
        <NavUser />

        <!-- ════════════════════════════════════════════
             HERO — Full-bleed, tanpa overlay hijau
        ═════════════════════════════════════════════ -->
        <section class="hero-section">
            <!-- Slides -->
            <div class="hero-bg">
                <div
                    v-for="(image, index) in heroImages"
                    :key="index"
                    class="hero-slide"
                    :class="{ active: index === currentImageIndex }"
                >
                    <img :src="image.url" class="hero-img" alt="" />
                    <!-- overlay hitam tipis saja, tanpa warna hijau -->
                    <div class="hero-overlay"></div>
                </div>
            </div>

            <!-- Thin top accent line -->
            <div class="hero-top-bar"></div>

            <!-- Content -->
            <div class="hero-content">
                <div class="hero-inner">

                    <h1 class="hero-title hero-anim-2">
                        Selamat Datang di<br>
                        <span class="hero-title-accent">SMK Assalam</span>
                    </h1>

                    <p class="hero-sub hero-anim-3">{{ schoolInfo.tagline }}</p>

                    <!-- Garis dekoratif -->
                    <div class="hero-divider hero-anim-4">
                        <span class="hero-divider-line"></span>
                        <span class="hero-divider-dot"></span>
                        <span class="hero-divider-line"></span>
                    </div>

                    <div class="hero-actions hero-anim-5">
                        <button @click="scrollToAbout" class="btn-hero-primary">
                            Pelajari Lebih Lanjut
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dot indicators -->
            <div class="hero-indicators">
                <button
                    v-for="(_, idx) in heroImages"
                    :key="idx"
                    @click="currentImageIndex = idx; stopHeroCarousel(); startHeroCarousel();"
                    class="hero-dot"
                    :class="{ active: idx === currentImageIndex }"
                ></button>
            </div>

            <!-- Arrows -->
            <button class="hero-arrow hero-arrow-left"
                @click="currentImageIndex = (currentImageIndex - 1 + heroImages.length) % heroImages.length">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button class="hero-arrow hero-arrow-right"
                @click="currentImageIndex = (currentImageIndex + 1) % heroImages.length">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Scroll hint -->
            <div class="hero-scroll-hint">
                <div class="scroll-line"></div>
                <span>Scroll</span>
            </div>
        </section>

        <!-- ════════════════════════════════════════════
             SAMBUTAN KEPALA SEKOLAH
        ═════════════════════════════════════════════ -->
        <section id="sambutan-section" class="sambutan-section">
            <div class="section-container">
                <div class="sambutan-grid">
                    <!-- Photo -->
                    <div class="sambutan-photo-col reveal-left">
                        <div class="photo-frame">
                            <div class="photo-accent"></div>
                            <div class="photo-wrapper">
                                <img
                                    src="/storage/img/landingpage/kepalasekolah1.png"
                                    alt="Kepala Sekolah"
                                    class="kepala-photo"
                                />
                            </div>
                            <div class="photo-namecard">
                                <p class="namecard-name">Didiek Kusdiono, ST</p>
                                <p class="namecard-role">Kepala Sekolah</p>
                            </div>
                        </div>
                    </div>

                    <!-- Text -->
                    <div class="sambutan-text-col reveal-right">
                        <div class="section-label">Sambutan</div>
                        <h2 class="section-title">Kepala Sekolah</h2>
                        <div class="section-underline"></div>

                        <p class="sambutan-quote">"Assalamu'alaikum Warahmatullahi Wabarakatuh"</p>

                        <div class="sambutan-body">
                            <p>Segala puji bagi Allah Subhanahu wa Ta'ala yang senantiasa melimpahkan rahmat dan
                            hidayah-Nya kepada kita semua. Shalawat serta salam semoga tercurah kepada Nabi
                            Muhammad Shallallahu 'Alaihi Wasallam beserta keluarga dan sahabatnya.</p>

                            <p>Website <strong>{{ schoolInfo.name }}</strong> hadir sebagai media informasi dan
                            komunikasi bagi seluruh warga sekolah serta masyarakat luas. Melalui website ini,
                            kami berupaya memberikan layanan informasi yang cepat, transparan, dan bermanfaat,
                            sekaligus mendukung perkembangan pendidikan di era digital.</p>

                            <p>Kami berharap website ini dapat menjadi sarana yang positif, sekaligus mengundang
                            partisipasi semua pihak untuk terus memberikan kritik dan saran demi kemajuan
                            <strong>{{ schoolInfo.name }}</strong>.</p>
                        </div>

                        <p class="sambutan-quote">"Wassalamu'alaikum Warahmatullahi Wabarakatuh"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════
             TENAGA PENDIDIK — Tanpa accent warna
        ═════════════════════════════════════════════ -->
        <section class="teacher-section">
            <div class="section-container">
                <div class="section-header reveal-up">
                    <div class="section-label">Personalia</div>
                    <h2 class="section-title">Kependidikan &amp; Tata Usaha</h2>
                    <div class="section-underline centered"></div>
                </div>

                <div class="teacher-carousel-wrapper reveal-up">
                    <button class="carousel-nav carousel-nav-prev"
                        @click="goToPreviousTeacher"
                        :disabled="currentTeacherIndex === 0">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div class="teacher-overflow">
                        <div
                            class="teacher-track"
                            :style="{ transform: teacherTransform }"
                            :class="{ 'no-transition': isDragging }"
                            @mousedown="handleDragStart"
                            @mousemove="handleDragMove"
                            @mouseleave="handleDragEnd"
                            @touchstart="handleDragStart"
                            @touchmove="handleDragMove"
                            @touchend="handleDragEnd"
                        >
                            <div
                                v-for="teacher in teachers"
                                :key="teacher.id"
                                class="teacher-slide"
                                :class="{ 'pointer-events-none': isDragging }"
                            >
                                <div class="teacher-card">
                                    <div class="teacher-photo-wrap">
                                        <img
                                            :src="teacher.image"
                                            :alt="teacher.name"
                                            class="teacher-photo"
                                            :class="teacher.image.includes('logo.png') ? 'logo-mode' : ''"
                                            draggable="false"
                                        />
                                    </div>
                                    <div class="teacher-info">
                                        <p class="teacher-name">{{ teacher.name }}</p>
                                        <p class="teacher-pos">{{ formatPosition(teacher.position) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="carousel-nav carousel-nav-next"
                        @click="goToNextTeacher"
                        :disabled="currentTeacherIndex >= maxTeacherIndex">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Dot indicator -->
                <div class="teacher-dots">
                    <button
                        v-for="i in (maxTeacherIndex + 1)"
                        :key="i"
                        class="teacher-dot"
                        :class="{ active: currentTeacherIndex === i - 1 }"
                        @click="currentTeacherIndex = i - 1; updateCurrentTranslateX()"
                    ></button>
                </div>

                <div class="section-cta">
                    <Link href="/profil/tenaga-pendidik" class="btn-outline">
                        Lihat Semua
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════
             BERITA & KEGIATAN — Layout fixed height
        ═════════════════════════════════════════════ -->
        <section class="news-section">
            <div class="section-container">
                <div class="news-header reveal-up">
                    <div>
                        <div class="section-label">Pembaruan Terkini</div>
                        <h2 class="section-title">Berita &amp; Kegiatan</h2>
                        <div class="section-underline"></div>
                    </div>
                    <Link href="/informasi/berita" class="news-see-all">
                        Lihat Semua
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>

                <div v-if="berita && berita.length > 0" class="news-grid">

                    <!-- Featured — kiri, full height -->
                    <Link
                        v-if="berita[0]"
                        :href="`/news/${berita[0].slug}`"
                        class="news-featured reveal-left"
                    >
                        <div class="news-featured-img-wrap">
                            <img :src="berita[0].image" :alt="berita[0].title" class="news-img" />
                            <div class="news-img-overlay"></div>
                            <span class="news-category-badge">{{ berita[0].category || 'Berita' }}</span>
                        </div>
                        <div class="news-featured-body">
                            <time class="news-date">{{ berita[0].displayDate }}</time>
                            <h3 class="news-featured-title">{{ berita[0].title }}</h3>
                            <p class="news-featured-desc">{{ berita[0].description }}</p>
                            <span class="news-read-more">
                                Baca Selengkapnya
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </div>
                    </Link>

                    <!-- Side list — kanan, same total height -->
                    <div class="news-side reveal-right">
                        <Link
                            v-for="(item, idx) in berita.slice(1, 5)"
                            :key="item.id"
                            :href="`/news/${item.slug}`"
                            class="news-side-item"
                            :style="{ '--side-i': idx }"
                        >
                            <div class="news-side-img-wrap">
                                <img :src="item.image" :alt="item.title" class="news-side-img" />
                            </div>
                            <div class="news-side-body">
                                <time class="news-date">{{ item.displayDate }}</time>
                                <h4 class="news-side-title">{{ item.title }}</h4>
                                <span class="news-read-more-sm">
                                    Baca
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="empty-state reveal">
                    <div class="empty-icon">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3>Belum Ada Berita</h3>
                    <p>Berita dan kegiatan akan ditampilkan di sini ketika tersedia.</p>
                </div>

                <div class="md-hidden-cta">
                    <Link href="/informasi/berita" class="btn-outline">Lihat Semua Berita</Link>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════
             EKSTRAKURIKULER — Pakai logo dari DB
        ═════════════════════════════════════════════ -->
        <section class="ekskur-section">
            <div class="section-container">
                <div class="section-header reveal-up">
                    <div class="section-label">Lingkungan Sekolah</div>
                    <h2 class="section-title">Kegiatan Eksternal Siswa</h2>
                    <div class="section-underline centered"></div>
                    <p class="section-desc">Wadah pengembangan bakat, minat, dan karakter siswa di luar kegiatan akademik.</p>
                </div>

                <div v-if="ekstrakurikuler && ekstrakurikuler.length > 0" class="ekskur-grid stagger-children">
                    <div
                        v-for="(item, index) in ekstrakurikuler"
                        :key="item.id"
                        class="ekskur-card stagger-item"
                        :style="{ '--ekskur-color': getEkskurColor(index), '--stagger-i': index }"
                    >
                        <div class="ekskur-top">
                            <!-- Logo dari DB: jika ada tampilkan gambar, jika tidak pakai initial -->
                            <div class="ekskur-logo-wrap">
                                <img
                                    v-if="item.logo"
                                    :src="item.logo"
                                    :alt="item.nama"
                                    class="ekskur-logo-img"
                                />
                                <span v-else class="ekskur-initial">{{ item.nama.charAt(0).toUpperCase() }}</span>
                            </div>
                            <span class="ekskur-badge">{{ item.jenis }}</span>
                        </div>
                        <div class="ekskur-body">
                            <h3 class="ekskur-name">{{ item.nama }}</h3>
                            <p class="ekskur-desc">
                                {{ item.deskripsi || 'Deskripsi kegiatan ekstrakurikuler ini akan segera tersedia.' }}
                            </p>
                        </div>
                        <div class="ekskur-footer">
                            <Link href="/ekstrakurikuler" class="ekskur-link">
                                Selengkapnya
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state reveal">
                    <div class="empty-icon">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3>Belum Ada Ekstrakurikuler</h3>
                    <p>Data akan ditampilkan segera.</p>
                </div>

                <!-- <div v-if="ekstrakurikuler && ekstrakurikuler.length > 0" class="section-cta">
                    <Link href="/ekstrakurikuler" class="btn-outline">
                        Lihat Semua Ekstrakurikuler
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div> -->
            </div>
        </section>

        <!-- ════════════════════════════════════════════
             PRESTASI — Icon berdasarkan tingkatan
        ═════════════════════════════════════════════ -->
        <section class="prestasi-section">
            <div class="prestasi-bg-pattern"></div>
            <div class="section-container prestasi-inner">
                <div class="prestasi-heading reveal-up">
                    <div class="section-label light">Pencapaian Kami</div>
                    <h2 class="section-title light">Prestasi Sekolah</h2>
                    <p class="prestasi-sub">Bukti nyata dedikasi siswa dan guru SMK Assalam Gegesik</p>
                </div>

                <div class="prestasi-grid stagger-children">

                    <!-- Internasional — globe / dunia -->
                    <div class="prestasi-card stagger-item" style="--stagger-i:0">
                        <div class="prestasi-icon-wrap">
                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div class="prestasi-num">{{ statistik?.prestasi_stats?.internasional || 0 }}</div>
                        <div class="prestasi-label">Internasional</div>
                        <div class="prestasi-desc">Tingkat Dunia</div>
                    </div>

                    <!-- Nasional — bendera / peta -->
                    <div class="prestasi-card stagger-item" style="--stagger-i:1">
                        <div class="prestasi-icon-wrap">
                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                                <line x1="4" y1="22" x2="4" y2="15"/>
                            </svg>
                        </div>
                        <div class="prestasi-num">{{ statistik?.prestasi_stats?.nasional || 0 }}</div>
                        <div class="prestasi-label">Nasional</div>
                        <div class="prestasi-desc">Tingkat Indonesia</div>
                    </div>

                    <!-- Provinsi — peta regional -->
                    <div class="prestasi-card stagger-item" style="--stagger-i:2">
                        <div class="prestasi-icon-wrap">
                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <div class="prestasi-num">{{ statistik?.prestasi_stats?.provinsi || 0 }}</div>
                        <div class="prestasi-label">Provinsi</div>
                        <div class="prestasi-desc">Tingkat Jawa Barat</div>
                    </div>

                    <!-- Kabupaten/Kota — gedung / bangunan kota -->
                    <div class="prestasi-card stagger-item" style="--stagger-i:3">
                        <div class="prestasi-icon-wrap">
                            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0H5m0 0H3m2 0V9a2 2 0 012-2h6a2 2 0 012 2v12M9 21v-6a1 1 0 011-1h4a1 1 0 011 1v6"/>
                            </svg>
                        </div>
                        <div class="prestasi-num">{{ statistik?.prestasi_stats?.kabupaten || 0 }}</div>
                        <div class="prestasi-label">Kabupaten/Kota</div>
                        <div class="prestasi-desc">Tingkat Cirebon</div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════
             CONTACT
        ═════════════════════════════════════════════ -->
        <section class="contact-section" >
            <div class="section-container">
                <div class="section-header reveal-up">
                    <div class="section-label">Terhubung</div>
                    <h2 class="section-title">Hubungi Kami</h2>
                    <div class="section-underline centered"></div>
                </div>

                <div class="contact-grid">
                    <!-- Map -->
                    <div class="contact-map-col reveal-left">
                        <h3 class="contact-col-title">Lokasi Kami</h3>
                        <p class="contact-col-sub">Temukan kami di peta atau kunjungi langsung sekolah kami.</p>
                        <div class="map-frame">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.999999999999!2d108.41914140000001!3d-6.6012878!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6edd3b9d4d9ca7%3A0x5f1f07209694bcc5!2sSMK%20AS%20SALAM%20GEGESIK!5e0!3m2!1sid!2sid!4v1620000000000!5m2!1sid!2sid"
                                width="100%" height="100%" style="border:0;" :allowfullscreen="true" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Lokasi SMK AS SALAM GEGESIK"
                            ></iframe>
                        </div>
                        <div class="contact-pills">
                            <div class="contact-pill">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Gegesik, Cirebon, Jawa Barat
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="contact-form-col reveal-right">
                        <h3 class="contact-col-title">Kirim Pesan</h3>
                        <p class="contact-col-sub">Sampaikan kritik dan saran untuk bersama memajukan pendidikan SMK Assalam.</p>

                        <div v-if="contactFormErrors.general" class="form-error-banner">
                            {{ contactFormErrors.general[0] }}
                        </div>

                        <form @submit.prevent="submitContactForm" class="contact-form">
                            <div class="form-row">
                                <div class="form-group" :class="{ error: hasError('name') }">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" v-model="contactForm.name" class="form-input"
                                        placeholder="Nama Anda" required :disabled="contactForm.processing"/>
                                    <p v-if="hasError('name')" class="form-error-msg">{{ getErrorMessage('name') }}</p>
                                </div>
                                <div class="form-group" :class="{ error: hasError('email') }">
                                    <label class="form-label">Email</label>
                                    <input type="email" v-model="contactForm.email" class="form-input"
                                        placeholder="email@contoh.com" required :disabled="contactForm.processing"/>
                                    <p v-if="hasError('email')" class="form-error-msg">{{ getErrorMessage('email') }}</p>
                                </div>
                            </div>

                            <div class="form-group" :class="{ error: hasError('phone') }">
                                <label class="form-label">No. Telepon <span class="form-optional">(opsional)</span></label>
                                <input type="tel" v-model="contactForm.phone" class="form-input"
                                    placeholder="08xx-xxxx-xxxx" :disabled="contactForm.processing"/>
                                <p v-if="hasError('phone')" class="form-error-msg">{{ getErrorMessage('phone') }}</p>
                            </div>

                            <div class="form-group" :class="{ error: hasError('message') }">
                                <label class="form-label">Pesan</label>
                                <textarea v-model="contactForm.message" class="form-textarea" rows="5"
                                    placeholder="Tulis pesan, kritik, atau saran Anda di sini..."
                                    required :disabled="contactForm.processing"></textarea>
                                <p v-if="hasError('message')" class="form-error-msg">{{ getErrorMessage('message') }}</p>
                            </div>

                            <button type="submit" class="btn-submit" :disabled="contactForm.processing">
                                <span v-if="contactForm.processing" class="btn-loading">
                                    <svg class="spin" width="18" height="18" fill="none" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                        <path fill="currentColor" class="opacity-75"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Mengirim...
                                </span>
                                <span v-else class="btn-send-label">
                                    Kirim Pesan
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                    </svg>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ganti bagian <Teleport> di template Vue kalian dengan ini -->

<Teleport to="body">
    <Transition name="modal">
        <div
            v-if="showSuccessNotification"
            @click="closeSuccessNotification"
            style="
                position: fixed !important;
                inset: 0 !important;
                background: rgba(0,0,0,0.55) !important;
                backdrop-filter: blur(4px) !important;
                -webkit-backdrop-filter: blur(4px) !important;
                z-index: 99999 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 24px !important;
            "
        >
            <div
                @click.stop
                style="
                    background: #ffffff !important;
                    border-radius: 0 !important;
                    padding: 44px 36px !important;
                    max-width: 420px !important;
                    width: 100% !important;
                    text-align: center !important;
                    box-shadow: 0 24px 60px rgba(0,0,0,0.3) !important;
                    position: relative !important;
                    z-index: 100000 !important;
                "
            >

                <!-- Judul -->
                <h3 style="
                    font-family: 'Fraunces', Georgia, serif;
                    font-size: 21px;
                    font-weight: 700;
                    color: #111827;
                    margin: 0 0 10px;
                ">Pesan Berhasil Dikirim!</h3>

                <!-- Body -->
                <p style="
                    font-size: 14px;
                    line-height: 1.75;
                    color: #6b7280;
                    margin: 0 0 24px;
                ">
                    Terima kasih atas kritik dan saran Anda. Kami akan terus memperbaiki diri demi kemajuan SMK Assalam Gegesik.
                </p>

                <!-- Tombol tutup -->
                <button
                    @click="closeSuccessNotification"
                    style="
                        padding: 11px 28px;
                        background: #16a34a;
                        color: #ffffff;
                        border: none;
                        border-radius: 6px;
                        font-size: 14px;
                        font-weight: 700;
                        cursor: pointer;
                    "
                >Tutup</button>
            </div>
        </div>
    </Transition>
</Teleport>
        <FooterUser />
    </div>
</template>

<style scoped>
/* ════════════════════════════════════════════
   DESIGN TOKENS
════════════════════════════════════════════ */
.smk-root {
    --green-50:  #f0fdf4;
    --green-100: #dcfce7;
    --green-500: #22c55e;
    --green-600: #16a34a;
    --green-700: #15803d;
    --green-800: #166534;
    --green-900: #14532d;
    --gray-50:   #f9fafb;
    --gray-100:  #f3f4f6;
    --gray-200:  #e5e7eb;
    --gray-300:  #d1d5db;
    --gray-400:  #9ca3af;
    --gray-500:  #6b7280;
    --gray-600:  #4b5563;
    --gray-700:  #374151;
    --gray-900:  #111827;
    --white:     #ffffff;

    --font-display: 'Fraunces', Georgia, serif;
    --font-body:    'Plus Jakarta Sans', sans-serif;

    --radius-sm: 6px;
    --radius-md: 12px;
    --radius-lg: 20px;

    --shadow-sm: 0 1px 3px rgba(0,0,0,0.07);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
    --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);

    --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);

    font-family: var(--font-body);
    color: var(--gray-900);
    background: var(--white);
    min-height: 100vh;
}

/* ─── Scroll Reveal ─────────────────────────────────────────────── */
/* ── Scroll Reveal System — multi-direction ── */
/* Base state: semua invisible sebelum trigger */
.reveal,
.reveal-left,
.reveal-right,
.reveal-up,
.reveal-zoom {
    opacity: 0;
    will-change: opacity, transform;
}
.reveal.revealed,
.reveal-left.revealed,
.reveal-right.revealed,
.reveal-up.revealed,
.reveal-zoom.revealed { opacity: 1; transform: none !important; }

/* Dari atas (default) */
.reveal       { transform: translateY(32px); transition: opacity 0.7s cubic-bezier(0.22,1,0.36,1), transform 0.7s cubic-bezier(0.22,1,0.36,1); }
/* Dari kiri */
.reveal-left  { transform: translateX(-56px); transition: opacity 0.75s cubic-bezier(0.22,1,0.36,1), transform 0.75s cubic-bezier(0.22,1,0.36,1); }
/* Dari kanan */
.reveal-right { transform: translateX(56px);  transition: opacity 0.75s cubic-bezier(0.22,1,0.36,1), transform 0.75s cubic-bezier(0.22,1,0.36,1); }
/* Dari bawah (subtle) */
.reveal-up    { transform: translateY(24px);  transition: opacity 0.65s cubic-bezier(0.22,1,0.36,1), transform 0.65s cubic-bezier(0.22,1,0.36,1); }
/* Zoom in */
.reveal-zoom  { transform: scale(0.92);       transition: opacity 0.65s cubic-bezier(0.22,1,0.36,1), transform 0.65s cubic-bezier(0.22,1,0.36,1); }

/* Stagger: tiap child muncul dengan delay berurutan */
.stagger-item {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.55s cubic-bezier(0.22,1,0.36,1), transform 0.55s cubic-bezier(0.22,1,0.36,1);
    transition-delay: calc(var(--stagger-i, 0) * 90ms);
}
.stagger-children.revealed .stagger-item { opacity: 1; transform: none; }

/* ─── Shared Layout ──────────────────────────────────────────────── */
.section-container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

.section-label {
    font-size: 11px; font-weight: 700; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--green-600); margin-bottom: 8px;
}
.section-label.light { color: rgba(255,255,255,0.65); }

.section-title {
    font-family: var(--font-display);
    font-size: clamp(26px, 4.5vw, 42px);
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1.15;
    margin: 0 0 16px;
}
.section-title.light { color: var(--white); }

.section-underline {
    width: 44px; height: 3px;
    background: var(--green-500);
    border-radius: 2px;
    margin-bottom: 28px;
}
.section-underline.centered { margin-left: auto; margin-right: auto; }

.section-desc {
    font-size: 15px; color: var(--gray-500); max-width: 500px;
    margin: -12px auto 28px; text-align: center; line-height: 1.7;
}
.section-header { text-align: center; margin-bottom: 44px; }
.section-cta { text-align: center; margin-top: 44px; }

.btn-outline {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 28px;
    border: 2px solid var(--gray-900); border-radius: var(--radius-sm);
    font-family: var(--font-body); font-size: 13px; font-weight: 700;
    letter-spacing: 0.04em; color: var(--gray-900); text-decoration: none;
    transition: var(--transition);
}
.btn-outline:hover { background: var(--gray-900); color: var(--white); }

.empty-state { text-align: center; padding: 64px 24px; color: var(--gray-500); }
.empty-icon {
    display: inline-flex; padding: 20px; background: var(--gray-100);
    border-radius: 50%; margin-bottom: 20px; color: var(--gray-400);
}
.empty-state h3 { font-size: 18px; font-weight: 600; color: var(--gray-700); margin: 0 0 8px; }
.empty-state p { font-size: 14px; margin: 0; }

/* ════════════════════════════════════════════
   HERO — Bersih, tanpa overlay hijau
════════════════════════════════════════════ */
.hero-section {
    position: relative; min-height: 100vh;
    display: flex; align-items: center; overflow: hidden;
}
.hero-bg { position: absolute; inset: 0; }
.hero-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 1.2s ease; }
.hero-slide.active { opacity: 1; }
.hero-img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
/* Overlay: hitam murni, tidak ada warna hijau sama sekali */
.hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.55) 0%,
        rgba(0,0,0,0.40) 50%,
        rgba(0,0,0,0.70) 100%
    );
}

/* Garis tipis putih di atas, bukan hijau */
.hero-top-bar {
    position: absolute; top: 0; left: 0; right: 0;
    height: 3px;
    background: rgba(255,255,255,0.3);
    z-index: 20;
}

.hero-content {
    position: relative; z-index: 10;
    width: 100%; padding: 120px 24px 100px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.hero-inner {
    max-width: 900px;
    width: 100%;
    text-align: center;
    display: flex; flex-direction: column;
    align-items: center;
    transform: translateX(120px);
}

/* ── Slide dari kiri / kanan — staggered ── */
/* Badge masuk dari kiri */
.hero-anim-1 { opacity: 0; animation: heroSlideLeft  0.75s cubic-bezier(0.22,1,0.36,1) 0.05s forwards; }
/* Judul masuk dari kanan */
.hero-anim-2 { opacity: 0; animation: heroSlideRight 0.80s cubic-bezier(0.22,1,0.36,1) 0.22s forwards; }
/* Sub masuk dari kiri */
.hero-anim-3 { opacity: 0; animation: heroSlideLeft  0.75s cubic-bezier(0.22,1,0.36,1) 0.40s forwards; }
/* Divider fade + scale */
.hero-anim-4 { opacity: 0; animation: heroFadeScale  0.60s cubic-bezier(0.22,1,0.36,1) 0.55s forwards; }
/* Tombol masuk dari kanan */
.hero-anim-5 { opacity: 0; animation: heroSlideRight 0.70s cubic-bezier(0.22,1,0.36,1) 0.68s forwards; }

@media (max-width: 768px) {
    .hero-inner { transform: translateX(0); padding: 0 4px; }
}

/* Brand baris teratas: logo kecil + nama */
.hero-brand {
    display: inline-flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.20);
    backdrop-filter: blur(10px);
    padding: 8px 20px; border-radius: 100px;
    margin-bottom: 32px;
}
.hero-logo {
    width: 28px; height: 28px; object-fit: contain; border-radius: 50%;
}
.hero-brand-name {
    font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.9);
    letter-spacing: 0.04em;
}

.hero-title {
    font-family: var(--font-display);
    font-size: clamp(32px, 6.5vw, 68px);
    font-weight: 700;
    color: white;
    line-height: 1.1;
    margin: 0 0 8px;
    text-shadow: 0 2px 24px rgba(0,0,0,0.35);
}
/* Baris kedua judul pakai warna kuning/emas agar kontras & menarik tanpa hijau */
.hero-title-accent {
    color: #fde68a; /* kuning hangat, bukan hijau */
    font-style: italic;
}

.hero-sub {
    font-size: clamp(14px, 2.2vw, 18px);
    color: rgba(255,255,255,0.80);
    line-height: 1.7; margin: 0 0 28px;
    max-width: 520px; margin-left: auto; margin-right: auto;
}

/* Garis dekoratif tengah — putih/gold, bukan hijau */
.hero-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 12px; margin-bottom: 32px;
}
.hero-divider-line { flex: 1; max-width: 80px; height: 1px; background: rgba(255,255,255,0.3); }
.hero-divider-dot  { width: 6px; height: 6px; background: #fde68a; border-radius: 50%; }

.hero-actions {
    display: flex; gap: 14px; flex-wrap: wrap;
    justify-content: center;
}

.btn-hero-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 30px;
    background: var(--white); color: var(--gray-900);
    border: none; border-radius: var(--radius-sm);
    font-family: var(--font-body); font-size: 14px; font-weight: 700;
    cursor: pointer; transition: var(--transition); text-decoration: none;
}
.btn-hero-primary:hover {
    background: var(--green-600); color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}

.btn-hero-ghost {
    display: inline-flex; align-items: center;
    padding: 14px 30px;
    background: transparent; color: white;
    border: 2px solid rgba(255,255,255,0.45);
    border-radius: var(--radius-sm);
    font-family: var(--font-body); font-size: 14px; font-weight: 600;
    text-decoration: none; transition: var(--transition);
}
.btn-hero-ghost:hover {
    background: rgba(255,255,255,0.12);
    border-color: rgba(255,255,255,0.8);
}

.hero-indicators {
    position: absolute; bottom: 36px; left: 50%;
    transform: translateX(-50%); z-index: 20;
    display: flex; gap: 10px;
}
.hero-dot {
    width: 8px; height: 8px;
    background: rgba(255,255,255,0.35); border: none;
    border-radius: 50%; cursor: pointer;
    transition: var(--transition); padding: 0;
}
.hero-dot.active { background: #fde68a; width: 26px; border-radius: 4px; }

.hero-arrow {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 20;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.20);
    backdrop-filter: blur(8px); color: white;
    border-radius: 50%; width: 46px; height: 46px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: var(--transition);
}
.hero-arrow:hover { background: rgba(255,255,255,0.22); }
.hero-arrow-left { left: 20px; }
.hero-arrow-right { right: 20px; }

@media (max-width: 768px) {
    .hero-section { min-height: 100svh; }
    .hero-content { padding: 100px 20px 90px; }
    .hero-arrow { width: 36px; height: 36px; }
    .hero-arrow-left  { left: 10px; }
    .hero-arrow-right { right: 10px; }
    .hero-brand { padding: 6px 14px; margin-bottom: 20px; }
    .hero-brand-name { font-size: 12px; }
    .hero-logo { width: 22px; height: 22px; }
    .hero-title { font-size: clamp(28px, 9vw, 44px); margin-bottom: 12px; }
    .hero-sub { font-size: 14px; margin-bottom: 20px; }
    .hero-divider { margin-bottom: 24px; }
    .hero-actions { flex-direction: column; align-items: center; gap: 10px; width: 100%; }
    .btn-hero-primary,
    .btn-hero-ghost { width: 100%; max-width: 280px; justify-content: center; padding: 13px 20px; }
    .hero-scroll-hint { display: none; }
    .hero-indicators { bottom: 24px; }
}

.hero-scroll-hint {
    position: absolute; bottom: 36px; right: 28px; z-index: 20;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    color: rgba(255,255,255,0.45);
    font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase;
}
.scroll-line {
    width: 1px; height: 36px;
    background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.45));
    animation: scrollLine 2s ease infinite;
}

/* ════════════════════════════════════════════
   SAMBUTAN
════════════════════════════════════════════ */
.sambutan-section { padding: 96px 0; background: var(--white); }
.sambutan-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 72px; align-items: start;
}
@media (max-width: 768px) { .sambutan-grid { grid-template-columns: 1fr; gap: 48px; } }

.photo-frame { position: relative; }
.photo-accent {
    position: absolute; top: -14px; left: -14px;
    right: 28px; bottom: 28px;
    border: 2px solid var(--green-100);
    border-radius: var(--radius-md); z-index: 0;
}
.photo-wrapper {
    position: relative; z-index: 1;
    border-radius: var(--radius-md); overflow: hidden;
    aspect-ratio: 4/5; box-shadow: var(--shadow-lg);
}
.kepala-photo { width: 100%; height: 100%; object-fit: cover; object-position: top center; }

.photo-namecard {
    position: relative; z-index: 2;
    background: white; padding: 18px 22px;
    border-left: 4px solid var(--green-500);
    box-shadow: var(--shadow-md);
}
.namecard-name {
    font-family: var(--font-display); font-size: 19px;
    font-weight: 700; color: var(--gray-900); margin: 0 0 4px;
}
.namecard-role {
    font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--green-600); margin: 0;
}

.sambutan-text-col { padding-top: 12px; }
.sambutan-quote {
    font-family: var(--font-display); font-style: italic;
    font-size: 16px; color: var(--green-700); margin: 0 0 18px;
}
.sambutan-body { display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px; }
.sambutan-body p {
    font-size: 15px; line-height: 1.8;
    color: var(--gray-600); margin: 0; text-align: justify;
}

/* ════════════════════════════════════════════
   TEACHER CAROUSEL — Tanpa accent warna hijau
════════════════════════════════════════════ */
.teacher-section { padding: 96px 0; background: var(--gray-50); }
.teacher-carousel-wrapper { position: relative; padding: 0 52px; }
.teacher-overflow { overflow: hidden; }
.teacher-track {
    display: flex;
    transition: transform 0.5s cubic-bezier(0.4,0,0.2,1);
    cursor: grab; user-select: none;
}
.teacher-track:active { cursor: grabbing; }
.teacher-track.no-transition { transition: none; }
.teacher-slide { width: 25%; flex-shrink: 0; padding: 0 10px; }
@media (max-width: 768px) { .teacher-slide { width: 100%; } }

.teacher-card {
    background: white; border-radius: var(--radius-md);
    overflow: hidden; box-shadow: var(--shadow-sm);
    transition: var(--transition); border: 1px solid var(--gray-100);
}
.teacher-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }

.teacher-photo-wrap {
    position: relative; aspect-ratio: 3/4;
    background: var(--gray-100); overflow: hidden;
}
.teacher-photo {
    width: 100%; height: 100%; object-fit: cover;
    object-position: top center;
    transition: transform 0.5s ease;
}
.teacher-photo.logo-mode { object-fit: contain; padding: 28px; background: var(--gray-50); }
.teacher-card:hover .teacher-photo { transform: scale(1.04); }

.teacher-info {
    padding: 16px; text-align: center;
    /* Dihapus: border-top hijau — sekarang abu biasa */
    border-top: 1px solid var(--gray-100);
}
.teacher-name {
    font-size: 14px; font-weight: 700;
    color: var(--gray-900); margin: 0 0 4px;
}
.teacher-pos {
    font-size: 11px; font-weight: 600;
    /* Warna posisi tetap hijau, tapi border card tidak */
    color: var(--green-600);
    letter-spacing: 0.06em; text-transform: uppercase; margin: 0;
}

.carousel-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 42px; height: 42px;
    background: white; border: 1px solid var(--gray-200);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: var(--transition);
    box-shadow: var(--shadow-md); color: var(--gray-700); z-index: 5;
}
.carousel-nav:hover:not(:disabled) {
    background: var(--green-600); border-color: var(--green-600);
    color: white; box-shadow: 0 4px 16px rgba(22,163,74,0.3);
}
.carousel-nav:disabled { opacity: 0.3; cursor: not-allowed; }
.carousel-nav-prev { left: 0; }
.carousel-nav-next { right: 0; }

.teacher-dots {
    display: flex; justify-content: center; gap: 8px; margin-top: 28px;
}
.teacher-dot {
    width: 8px; height: 8px; background: var(--gray-300);
    border: none; border-radius: 50%; cursor: pointer;
    transition: var(--transition); padding: 0;
}
.teacher-dot.active { background: var(--green-600); width: 22px; border-radius: 4px; }

/* ════════════════════════════════════════════
   BERITA — Featured & side sama tingginya
════════════════════════════════════════════ */
.news-section { padding: 96px 0; background: var(--gray-50); }

.news-header {
    display: flex; justify-content: space-between;
    align-items: flex-end; margin-bottom: 36px;
}
.news-see-all {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 700; color: var(--green-700);
    text-decoration: none; transition: var(--transition);
    border-bottom: 2px solid transparent; white-space: nowrap; padding-bottom: 2px;
}
.news-see-all:hover { border-bottom-color: var(--green-600); gap: 10px; }

/* Grid: kiri featured | kanan side list — SAMA TINGGI */
.news-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 20px;
    /* kunci agar keduanya stretch sama tinggi */
    align-items: stretch;
}
@media (max-width: 900px) {
    .news-grid { grid-template-columns: 1fr; }
    .news-header { flex-direction: column; align-items: flex-start; gap: 16px; }
}

/* Featured — flex column, img atas, body tumbuh ke bawah */
.news-featured {
    display: flex; flex-direction: column;
    background: white;
    border: 1px solid var(--gray-100);
    border-radius: var(--radius-md);
    overflow: hidden;
    text-decoration: none;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    /* Pastikan stretch ke penuh */
    height: 100%;
}
.news-featured:hover { box-shadow: var(--shadow-lg); transform: translateY(-3px); }

.news-featured-img-wrap {
    position: relative; overflow: hidden;
    /* Tinggi gambar featured tetap, sisanya tumbuh */
    height: 260px; flex-shrink: 0;
}
.news-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.news-featured:hover .news-img { transform: scale(1.04); }
.news-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.18), transparent);
}
.news-category-badge {
    position: absolute; top: 14px; left: 14px;
    background: var(--green-600); color: white;
    font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; padding: 4px 12px; border-radius: 100px;
}

.news-featured-body {
    padding: 24px; display: flex; flex-direction: column;
    gap: 10px;
    /* Body tumbuh mengisi sisa tinggi */
    flex: 1;
}
.news-date { font-size: 11px; color: var(--gray-400); font-weight: 500; letter-spacing: 0.04em; }
.news-featured-title {
    font-family: var(--font-display); font-size: 21px;
    font-weight: 600; color: var(--gray-900); line-height: 1.35; margin: 0;
    display: -webkit-box; -webkit-box-orient: vertical;
    -webkit-line-clamp: 3; overflow: hidden;
}
.news-featured-desc {
    font-size: 13px; color: var(--gray-500); line-height: 1.65; margin: 0;
    display: -webkit-box; -webkit-box-orient: vertical;
    -webkit-line-clamp: 3; overflow: hidden;
    /* Dorong read-more ke bawah */
    flex: 1;
}
.news-read-more {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 13px; font-weight: 700; color: var(--green-700); margin-top: auto;
}

/* Side list — 4 item, tiap item flex-1 agar sejajar total */
.news-side {
    display: flex; flex-direction: column;
    border: 1px solid var(--gray-100);
    border-radius: var(--radius-md); overflow: hidden;
    box-shadow: var(--shadow-sm);
    /* Tinggi side mengikuti featured */
    height: 100%;
}
.news-side-item {
    display: flex; text-decoration: none;
    background: white; transition: background var(--transition);
    border-bottom: 1px solid var(--gray-100);
    /* Tiap item dapat bagian yang sama */
    flex: 1; min-height: 0;
}
.news-side-item:last-child { border-bottom: none; }
.news-side-item:hover { background: var(--green-50); }

.news-side-img-wrap {
    width: 88px; flex-shrink: 0; overflow: hidden;
}
.news-side-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.news-side-item:hover .news-side-img { transform: scale(1.06); }

.news-side-body {
    padding: 12px 14px; display: flex;
    flex-direction: column; gap: 5px;
    justify-content: center; min-width: 0;
}
.news-side-title {
    font-size: 13px; font-weight: 600;
    color: var(--gray-900); line-height: 1.4; margin: 0;
    display: -webkit-box; -webkit-box-orient: vertical;
    -webkit-line-clamp: 2; overflow: hidden;
}
.news-read-more-sm {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; color: var(--green-600);
}

.md-hidden-cta { display: none; text-align: center; margin-top: 28px; }
@media (max-width: 900px) { .md-hidden-cta { display: block; } }

/* ════════════════════════════════════════════
   EKSTRAKURIKULER — Logo dari DB
════════════════════════════════════════════ */
.ekskur-section { padding: 96px 0; background: var(--gray-50); }

.ekskur-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px;
}
@media (max-width: 900px) { .ekskur-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 540px) { .ekskur-grid { grid-template-columns: 1fr; } }

.ekskur-card {
    background: white; border-radius: var(--radius-md);
    overflow: hidden; box-shadow: var(--shadow-sm);
    display: flex; flex-direction: column;
    transition: var(--transition);
    border-top: 3px solid var(--ekskur-color, var(--green-600));
    border: 1px solid var(--gray-100);
    border-top: 3px solid var(--ekskur-color, var(--green-600));
}
.ekskur-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }

.ekskur-top {
    display: flex; align-items: center;
    justify-content: space-between; padding: 20px 20px 0;
}

/* Logo dari DB — kotak dengan foto atau initial */
.ekskur-logo-wrap {
    width: 52px; height: 52px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: color-mix(in srgb, var(--ekskur-color, var(--green-600)) 10%, transparent);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: 1px solid var(--gray-100);
}
.ekskur-logo-img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: center;
}
.ekskur-initial {
    font-family: var(--font-display); font-size: 24px; font-weight: 700;
    color: var(--ekskur-color, var(--green-600));
}

.ekskur-badge {
    font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: var(--ekskur-color, var(--green-600));
    background: color-mix(in srgb, var(--ekskur-color, var(--green-600)) 10%, transparent);
    padding: 4px 10px; border-radius: 100px;
}

.ekskur-body { padding: 14px 20px; flex: 1; }
.ekskur-name { font-size: 16px; font-weight: 700; color: var(--gray-900); margin: 0 0 8px; }
.ekskur-desc {
    font-size: 13px; line-height: 1.65; color: var(--gray-500); margin: 0;
    display: -webkit-box; -webkit-box-orient: vertical;
    -webkit-line-clamp: 3; overflow: hidden;
}

.ekskur-footer {
    padding: 14px 20px;
    border-top: 1px solid var(--gray-100);
}
.ekskur-link {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 700;
    color: var(--ekskur-color, var(--green-600));
    text-decoration: none; transition: gap var(--transition);
}
.ekskur-link:hover { gap: 8px; }

/* ════════════════════════════════════════════
   PRESTASI — Icon per tingkatan, bukan juara
════════════════════════════════════════════ */
.prestasi-section {
    position: relative; padding: 96px 0;
    background: var(--green-800); overflow: hidden;
}
.prestasi-bg-pattern {
    position: absolute; inset: 0;
    background-image:
        radial-gradient(circle at 15% 50%, rgba(255,255,255,0.04) 0%, transparent 45%),
        radial-gradient(circle at 85% 20%, rgba(255,255,255,0.04) 0%, transparent 45%);
    pointer-events: none;
}
.prestasi-inner { position: relative; z-index: 1; }
.prestasi-heading { text-align: center; margin-bottom: 52px; }
.prestasi-sub {
    font-size: 14px; color: rgba(255,255,255,0.55);
    margin: 8px 0 0; letter-spacing: 0.02em;
}

.prestasi-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
}
@media (max-width: 768px) { .prestasi-grid { grid-template-columns: repeat(2, 1fr); } }

.prestasi-card {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: var(--radius-md);
    padding: 36px 20px 32px;
    text-align: center;
    display: flex; flex-direction: column;
    align-items: center; gap: 6px;
    transition: var(--transition);
}
.prestasi-card:hover {
    background: rgba(255,255,255,0.12);
    transform: translateY(-4px);
}

.prestasi-icon-wrap {
    width: 56px; height: 56px;
    background: rgba(255,255,255,0.10);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 8px;
    color: rgba(255,255,255,0.85);
    stroke-width: 1.5;
}

.prestasi-num {
    font-family: var(--font-display);
    font-size: 52px; font-weight: 700;
    color: white; line-height: 1;
}
.prestasi-label {
    font-size: 14px; font-weight: 700;
    color: rgba(255,255,255,0.90);
    letter-spacing: 0.04em;
}
.prestasi-desc {
    font-size: 11px; color: rgba(255,255,255,0.45);
    letter-spacing: 0.06em; text-transform: uppercase;
}

/* ════════════════════════════════════════════
   CONTACT
════════════════════════════════════════════ */
.contact-section { padding: 96px 0; background: white; }
.contact-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 60px; align-items: start;
}
@media (max-width: 768px) { .contact-grid { grid-template-columns: 1fr; gap: 40px; } }

.contact-col-title {
    font-family: var(--font-display); font-size: 21px;
    font-weight: 600; color: var(--gray-900); margin: 0 0 8px;
}
.contact-col-sub { font-size: 14px; color: var(--gray-500); line-height: 1.65; margin: 0 0 20px; }

.map-frame {
    width: 100%; height: 340px;
    border-radius: var(--radius-md); overflow: hidden;
    border: 1px solid var(--gray-200); box-shadow: var(--shadow-md);
}

.contact-pills { display: flex; flex-direction: column; gap: 10px; margin-top: 16px; }
.contact-pill {
    display: inline-flex; align-items: center; gap: 10px;
    font-size: 13px; color: var(--gray-600);
    background: var(--gray-50); padding: 10px 16px;
    border-radius: var(--radius-sm); border: 1px solid var(--gray-200);
}
.contact-pill svg { color: var(--green-600); flex-shrink: 0; }

.form-error-banner {
    background: #fef2f2; border: 1px solid #fecaca;
    color: #dc2626; padding: 12px 16px;
    border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 20px;
}
.contact-form { display: flex; flex-direction: column; gap: 18px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 540px) { .form-row { grid-template-columns: 1fr; } }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 13px; font-weight: 600; color: var(--gray-700); }
.form-optional { font-weight: 400; color: var(--gray-400); font-size: 12px; }
.form-input, .form-textarea {
    width: 100%; padding: 11px 14px;
    border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
    font-family: var(--font-body); font-size: 14px;
    color: var(--gray-900); background: white;
    transition: var(--transition); outline: none; box-sizing: border-box;
}
.form-input:focus, .form-textarea:focus {
    border-color: var(--green-500);
    box-shadow: 0 0 0 3px rgba(34,197,94,0.1);
}
.form-group.error .form-input,
.form-group.error .form-textarea { border-color: #f87171; }
.form-textarea { resize: none; }
.form-error-msg { font-size: 12px; color: #dc2626; }

.btn-submit {
    padding: 14px 28px; background: var(--green-700); color: white;
    border: none; border-radius: var(--radius-sm);
    font-family: var(--font-body); font-size: 15px; font-weight: 700;
    cursor: pointer; transition: var(--transition);
    display: flex; align-items: center; justify-content: center;
}
.btn-submit:hover:not(:disabled) {
    background: var(--green-800); transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(22,163,74,0.35);
}
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-loading, .btn-send-label { display: flex; align-items: center; gap: 8px; }

@keyframes heroSlideLeft {
    from { opacity: 0; transform: translateX(-60px); filter: blur(6px); }
    to   { opacity: 1; transform: translateX(0);     filter: blur(0); }
}
@keyframes heroSlideRight {
    from { opacity: 0; transform: translateX(60px);  filter: blur(6px); }
    to   { opacity: 1; transform: translateX(0);     filter: blur(0); }
}
@keyframes heroFadeScale {
    from { opacity: 0; transform: scaleX(0.4); }
    to   { opacity: 1; transform: scaleX(1); }
}
@keyframes heroFadeDown {
    from { opacity: 0; transform: translateY(-20px); filter: blur(4px); }
    to   { opacity: 1; transform: translateY(0);     filter: blur(0); }
}
@keyframes heroFadeUp {
    from { opacity: 0; transform: translateY(30px);  filter: blur(4px); }
    to   { opacity: 1; transform: translateY(0);     filter: blur(0); }
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-18px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(22px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes scrollLine {
    0%   { opacity: 0; transform: scaleY(0); transform-origin: top; }
    50%  { opacity: 1; transform: scaleY(1); }
    100% { opacity: 0; }
}
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; }

/* ════════════════════════════════════════════
   SECTION-SPECIFIC ANIMATION TWEAKS
════════════════════════════════════════════ */

/* Sambutan: 2 kolom — kiri sedikit lebih lambat supaya berasa "in conversation" */
.sambutan-photo-col.reveal-left  { transition-duration: 0.85s; }
.sambutan-text-col.reveal-right  { transition-duration: 0.85s; transition-delay: 0.12s; }

/* Berita: featured lebih lambat (elemen besar), side dengan slight delay */
.news-featured.reveal-left  { transition-duration: 0.80s; }
.news-side.reveal-right     { transition-duration: 0.80s; transition-delay: 0.15s; }

/* News side items: stagger dari kanan satu per satu */
.news-side.reveal-right .news-side-item {
    opacity: 0;
    transform: translateX(40px);
    transition: opacity 0.5s cubic-bezier(0.22,1,0.36,1), transform 0.5s cubic-bezier(0.22,1,0.36,1);
    transition-delay: calc(0.18s + var(--side-i, 0) * 80ms);
}
.news-side.reveal-right.revealed .news-side-item {
    opacity: 1;
    transform: none;
}

/* Prestasi stagger: delay lebih lambat antar card untuk dramatisasi */
.prestasi-grid .stagger-item {
    transition-delay: calc(var(--stagger-i, 0) * 110ms);
}

/* Contact: map dan form delay sedikit berbeda */
.contact-map-col.reveal-left  { transition-duration: 0.80s; }
.contact-form-col.reveal-right { transition-duration: 0.80s; transition-delay: 0.18s; }

/* Ekskul stagger: baris pertama cepat, baris berikutnya mulai setelah baris satu */
.ekskur-grid .stagger-item {
    transition-delay: calc(var(--stagger-i, 0) * 75ms);
}

</style>
