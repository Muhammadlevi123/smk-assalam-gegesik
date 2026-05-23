<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted, ref, computed, watch, nextTick } from 'vue';

interface PrestasiItem {
    id: number;
    nama_lomba: string;
    tingkat: string;
    juara: string;
    penyelenggara: string;
    tanggal: string;
    tanggal_formatted: string;
    nama_siswa: string;
    foto?: string;
    siswa: { id: number; nama: string }[];
}
interface Stats {
    internasional: number;
    nasional: number;
    provinsi: number;
    kabupaten: number;
    total: number;
}

const props = defineProps<{
    prestasi?: PrestasiItem[];
    stats?: Stats;
    aktif_filter?: string;
}>();

const searchInput = ref('');
const aktifNav    = ref('');
const showAllKat  = ref(false);

const setKategori = (key: string | null) => {
    aktifNav.value    = key || '';
    searchInput.value = '';
    showAllKat.value  = false;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const warna: Record<string, { bg: string; text: string; border: string; grad: string }> = {
    internasional: { bg: '#fef3c7', text: '#92400e', border: '#fcd34d', grad: 'linear-gradient(135deg,#f59e0b,#d97706)' },
    nasional:      { bg: '#fee2e2', text: '#991b1b', border: '#fca5a5', grad: 'linear-gradient(135deg,#ef4444,#dc2626)' },
    provinsi:      { bg: '#dbeafe', text: '#1e40af', border: '#93c5fd', grad: 'linear-gradient(135deg,#3b82f6,#2563eb)' },
    kabupaten:     { bg: '#d1fae5', text: '#065f46', border: '#6ee7b7', grad: 'linear-gradient(135deg,#10b981,#059669)' },
    kota:          { bg: '#d1fae5', text: '#065f46', border: '#6ee7b7', grad: 'linear-gradient(135deg,#10b981,#059669)' },
};
const getW = (t: string) =>
    warna[t.toLowerCase()] ?? { bg:'#f3f4f6', text:'#374151', border:'#d1d5db', grad:'linear-gradient(135deg,#6b7280,#4b5563)' };

const getIcon = (juara: string) => {
    const j = juara.toLowerCase();
    if (j.includes('1') || j.includes('emas'))     return '🥇';
    if (j.includes('2') || j.includes('perak'))    return '🥈';
    if (j.includes('3') || j.includes('perunggu')) return '🥉';
    return '🏆';
};

const defaultImg = '/storage/img/logo/logo.png';
const allData    = computed(() => props.prestasi ?? []);

const searchResult = computed(() => {
    const q = searchInput.value.toLowerCase().trim();
    if (!q) return [];
    return allData.value.filter(p =>
        p.nama_lomba.toLowerCase().includes(q) ||
        p.nama_siswa.toLowerCase().includes(q) ||
        (p.penyelenggara?.toLowerCase() ?? '').includes(q) ||
        p.tingkat.toLowerCase().includes(q)
    );
});
const isSearching = computed(() => searchInput.value.trim().length > 0);
const isKategori  = computed(() => !!aktifNav.value && !isSearching.value);

const galeri = computed(() => allData.value.slice(0, 7));
const kategoriData = computed(() => {
    if (!aktifNav.value) return [];
    return allData.value.filter(p => {
        const t = p.tingkat.toLowerCase();
        if (aktifNav.value === 'kabupaten') return ['kabupaten','kota'].includes(t);
        return t === aktifNav.value;
    });
});

// 4 item per baris: [besar][besar][stack] atau [stack][besar][besar]
// stack = 2 foto kecil numpuk vertikal
// Max 4 baris = 16 item
const galeriKatDisplay = computed(() =>
    showAllKat.value ? kategoriData.value : kategoriData.value.slice(0, 16)
);
const galeriKatRows = computed(() => {
    const items = galeriKatDisplay.value;
    const rows: typeof items[] = [];
    for (let i = 0; i < items.length; i += 4) {
        rows.push(items.slice(i, i + 4));
    }
    return rows;
});
const hasMore = computed(() => !showAllKat.value && kategoriData.value.length > 16);

const sectionList = computed(() => [
    { key: 'internasional', label: 'Internasional', data: allData.value.filter(p => p.tingkat.toLowerCase() === 'internasional'), count: props.stats?.internasional ?? 0 },
    { key: 'nasional',      label: 'Nasional',      data: allData.value.filter(p => p.tingkat.toLowerCase() === 'nasional'),      count: props.stats?.nasional ?? 0 },
    { key: 'provinsi',      label: 'Provinsi',      data: allData.value.filter(p => p.tingkat.toLowerCase() === 'provinsi'),      count: props.stats?.provinsi ?? 0 },
    { key: 'kabupaten',     label: 'Kab./Kota',     data: allData.value.filter(p => ['kabupaten','kota'].includes(p.tingkat.toLowerCase())), count: props.stats?.kabupaten ?? 0 },
].filter(s => s.count > 0));

const scrollLeft  = (id: string) => document.getElementById(id)?.scrollBy({ left: -300, behavior: 'smooth' });
const scrollRight = (id: string) => document.getElementById(id)?.scrollBy({ left:  300, behavior: 'smooth' });
const clearSearch = () => { searchInput.value = ''; };

const observeFadeIn = () => {
    nextTick(() => {
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    (e.target as HTMLElement).classList.add('visible');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.05 });
        document.querySelectorAll('.fade-in:not(.visible)').forEach(el => obs.observe(el));
    });
};
onMounted(() => observeFadeIn());
watch([aktifNav, isSearching], () => {
    nextTick(() => {
        document.querySelectorAll('.fade-in').forEach(el => el.classList.add('visible'));
    });
});
</script>

