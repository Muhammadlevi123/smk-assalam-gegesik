<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavUser from '../../components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';

interface SearchResult {
    type:       string;
    type_label: string;
    id:         number;
    title:      string;
    excerpt:    string;
    url:        string;
    date:       string | null;
}

interface Props {
    query:   string;
    results: SearchResult[];
    total:   number;
}

const props = defineProps<Props>();
const searchQuery = ref(props.query);

const doSearch = () => {
    if (searchQuery.value.trim().length < 2) return;
    router.get('/search', { q: searchQuery.value }, { preserveState: false });
};

// Warna badge per tipe
const typeColor: Record<string, string> = {
    berita:      'bg-blue-100 text-blue-700',
    artikel:     'bg-purple-100 text-purple-700',
    prestasi:    'bg-amber-100 text-amber-700',
    guru:        'bg-green-100 text-green-700',
    tenaga:      'bg-teal-100 text-teal-700',
    organisasi:  'bg-indigo-100 text-indigo-700',
};

const getTypeColor = (type: string) => typeColor[type] ?? 'bg-gray-100 text-gray-700';

// Group hasil per tipe
const grouped = Object.entries(
    props.results.reduce((acc: Record<string, SearchResult[]>, item) => {
        if (!acc[item.type_label]) acc[item.type_label] = [];
        acc[item.type_label].push(item);
        return acc;
    }, {})
);
</script>

<template>
    <Head :title="query ? `Hasil pencarian: ${query}` : 'Pencarian'" />

    <NavUser />

    <main class="search-page">

        <!-- COVER -->
        <section class="hero">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
        </section>

        <div class="page-bg">
            <div class="page-wrap">
                <div class="search-body">

                    <!-- Belum ada query -->
                    <div v-if="!query" class="search-empty">
                        <div class="empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3>Ketik kata kunci untuk mencari</h3>
                        <p>Cari berita, artikel, prestasi, tenaga pendidik, kependidikan, dan ekstrakurikuler.</p>
                    </div>

                    <!-- Query terlalu pendek -->
                    <div v-else-if="query && query.length < 2" class="search-empty">
                        <div class="empty-icon">⚠️</div>
                        <h3>Kata kunci terlalu pendek</h3>
                        <p>Masukkan minimal 2 karakter untuk mencari.</p>
                    </div>

                    <!-- Tidak ada hasil -->
                    <div v-else-if="query && total === 0" class="search-empty">
                        <div class="empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3>Tidak ada hasil untuk "<strong>{{ query }}</strong>"</h3>
                        <p>Coba kata kunci lain atau periksa ejaan.</p>
                    </div>

                    <!-- Ada hasil -->
                    <div v-else class="search-results">
                        <div class="results-meta">
                            Ditemukan <strong>{{ total }}</strong> hasil untuk "<strong>{{ query }}</strong>"
                        </div>

                        <!-- Group per kategori -->
                        <div v-for="[label, items] in grouped" :key="label" class="result-group">
                            <h2 class="group-title">{{ label }}</h2>
                            <div class="result-list">
                                <Link
                                    v-for="item in items"
                                    :key="`${item.type}-${item.id}`"
                                    :href="item.url"
                                    class="result-card"
                                >
                                    <div class="result-content">
                                        <div class="result-meta-row">
                                            <span :class="['result-badge', getTypeColor(item.type)]">{{ item.type_label }}</span>
                                            <span v-if="item.date" class="result-date">{{ item.date }}</span>
                                        </div>
                                        <h3 class="result-title">{{ item.title }}</h3>
                                        <p class="result-excerpt">{{ item.excerpt }}</p>
                                    </div>
                                    <div class="result-arrow">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <FooterUser />
</template>

<style scoped>
.search-page {
    min-height: 100vh;
    background: #f9fafb;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.hero { position: relative; height: 220px; overflow: hidden; }
.hero-bg {
    position: absolute; inset: 0;
    background: url('/storage/img/landingpage/cover4.png') center/cover no-repeat;
}
.hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(10,40,20,.85), rgba(22,101,52,.65));
}
@media (max-width: 768px) { .hero { height: 130px; } }

.page-bg { background: #f9fafb; padding: 20px 0 80px; }
.page-wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; display: flex; flex-direction: column; gap: 32px; }

.search-body { display: flex; flex-direction: column; gap: 0; }

.search-empty { text-align: center; padding: 80px 24px; }
.empty-icon {
    width: 72px; height: 72px;
    background: #f3f4f6; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px; font-size: 28px;
}
.empty-icon svg { width: 36px; height: 36px; color: #9ca3af; }
.search-empty h3 { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.search-empty p { font-size: 14px; color: #6b7280; }

.results-meta {
    font-size: 14px; color: #6b7280;
    margin-bottom: 32px; padding-bottom: 16px;
    border-bottom: 1px solid #e5e7eb;
}
.results-meta strong { color: #111827; }

.result-group { margin-bottom: 40px; }
.group-title {
    font-size: 13px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: #6b7280; margin-bottom: 12px;
    padding-bottom: 8px; border-bottom: 2px solid #e5e7eb;
}

.result-list { display: flex; flex-direction: column; gap: 12px; }

.result-card {
    display: flex; align-items: center; gap: 16px;
    background: white; border: 1px solid #e5e7eb;
    border-radius: 12px; padding: 16px;
    text-decoration: none; transition: all 0.2s ease;
}
.result-card:hover {
    border-color: #16a34a;
    box-shadow: 0 4px 16px rgba(22,163,74,0.1);
    transform: translateY(-1px);
}

.result-content { flex: 1; min-width: 0; }
.result-meta-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.result-badge {
    display: inline-flex; align-items: center;
    padding: 2px 10px; border-radius: 50px;
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.result-date { font-size: 12px; color: #9ca3af; }

.result-title {
    font-size: 15px; font-weight: 700; color: #111827;
    margin-bottom: 4px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.result-card:hover .result-title { color: #15803d; }

.result-excerpt {
    font-size: 13px; color: #6b7280; line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}

.result-arrow { flex-shrink: 0; color: #d1d5db; transition: color 0.2s, transform 0.2s; }
.result-arrow svg { width: 18px; height: 18px; }
.result-card:hover .result-arrow { color: #15803d; transform: translateX(3px); }

@media (max-width: 640px) {
    .result-title { font-size: 14px; white-space: normal; }
    .result-arrow { display: none; }
}
</style>