<template>
    <Head title="Prestasi - SMK Assalam Gegesik" />
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

                <!-- NAVBAR -->
                <div class="page-header fade-in">
                    <h1 class="page-title">Prestasi &amp; Penghargaan</h1>
                    <div class="news-navbar">
                        <nav class="nav-cats">
                            <button class="nav-cat" :class="{ active: aktifNav === '' && !isSearching }" @click="setKategori(null)">HOME</button>
                            <button v-for="s in sectionList" :key="s.key" class="nav-cat" :class="{ active: aktifNav === s.key }" @click="setKategori(s.key)">{{ s.label.toUpperCase() }}</button>
                        </nav>
                        <form @submit.prevent class="nav-search">
                            <input v-model="searchInput" type="text" placeholder="Cari prestasi..." class="nav-search-input" />
                            <button v-if="isSearching" type="button" class="nav-search-btn" @click="clearSearch">✕</button>
                            <span v-else class="nav-search-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                        </form>
                    </div>
                </div>

                <div v-if="allData.length === 0" class="empty fade-in">
                    <div style="font-size:48px">🏆</div>
                    <p>Belum ada data prestasi.</p>
                </div>

                <template v-else>

                    <!-- MODE SEARCH -->
                    <template v-if="isSearching">
                        <div class="filter-head fade-in">
                            <div class="sec-title-wrap">
                                <span class="sec-bar"></span>
                                <div>
                                    <h2 class="sec-title">Hasil: "{{ searchInput }}"</h2>
                                    <p class="sec-count">{{ searchResult.length }} prestasi ditemukan</p>
                                </div>
                            </div>
                            <button class="clear-btn" @click="clearSearch">✕ Hapus pencarian</button>
                        </div>
                        <div v-if="searchResult.length > 0" class="filter-grid fade-in">
                            <div v-for="item in searchResult" :key="item.id" class="fcard">
                                <div class="fcard-img">
                                    <img :src="item.foto ? '/storage/' + item.foto : defaultImg" :alt="item.nama_lomba" />
                                    <div class="fcard-hover-overlay">
                                        <span class="fcard-icon">{{ getIcon(item.juara) }}</span>
                                        <div class="fcard-info">
                                            <p class="fcard-juara">{{ item.juara }}</p>
                                            <h3 class="fcard-nama">{{ item.nama_lomba }}</h3>
                                            <p class="fcard-meta">👤 {{ item.nama_siswa || '—' }}</p>
                                            <p class="fcard-meta">🏢 {{ item.penyelenggara || '—' }}</p>
                                            <p class="fcard-meta">📅 {{ item.tanggal_formatted }}</p>
                                        </div>
                                    </div>
                                    <span class="fcard-badge" :style="{ background: getW(item.tingkat).grad }">{{ item.tingkat }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="empty fade-in">
                            <div style="font-size:36px">🔍</div>
                            <p>Tidak ada hasil untuk "{{ searchInput }}"</p>
                        </div>
                    </template>

                    <!-- MODE KATEGORI -->
                    <template v-else-if="isKategori">
                        <div class="section">
                            <div class="sec-head">
                                <div class="sec-title-wrap">
                                    <span class="sec-bar" :style="{ background: getW(aktifNav).grad }"></span>
                                    <div>
                                        <h2 class="sec-title">{{ sectionList.find(s => s.key === aktifNav)?.label }}</h2>
                                        <p class="sec-count">{{ kategoriData.length }} prestasi</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="galeriKatRows.length > 0" class="kat-rows">
                                <div v-for="(row, rowIdx) in galeriKatRows" :key="rowIdx"
                                    class="kat-baris"
                                    :class="rowIdx % 2 === 0 ? 'baris-a' : 'baris-b'">

                                    <!-- BARIS A (ganjil): [besar][besar][stack kecil] -->
                                    <template v-if="rowIdx % 2 === 0">
                                        <div v-if="row[0]" class="kb">
                                            <img :src="row[0].foto ? '/storage/' + row[0].foto : defaultImg" :alt="row[0].nama_lomba" />
                                            <div class="galeri-overlay"><div class="galeri-info">
                                                <span class="galeri-icon sm">{{ getIcon(row[0].juara) }}</span>
                                                <span class="galeri-badge-inline sm" :style="{ background: getW(row[0].tingkat).grad }">{{ row[0].tingkat }}</span>
                                                <p class="galeri-juara">{{ row[0].juara }}</p>
                                                <h3 class="galeri-nama sm">{{ row[0].nama_lomba }}</h3>
                                                <p class="galeri-sub">👤 {{ row[0].nama_siswa || '—' }}</p>
                                            </div></div>
                                        </div>
                                        <div v-if="row[1]" class="kb">
                                            <img :src="row[1].foto ? '/storage/' + row[1].foto : defaultImg" :alt="row[1].nama_lomba" />
                                            <div class="galeri-overlay"><div class="galeri-info">
                                                <span class="galeri-icon sm">{{ getIcon(row[1].juara) }}</span>
                                                <span class="galeri-badge-inline sm" :style="{ background: getW(row[1].tingkat).grad }">{{ row[1].tingkat }}</span>
                                                <p class="galeri-juara">{{ row[1].juara }}</p>
                                                <h3 class="galeri-nama sm">{{ row[1].nama_lomba }}</h3>
                                                <p class="galeri-sub">👤 {{ row[1].nama_siswa || '—' }}</p>
                                            </div></div>
                                        </div>
                                        <!-- Stack 2 kecil numpuk vertikal -->
                                        <div class="kst">
                                            <div v-if="row[2]" class="ks">
                                                <img :src="row[2].foto ? '/storage/' + row[2].foto : defaultImg" :alt="row[2].nama_lomba" />
                                                <div class="galeri-overlay"><div class="galeri-info compact">
                                                    <span class="galeri-icon xs">{{ getIcon(row[2].juara) }}</span>
                                                    <h3 class="galeri-nama xs">{{ row[2].nama_lomba }}</h3>
                                                </div></div>
                                                <span class="galeri-badge-abs" :style="{ background: getW(row[2].tingkat).grad }">{{ row[2].tingkat }}</span>
                                            </div>
                                            <div v-if="row[3]" class="ks">
                                                <img :src="row[3].foto ? '/storage/' + row[3].foto : defaultImg" :alt="row[3].nama_lomba" />
                                                <div class="galeri-overlay"><div class="galeri-info compact">
                                                    <span class="galeri-icon xs">{{ getIcon(row[3].juara) }}</span>
                                                    <h3 class="galeri-nama xs">{{ row[3].nama_lomba }}</h3>
                                                </div></div>
                                                <span class="galeri-badge-abs" :style="{ background: getW(row[3].tingkat).grad }">{{ row[3].tingkat }}</span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- BARIS B (genap): [stack kecil][besar][besar] -->
                                    <template v-else>
                                        <!-- Stack 2 kecil numpuk vertikal kiri -->
                                        <div class="kst">
                                            <div v-if="row[0]" class="ks">
                                                <img :src="row[0].foto ? '/storage/' + row[0].foto : defaultImg" :alt="row[0].nama_lomba" />
                                                <div class="galeri-overlay"><div class="galeri-info compact">
                                                    <span class="galeri-icon xs">{{ getIcon(row[0].juara) }}</span>
                                                    <h3 class="galeri-nama xs">{{ row[0].nama_lomba }}</h3>
                                                </div></div>
                                                <span class="galeri-badge-abs" :style="{ background: getW(row[0].tingkat).grad }">{{ row[0].tingkat }}</span>
                                            </div>
                                            <div v-if="row[1]" class="ks">
                                                <img :src="row[1].foto ? '/storage/' + row[1].foto : defaultImg" :alt="row[1].nama_lomba" />
                                                <div class="galeri-overlay"><div class="galeri-info compact">
                                                    <span class="galeri-icon xs">{{ getIcon(row[1].juara) }}</span>
                                                    <h3 class="galeri-nama xs">{{ row[1].nama_lomba }}</h3>
                                                </div></div>
                                                <span class="galeri-badge-abs" :style="{ background: getW(row[1].tingkat).grad }">{{ row[1].tingkat }}</span>
                                            </div>
                                        </div>
                                        <div v-if="row[2]" class="kb">
                                            <img :src="row[2].foto ? '/storage/' + row[2].foto : defaultImg" :alt="row[2].nama_lomba" />
                                            <div class="galeri-overlay"><div class="galeri-info">
                                                <span class="galeri-icon sm">{{ getIcon(row[2].juara) }}</span>
                                                <span class="galeri-badge-inline sm" :style="{ background: getW(row[2].tingkat).grad }">{{ row[2].tingkat }}</span>
                                                <p class="galeri-juara">{{ row[2].juara }}</p>
                                                <h3 class="galeri-nama sm">{{ row[2].nama_lomba }}</h3>
                                                <p class="galeri-sub">👤 {{ row[2].nama_siswa || '—' }}</p>
                                            </div></div>
                                        </div>
                                        <div v-if="row[3]" class="kb">
                                            <img :src="row[3].foto ? '/storage/' + row[3].foto : defaultImg" :alt="row[3].nama_lomba" />
                                            <div class="galeri-overlay"><div class="galeri-info">
                                                <span class="galeri-icon sm">{{ getIcon(row[3].juara) }}</span>
                                                <span class="galeri-badge-inline sm" :style="{ background: getW(row[3].tingkat).grad }">{{ row[3].tingkat }}</span>
                                                <p class="galeri-juara">{{ row[3].juara }}</p>
                                                <h3 class="galeri-nama sm">{{ row[3].nama_lomba }}</h3>
                                                <p class="galeri-sub">👤 {{ row[3].nama_siswa || '—' }}</p>
                                            </div></div>
                                        </div>
                                    </template>
                                </div>

                                <div v-if="hasMore" class="lihat-semua-wrap">
                                    <button class="lihat-semua-btn" @click="showAllKat = true">
                                        Lihat Semua {{ kategoriData.length }} Prestasi ↓
                                    </button>
                                </div>
                            </div>

                            <div v-else class="empty">
                                <div style="font-size:36px">🏆</div>
                                <p>Belum ada prestasi {{ sectionList.find(s => s.key === aktifNav)?.label }}.</p>
                            </div>
                        </div>
                    </template>

                    <!-- MODE HOME -->
                    <template v-else>
                        <div class="section fade-in">
                            <div class="sec-head">
                                <div class="sec-title-wrap">
                                    <span class="sec-bar"></span>
                                    <div>
                                        <h2 class="sec-title">Prestasi Terkini</h2>
                                        <p class="sec-count">{{ Math.min(7, allData.length) }} pencapaian terbaru</p>
                                    </div>
                                </div>
                            </div>
                            <div class="galeri-grid">
                                <div v-if="galeri[0]" class="galeri-item g-big">
                                    <img :src="galeri[0].foto ? '/storage/' + galeri[0].foto : defaultImg" :alt="galeri[0].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info">
                                        <span class="galeri-icon">{{ getIcon(galeri[0].juara) }}</span>
                                        <span class="galeri-badge-inline" :style="{ background: getW(galeri[0].tingkat).grad }">{{ galeri[0].tingkat }}</span>
                                        <p class="galeri-juara">{{ galeri[0].juara }}</p>
                                        <h3 class="galeri-nama">{{ galeri[0].nama_lomba }}</h3>
                                        <p class="galeri-sub">👤 {{ galeri[0].nama_siswa || '—' }}</p>
                                        <p class="galeri-sub">🏢 {{ galeri[0].penyelenggara || '—' }}</p>
                                        <p class="galeri-sub">📅 {{ galeri[0].tanggal_formatted }}</p>
                                    </div></div>
                                </div>
                                <div v-if="galeri[1]" class="galeri-item g-mid">
                                    <img :src="galeri[1].foto ? '/storage/' + galeri[1].foto : defaultImg" :alt="galeri[1].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info">
                                        <span class="galeri-icon sm">{{ getIcon(galeri[1].juara) }}</span>
                                        <span class="galeri-badge-inline sm" :style="{ background: getW(galeri[1].tingkat).grad }">{{ galeri[1].tingkat }}</span>
                                        <p class="galeri-juara">{{ galeri[1].juara }}</p>
                                        <h3 class="galeri-nama sm">{{ galeri[1].nama_lomba }}</h3>
                                        <p class="galeri-sub">👤 {{ galeri[1].nama_siswa || '—' }}</p>
                                        <p class="galeri-sub">📅 {{ galeri[1].tanggal_formatted }}</p>
                                    </div></div>
                                </div>
                                <div v-if="galeri[3]" class="galeri-item g-sm">
                                    <img :src="galeri[3].foto ? '/storage/' + galeri[3].foto : defaultImg" :alt="galeri[3].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info compact">
                                        <span class="galeri-icon xs">{{ getIcon(galeri[3].juara) }}</span>
                                        <h3 class="galeri-nama xs">{{ galeri[3].nama_lomba }}</h3>
                                        <p class="galeri-sub xs">{{ galeri[3].nama_siswa || '—' }}</p>
                                    </div></div>
                                    <span class="galeri-badge-abs" :style="{ background: getW(galeri[3].tingkat).grad }">{{ galeri[3].tingkat }}</span>
                                </div>
                                <div v-if="galeri[4]" class="galeri-item g-sm">
                                    <img :src="galeri[4].foto ? '/storage/' + galeri[4].foto : defaultImg" :alt="galeri[4].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info compact">
                                        <span class="galeri-icon xs">{{ getIcon(galeri[4].juara) }}</span>
                                        <h3 class="galeri-nama xs">{{ galeri[4].nama_lomba }}</h3>
                                        <p class="galeri-sub xs">{{ galeri[4].nama_siswa || '—' }}</p>
                                    </div></div>
                                    <span class="galeri-badge-abs" :style="{ background: getW(galeri[4].tingkat).grad }">{{ galeri[4].tingkat }}</span>
                                </div>
                                <div v-if="galeri[2]" class="galeri-item g-mid">
                                    <img :src="galeri[2].foto ? '/storage/' + galeri[2].foto : defaultImg" :alt="galeri[2].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info">
                                        <span class="galeri-icon sm">{{ getIcon(galeri[2].juara) }}</span>
                                        <span class="galeri-badge-inline sm" :style="{ background: getW(galeri[2].tingkat).grad }">{{ galeri[2].tingkat }}</span>
                                        <p class="galeri-juara">{{ galeri[2].juara }}</p>
                                        <h3 class="galeri-nama sm">{{ galeri[2].nama_lomba }}</h3>
                                        <p class="galeri-sub">👤 {{ galeri[2].nama_siswa || '—' }}</p>
                                        <p class="galeri-sub">📅 {{ galeri[2].tanggal_formatted }}</p>
                                    </div></div>
                                </div>
                                <div v-if="galeri[5]" class="galeri-item g-sm">
                                    <img :src="galeri[5].foto ? '/storage/' + galeri[5].foto : defaultImg" :alt="galeri[5].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info compact">
                                        <span class="galeri-icon xs">{{ getIcon(galeri[5].juara) }}</span>
                                        <h3 class="galeri-nama xs">{{ galeri[5].nama_lomba }}</h3>
                                        <p class="galeri-sub xs">{{ galeri[5].nama_siswa || '—' }}</p>
                                    </div></div>
                                    <span class="galeri-badge-abs" :style="{ background: getW(galeri[5].tingkat).grad }">{{ galeri[5].tingkat }}</span>
                                </div>
                                <div v-if="galeri[6]" class="galeri-item g-sm">
                                    <img :src="galeri[6].foto ? '/storage/' + galeri[6].foto : defaultImg" :alt="galeri[6].nama_lomba" />
                                    <div class="galeri-overlay"><div class="galeri-info compact">
                                        <span class="galeri-icon xs">{{ getIcon(galeri[6].juara) }}</span>
                                        <h3 class="galeri-nama xs">{{ galeri[6].nama_lomba }}</h3>
                                        <p class="galeri-sub xs">{{ galeri[6].nama_siswa || '—' }}</p>
                                    </div></div>
                                    <span class="galeri-badge-abs" :style="{ background: getW(galeri[6].tingkat).grad }">{{ galeri[6].tingkat }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-for="sec in sectionList" :key="sec.key" :id="'section-' + sec.key" class="section fade-in">
                            <div class="sec-head">
                                <div class="sec-title-wrap">
                                    <span class="sec-bar" :style="{ background: getW(sec.key).grad }"></span>
                                    <div>
                                        <h2 class="sec-title">{{ sec.label }}</h2>
                                        <p class="sec-count">{{ sec.count }} prestasi</p>
                                    </div>
                                </div>
                                <div class="scroll-nav">
                                    <button class="scroll-btn" @click="scrollLeft('scroll-' + sec.key)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button class="scroll-btn" @click="scrollRight('scroll-' + sec.key)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="h-scroll" :id="'scroll-' + sec.key">
                                <div v-for="item in sec.data" :key="item.id" class="hcard">
                                    <div class="hcard-img">
                                        <img :src="item.foto ? '/storage/' + item.foto : defaultImg" :alt="item.nama_lomba" />
                                        <div class="hcard-img-overlay" :style="{ background: getW(item.tingkat).grad }"></div>
                                        <span class="hcard-icon">{{ getIcon(item.juara) }}</span>
                                        <span class="hcard-tingkat" :style="{ background: getW(item.tingkat).grad }">{{ item.tingkat }}</span>
                                    </div>
                                    <div class="hcard-body">
                                        <p class="hcard-juara">{{ item.juara }}</p>
                                        <h3 class="hcard-nama">{{ item.nama_lomba }}</h3>
                                        <div class="hcard-meta">
                                            <p class="hcard-meta-item">
                                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ item.nama_siswa || '—' }}
                                            </p>
                                            <p class="hcard-meta-item">
                                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5"/></svg>
                                                {{ item.penyelenggara || '—' }}
                                            </p>
                                            <p class="hcard-meta-item">
                                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ item.tanggal_formatted }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </template>
            </div>
        </div>

        <FooterUser />
    </div>
</template>

<style scoped>
.page-root{
    --g500:#22c55e;--g600:#16a34a;--g700:#15803d;
    --gray50:#f9fafb;--gray100:#f3f4f6;--gray200:#e5e7eb;--gray300:#d1d5db;
    --gray400:#9ca3af;--gray500:#6b7280;--gray700:#374151;--gray900:#111827;
    --fd:'Fraunces',Georgia,serif;--fb:'Plus Jakarta Sans',sans-serif;
    font-family:var(--fb);color:var(--gray900);min-height:100vh;background:var(--gray50);
}
.fade-in{opacity:0;transform:translateY(16px);transition:opacity .5s ease,transform .5s ease}
.fade-in.visible{opacity:1;transform:none}

.hero{position:relative;height:280px;overflow:hidden}
.hero-bg{position:absolute;inset:0;background:url('/storage/img/landingpage/cover1.png') center/cover no-repeat}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,40,20,.88),rgba(22,101,52,.70))}
@media(max-width:768px){.hero{height:180px}}

.page-bg{background:var(--gray50);padding:20px 0 80px}
.page-wrap{max-width:1120px;margin:0 auto;padding:0 24px;display:flex;flex-direction:column;gap:40px}

.page-header{display:flex;flex-direction:column;gap:0}
.page-title{font-family:var(--fd);font-size:clamp(20px,3vw,30px);font-weight:700;color:var(--gray900);margin:0 0 8px}
.news-navbar{display:flex;align-items:center;background:#f3f4f6;border-bottom:2px solid var(--gray200);overflow:hidden}
.nav-cats{display:flex;align-items:stretch;flex:1;overflow-x:auto;scrollbar-width:none}
.nav-cats::-webkit-scrollbar{display:none}
.nav-cat{padding:0 18px;height:46px;font-size:12px;font-weight:700;letter-spacing:.05em;color:var(--gray500);background:transparent;border:none;cursor:pointer;white-space:nowrap;transition:color .2s,background .2s;font-family:var(--fb);position:relative}
.nav-cat:hover{color:var(--g700);background:rgba(22,101,52,.06)}
.nav-cat.active{color:var(--g700);font-weight:800}
.nav-cat.active::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:var(--g600)}
.nav-search{display:flex;align-items:center;flex-shrink:0;border-left:1px solid var(--gray200)}
.nav-search-input{background:transparent;border:none;outline:none;color:var(--gray700);font-size:12px;font-family:var(--fb);padding:8px 12px;width:150px}
.nav-search-input::placeholder{color:var(--gray400)}
.nav-search-btn{background:transparent;border:none;border-left:1px solid var(--gray200);cursor:pointer;color:var(--gray400);padding:10px 12px;font-size:12px;display:flex;align-items:center;transition:color .2s}
.nav-search-btn:hover{color:var(--gray700)}

.section{display:flex;flex-direction:column;gap:16px}
.sec-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.sec-title-wrap{display:flex;align-items:center;gap:14px}
.sec-bar{display:block;width:4px;height:36px;background:var(--g600);flex-shrink:0}
.sec-title{font-family:var(--fd);font-size:clamp(18px,2.5vw,22px);font-weight:700;color:var(--gray900);margin:0;line-height:1.2}
.sec-count{font-size:12px;color:var(--gray400);margin:4px 0 0}
.scroll-nav{display:flex;align-items:center;gap:8px}
.scroll-btn{width:34px;height:34px;border:1.5px solid var(--gray200);background:white;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--gray500);transition:all .2s}
.scroll-btn:hover{border-color:var(--g600);color:var(--g700)}

/* GALERI HOME */
.galeri-grid{display:grid;grid-template-columns:2fr 1.2fr 1fr 1fr;grid-template-rows:1fr 1fr;gap:4px;height:500px}
@media(max-width:900px){.galeri-grid{grid-template-columns:1fr 1fr;grid-template-rows:repeat(3,180px);height:auto}}
@media(max-width:560px){.galeri-grid{grid-template-columns:1fr;grid-template-rows:repeat(4,200px);height:auto}}
.galeri-item{position:relative;overflow:hidden;cursor:pointer;background:var(--gray200)}
.galeri-item img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s}
.galeri-item:hover img{transform:scale(1.07)}
.g-big{grid-column:1;grid-row:1/3}
.g-mid{grid-column:2;grid-row:auto}
.g-sm{grid-column:auto;grid-row:auto}
.galeri-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.9) 0%,rgba(0,0,0,.3) 50%,transparent 100%);opacity:0;transition:opacity .3s;display:flex;align-items:flex-end;padding:14px}
.galeri-item:hover .galeri-overlay{opacity:1}
.galeri-info{display:flex;flex-direction:column;gap:3px;width:100%}
.galeri-info.compact{gap:2px}
.galeri-icon{font-size:28px;margin-bottom:4px}
.galeri-icon.sm{font-size:20px;margin-bottom:2px}
.galeri-icon.xs{font-size:14px;margin-bottom:1px}
.galeri-badge-inline{display:inline-block;color:white;font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:2px 8px;margin-bottom:3px;align-self:flex-start}
.galeri-badge-inline.sm{font-size:8px;padding:1px 6px}
.galeri-juara{font-size:11px;font-weight:700;color:#86efac;text-transform:uppercase;letter-spacing:.07em;margin:0}
.galeri-nama{font-family:var(--fd);font-size:15px;font-weight:700;color:white;line-height:1.25;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.galeri-nama.sm{font-size:13px}
.galeri-nama.xs{font-size:11px;-webkit-line-clamp:1}
.galeri-sub{font-size:11px;color:rgba(255,255,255,.8);margin:0}
.galeri-sub.xs{font-size:9px}
.galeri-badge-abs{position:absolute;top:8px;right:8px;color:white;font-size:8px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;padding:2px 7px}

/* HORIZONTAL SCROLL */
.h-scroll{display:flex;gap:16px;overflow-x:auto;scrollbar-width:none;padding-bottom:8px;scroll-snap-type:x mandatory}
.h-scroll::-webkit-scrollbar{display:none}
.hcard{flex-shrink:0;width:230px;scroll-snap-align:start;display:flex;flex-direction:column;background:white;border:1px solid var(--gray200);overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:box-shadow .2s,transform .2s}
.hcard:hover{box-shadow:0 8px 28px rgba(0,0,0,.13);transform:translateY(-3px)}
.hcard-img{position:relative;height:150px;overflow:hidden;flex-shrink:0}
.hcard-img img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s}
.hcard:hover .hcard-img img{transform:scale(1.06)}
.hcard-img-overlay{position:absolute;inset:0;opacity:.2}
.hcard-icon{position:absolute;top:8px;left:10px;font-size:22px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.4))}
.hcard-tingkat{position:absolute;bottom:8px;right:8px;color:white;font-size:8px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:2px 8px}
.hcard-body{padding:12px 14px;display:flex;flex-direction:column;gap:6px;flex:1}
.hcard-juara{font-size:10px;font-weight:800;color:var(--g700);text-transform:uppercase;letter-spacing:.07em;margin:0}
.hcard-nama{font-family:var(--fd);font-size:13px;font-weight:700;color:var(--gray900);line-height:1.35;margin:0;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.hcard-meta{display:flex;flex-direction:column;gap:4px;margin-top:4px;padding-top:8px;border-top:1px solid var(--gray100)}
.hcard-meta-item{display:flex;align-items:center;gap:5px;font-size:10px;color:var(--gray500)}
.hcard-meta-item svg{flex-shrink:0;color:var(--gray400)}

/* SEARCH */
.filter-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.clear-btn{padding:7px 16px;font-size:12px;font-weight:600;border:1.5px solid var(--gray200);background:white;color:var(--gray500);cursor:pointer;transition:all .2s;font-family:var(--fb)}
.clear-btn:hover{border-color:var(--g600);color:var(--g700)}
.filter-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:900px){.filter-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:620px){.filter-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:400px){.filter-grid{grid-template-columns:1fr}}
.fcard{overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.07);transition:box-shadow .2s,transform .2s}
.fcard:hover{box-shadow:0 10px 32px rgba(0,0,0,.14);transform:translateY(-3px)}
.fcard-img{position:relative;height:200px;overflow:hidden}
.fcard-img img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .45s}
.fcard:hover .fcard-img img{transform:scale(1.06)}
.fcard-hover-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.92) 0%,rgba(0,0,0,.1) 55%,transparent 100%);opacity:0;transition:opacity .3s;display:flex;flex-direction:column;justify-content:flex-end;padding:14px}
.fcard:hover .fcard-hover-overlay{opacity:1}
.fcard-icon{font-size:22px;margin-bottom:4px}
.fcard-info{display:flex;flex-direction:column;gap:3px}
.fcard-juara{font-size:10px;font-weight:800;color:#86efac;text-transform:uppercase;letter-spacing:.07em;margin:0}
.fcard-nama{font-family:var(--fd);font-size:13px;font-weight:700;color:white;line-height:1.3;margin:2px 0 4px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
.fcard-meta{font-size:10px;color:rgba(255,255,255,.8);margin:0 0 2px}
.fcard-badge{position:absolute;top:8px;left:8px;color:white;font-size:8px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:2px 8px}

/* ══ KATEGORI ROWS ══════════════════════════════════════ */
.kat-rows{display:flex;flex-direction:column;gap:3px}
.kat-baris{
    display:grid;
    /* Baris A: [kb 2fr][kb 2fr][kst 1fr] — override per baris pakai class */
    height:260px;
    gap:3px;
    width:100%;
    overflow:hidden;
}
/* Baris A: 2 besar + 1 stack */
.baris-a{ grid-template-columns: 2fr 2fr 1fr }
/* Baris B: 1 stack + 2 besar */
.baris-b{ grid-template-columns: 1fr 2fr 2fr }

/* Foto besar */
.kb{
    position:relative;
    overflow:hidden;
    cursor:pointer;
    background:var(--gray100);
}
.kb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s}
.kb:hover img{transform:scale(1.05)}
.kb:hover .galeri-overlay{opacity:1}

/* Stack 2 kecil vertikal */
.kst{
    display:grid;
    grid-template-rows:1fr 1fr;
    gap:3px;
    overflow:hidden;
}

/* Foto kecil */
.ks{
    position:relative;
    overflow:hidden;
    cursor:pointer;
    background:var(--gray100);
}
.ks img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s}
.ks:hover img{transform:scale(1.05)}
.ks:hover .galeri-overlay{opacity:1}

.lihat-semua-wrap{display:flex;justify-content:center;padding:20px 0}
.lihat-semua-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 32px;border:2px solid var(--g600);background:white;color:var(--g700);font-size:13px;font-weight:700;font-family:var(--fb);cursor:pointer;transition:all .25s}
.lihat-semua-btn:hover{background:var(--g700);color:white}

@media(max-width:720px){.kat-baris{height:200px}}
@media(max-width:480px){.kat-baris{height:160px}.baris-a{grid-template-columns:1fr 1fr}.baris-b{grid-template-columns:1fr 1fr}.kst{display:none}}
/* ════════════════════════════════════════════════════════ */

.empty{text-align:center;padding:60px 0;color:var(--gray400);display:flex;flex-direction:column;align-items:center;gap:12px}
.empty p{font-size:13px}
</style>
