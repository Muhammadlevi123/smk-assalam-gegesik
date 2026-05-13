<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import NavUser from '@/components/NavUser.vue';
import FooterUser from '@/components/FooterUser.vue';
import { onMounted, onUnmounted, computed, ref, watch, nextTick } from 'vue';

interface KalenderItem {
    id: number;
    judul: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    tanggal_display: string;
    bulan: string;
}
interface TahunAjaranOption {
    id: number;
    tahun: string;
}

const props = defineProps<{
    kalender?: KalenderItem[];
    tahun_ajaran_aktif?: string;
    tahun_ajaran_id?: number;
    semua_tahun_ajaran?: TahunAjaranOption[];
}>();

const BULAN_NAMA = [
    'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'
];

// ✅ FIX: parse tanggal string sebagai local time (bukan UTC)
const parseLocalDate = (str: string): Date => new Date(str + 'T00:00:00');

// ✅ FIX: today pakai local date (bukan UTC via toISOString)
const getLocalDateStr = (d: Date): string =>
    `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

const today = getLocalDateStr(new Date());

// ─── Navigasi bulan ─────────────────────────────
const getBulanPertama = () => {
    if (props.kalender && props.kalender.length > 0)
        return parseLocalDate(props.kalender[0].tanggal_mulai);
    const t = new Date();
    return new Date(t.getFullYear(), t.getMonth(), 1);
};
const bulanTampil = ref(getBulanPertama());
watch(() => props.kalender, () => { bulanTampil.value = getBulanPertama(); });

const bulanLabel = computed(() =>
    BULAN_NAMA[bulanTampil.value.getMonth()] + ' ' + bulanTampil.value.getFullYear()
);
const prevBulan = () => { bulanTampil.value = new Date(bulanTampil.value.getFullYear(), bulanTampil.value.getMonth() - 1, 1); };
const nextBulan = () => { bulanTampil.value = new Date(bulanTampil.value.getFullYear(), bulanTampil.value.getMonth() + 1, 1); };

// ─── Sel kalender ───────────────────────────────
const selKalender = computed(() => {
    const thn = bulanTampil.value.getFullYear();
    const bln = bulanTampil.value.getMonth();
    const hariPertama = new Date(thn, bln, 1).getDay();
    const totalHari   = new Date(thn, bln + 1, 0).getDate();
    const sel: Array<{ tgl: number | null; tanggalStr: string | null }> = [];
    for (let i = 0; i < hariPertama; i++) sel.push({ tgl: null, tanggalStr: null });
    for (let d = 1; d <= totalHari; d++) {
        const s = `${thn}-${String(bln+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        sel.push({ tgl: d, tanggalStr: s });
    }
    while (sel.length % 7 !== 0) sel.push({ tgl: null, tanggalStr: null });
    return sel;
});

// ─── Map tanggal → event ────────────────────────
const eventPadaTanggal = computed(() => {
    const map: Record<string, KalenderItem[]> = {};
    props.kalender?.forEach(item => {
        if (!item.tanggal_mulai) return;
        // ✅ FIX: pakai parseLocalDate agar tidak ada offset UTC
        const mulai   = parseLocalDate(item.tanggal_mulai);
        const selesai = item.tanggal_selesai ? parseLocalDate(item.tanggal_selesai) : new Date(mulai);
        const cur = new Date(mulai);
        while (cur <= selesai) {
            // ✅ FIX: pakai getLocalDateStr agar key tanggal akurat
            const key = getLocalDateStr(cur);
            if (!map[key]) map[key] = [];
            if (!map[key].find(e => e.id === item.id)) map[key].push(item);
            cur.setDate(cur.getDate() + 1);
        }
    });
    return map;
});

const isToday = (s: string | null) => s === today;

// ─── Popover ────────────────────────────────────
const showPopover     = ref(false);
const selectedDate    = ref<string | null>(null);
const selectedEvents  = computed(() =>
    selectedDate.value ? (eventPadaTanggal.value[selectedDate.value] ?? []) : []
);
const selectedDateLabel = computed(() => {
    if (!selectedDate.value) return '';
    const d = parseLocalDate(selectedDate.value);
    return d.getDate() + ' ' + BULAN_NAMA[d.getMonth()] + ' ' + d.getFullYear();
});

const anchorEl = ref<HTMLElement | null>(null);
const popoverStyle = ref<Record<string, string>>({});
const arrowDir     = ref<'up'|'down'>('up');

const POPOVER_W = 260;
const POPOVER_H = 200;

const popoverEl = ref<HTMLElement | null>(null);

const hitungPosisi = () => {
    if (!anchorEl.value) return;
    const r    = anchorEl.value.getBoundingClientRect();
    const pH   = popoverEl.value ? popoverEl.value.offsetHeight : POPOVER_H;

    let left = r.left + r.width / 2 - POPOVER_W / 2;
    left = Math.max(8, Math.min(left, window.innerWidth - POPOVER_W - 8));

    const spaceBelow = window.innerHeight - r.bottom;
    if (spaceBelow >= pH + 16) {
        arrowDir.value = 'up';
        popoverStyle.value = {
            top:   (r.bottom + 8) + 'px',
            left:  left + 'px',
            width: POPOVER_W + 'px',
        };
    } else {
        arrowDir.value = 'down';
        popoverStyle.value = {
            top:   (r.top - pH - 8) + 'px',
            left:  left + 'px',
            width: POPOVER_W + 'px',
        };
    }
};

const onScroll = () => { if (showPopover.value) hitungPosisi(); };
const onResize = () => { if (showPopover.value) hitungPosisi(); };

const pilihTanggal = (tglStr: string | null, event?: MouseEvent) => {
    if (!tglStr || !eventPadaTanggal.value[tglStr]) {
        showPopover.value = false;
        selectedDate.value = null;
        anchorEl.value = null;
        return;
    }
    if (selectedDate.value === tglStr && showPopover.value) {
        showPopover.value = false;
        selectedDate.value = null;
        anchorEl.value = null;
        return;
    }
    selectedDate.value = tglStr;
    anchorEl.value = event?.currentTarget as HTMLElement ?? null;
    showPopover.value = true;
    nextTick(() => hitungPosisi());
};

const tutupPopover = () => {
    showPopover.value = false;
    selectedDate.value = null;
    anchorEl.value = null;
};

// ─── Tahun ajaran ───────────────────────────────
const gantiTahunAjaran = (id: number) => {
    router.get('/informasi/kalender-akademik', { tahun_ajaran_id: id }, { preserveScroll: false });
};

// ─── Format tanggal tabel ───────────────────────
const formatTgl = (str: string) => {
    if (!str) return '-';
    // ✅ FIX: pakai parseLocalDate agar tidak offset
    const d = parseLocalDate(str);
    return d.getDate() + ' ' + BULAN_NAMA[d.getMonth()] + ' ' + d.getFullYear();
};

// ─── Lifecycle ──────────────────────────────────
onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onResize);

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
onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onResize);
});
</script>

<template>
    <Head title="Kalender Akademik - SMK Assalam Gegesik" />
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
                <article class="article fade-in" style="margin-top:-70px;position:relative;z-index:10;">

                    <!-- Breadcrumb -->
                    <nav class="bc-nav">
                        <Link href="/" class="bc-link">Beranda</Link>
                        <span class="bc-sep">&#x203A;</span>
                        <span class="bc-link">Informasi</span>
                        <span class="bc-sep">&#x203A;</span>
                        <span class="bc-current">Kalender Akademik</span>
                    </nav>

                    <!-- Header -->
                    <div class="article-header">
                        <div class="article-line"></div>
                        <h1 class="article-title">Kalender Akademik</h1>
                        <p class="article-subtitle">
                            Jadwal kegiatan akademik SMK Assalam Gegesik
                        </p>
                    </div>

                    <!-- Pilih TA -->
                    <div v-if="semua_tahun_ajaran && semua_tahun_ajaran.length > 0" class="ta-selector fade-in">
                        <label class="ta-label">Tahun Ajaran</label>
                        <select class="ta-select" :value="tahun_ajaran_id"
                            @change="gantiTahunAjaran(Number(($event.target as HTMLSelectElement).value))">
                            <option v-for="ta in semua_tahun_ajaran" :key="ta.id" :value="ta.id">{{ ta.tahun }}</option>
                        </select>
                    </div>

                    <!-- Empty -->
                    <div v-if="!kalender || kalender.length === 0" class="empty-state fade-in">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="empty-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p>Belum ada kalender akademik untuk tahun ajaran ini.</p>
                    </div>

                    <template v-else>

                        <!-- KALENDER -->
                        <div class="cal-wrapper fade-in">

                            <!-- Nav bulan -->
                            <div class="cal-nav">
                                <button class="cal-nav-btn" @click="prevBulan">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="cal-bulan-label">{{ bulanLabel }}</span>
                                <button class="cal-nav-btn" @click="nextBulan">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Grid -->
                            <div class="cal-grid">
                                <!-- Header hari -->
                                <div v-for="(h, hi) in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']" :key="h"
                                    class="cal-head-cell" :class="hi===0||hi===6 ? 'weekend-label' : ''">{{ h }}</div>

                                <!-- Sel -->
                                <div
                                    v-for="(sel, idx) in selKalender" :key="idx"
                                    class="cal-cell"
                                    :class="{
                                        'cal-empty':    !sel.tgl,
                                        'cal-today':    isToday(sel.tanggalStr),
                                        'cal-selected': selectedDate === sel.tanggalStr && showPopover,
                                        'cal-has-ev':   !!sel.tanggalStr && !!eventPadaTanggal[sel.tanggalStr],
                                        'cal-weekend':  !!sel.tgl && (idx%7===0||idx%7===6),
                                    }"
                                    @click="pilihTanggal(sel.tanggalStr, $event)"
                                >
                                    <span v-if="sel.tgl" class="cal-tgl">{{ sel.tgl }}</span>
                                    <template v-if="sel.tanggalStr && eventPadaTanggal[sel.tanggalStr]">
                                        <div v-for="(ev,ei) in eventPadaTanggal[sel.tanggalStr].slice(0,2)" :key="ei" class="ev-pill">
                                            {{ ev.judul }}
                                        </div>
                                        <div v-if="eventPadaTanggal[sel.tanggalStr].length > 2" class="ev-more">
                                            +{{ eventPadaTanggal[sel.tanggalStr].length - 2 }}
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Hint -->
                            <div class="cal-hint">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Klik tanggal hijau untuk melihat detail kegiatan
                            </div>
                        </div>

                        <!-- Legenda -->
                        <div class="cal-legend fade-in">
                            <div class="legend-item"><span class="leg-dot today"></span>Hari ini</div>
                            <div class="legend-item"><span class="leg-dot event"></span>Ada kegiatan</div>
                            <div class="legend-item"><span class="leg-dot selected"></span>Dipilih</div>
                        </div>

                        <!-- Rangkuman tabel -->
                        <div class="rangkuman fade-in">
                            <div class="article-line"></div>
                            <h2 class="rangkuman-title">Daftar Kegiatan</h2>
                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th class="th-no">No</th>
                                            <th>Kegiatan</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, i) in kalender" :key="item.id" class="tr-row">
                                            <td class="td-no">{{ i + 1 }}</td>
                                            <td class="td-judul">{{ item.judul }}</td>
                                            <td class="td-tgl">{{ formatTgl(item.tanggal_mulai) }}</td>
                                            <td class="td-tgl">
                                                {{ item.tanggal_selesai && item.tanggal_selesai !== item.tanggal_mulai
                                                    ? formatTgl(item.tanggal_selesai) : '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </template>
                </article>
            </div>
        </div>

        <FooterUser />

        <!-- OVERLAY: tutup popover saat klik luar -->
        <div v-if="showPopover" class="popover-overlay" @click="tutupPopover"></div>

        <!-- POPOVER: fixed, mengikuti anchor saat scroll -->
        <Transition name="pop">
            <div
                v-if="showPopover && selectedEvents.length > 0"
                ref="popoverEl"
                class="popover-box"
                :class="arrowDir === 'up' ? 'arr-up' : 'arr-down'"
                :style="popoverStyle"
            >
                <div class="pop-header">
                    <span class="pop-date">{{ selectedDateLabel }}</span>
                    <button class="pop-close" @click="tutupPopover">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="pop-body">
                    <div v-for="ev in selectedEvents" :key="ev.id" class="pop-item">
                        <span class="pop-dot"></span>
                        <div>
                            <p class="pop-judul">{{ ev.judul }}</p>
                            <p class="pop-range">{{ ev.tanggal_display }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.page-root {
    --g500:#22c55e;--g600:#16a34a;--g700:#15803d;--g800:#166534;
    --gray50:#f9fafb;--gray100:#f3f4f6;--gray200:#e5e7eb;--gray300:#d1d5db;
    --gray400:#9ca3af;--gray500:#6b7280;--gray600:#4b5563;--gray700:#374151;--gray900:#111827;
    --fd:'Fraunces',Georgia,serif;--fb:'Plus Jakarta Sans',sans-serif;
    font-family:var(--fb); color:var(--gray900); min-height:100vh;
}
.fade-in{opacity:0;transform:translateY(20px);transition:opacity .6s ease,transform .6s ease}
.fade-in.visible{opacity:1;transform:none}

.cover-section{position:relative;height:340px;overflow:visible}
.cover-bg{position:absolute;inset:0;background:url('/storage/img/landingpage/cover4.png') center/cover no-repeat}
.cover-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(10,40,20,.72),rgba(22,101,52,.55))}
@media(max-width:768px){.cover-section{height:220px}}

.page-bg{background:var(--gray50);padding:0 24px 72px}
.page-wrap{max-width:900px;margin:0 auto}

.article{background:white;box-shadow:0 4px 24px rgba(0,0,0,.1);border:1px solid var(--gray100);padding:36px 44px 52px;display:flex;flex-direction:column;gap:22px}
@media(max-width:600px){.article{padding:24px 16px 40px}}

.bc-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid var(--gray100)}
.bc-link{font-size:13px;color:var(--gray400);text-decoration:none;transition:color .2s}
.bc-link:hover{color:var(--g600)}.bc-sep{font-size:13px;color:var(--gray300)}
.bc-current{font-size:13px;font-weight:600;color:var(--g700)}

.article-header{margin:0}
.article-line{width:40px;height:3px;background:var(--g600);border-radius:2px;margin-bottom:14px}
.article-title{font-family:var(--fd);font-size:clamp(22px,3.5vw,32px);font-weight:700;color:var(--gray900);margin:0 0 6px;line-height:1.2}
.article-subtitle{font-size:14px;color:var(--gray400);font-style:italic;margin:0}

.ta-selector{display:flex;align-items:center;gap:12px}
.ta-label{font-size:13px;font-weight:600;color:var(--gray600);white-space:nowrap}
.ta-select{padding:7px 12px;font-size:13px;border:1.5px solid var(--gray200);border-radius:8px;color:var(--gray900);background:white;cursor:pointer;outline:none;transition:border-color .2s}
.ta-select:focus{border-color:var(--g500)}

/* KALENDER */
.cal-wrapper{border:1px solid var(--gray200);border-radius:10px;overflow:hidden}
.cal-nav{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:var(--g700)}
.cal-bulan-label{font-family:var(--fd);font-size:18px;font-weight:700;color:white}
.cal-nav-btn{background:rgba(255,255,255,.15);border:none;border-radius:8px;width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:white;transition:background .2s}
.cal-nav-btn:hover{background:rgba(255,255,255,.28)}

.cal-grid{display:grid;grid-template-columns:repeat(7,1fr)}
.cal-head-cell{padding:10px 4px;text-align:center;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--gray500);background:var(--gray50);border-bottom:1px solid var(--gray200)}
.weekend-label{color:var(--g600)}

.cal-cell{min-height:80px;padding:8px 6px 6px;border-right:1px solid var(--gray100);border-bottom:1px solid var(--gray100);cursor:pointer;transition:background .15s;display:flex;flex-direction:column;gap:3px}
.cal-cell:nth-child(7n){border-right:none}
.cal-cell:hover:not(.cal-empty){background:var(--gray50)}
.cal-empty{background:var(--gray50);cursor:default;opacity:.35}
.cal-weekend{background:#fafafa}
.cal-today{background:#f0fdf4 !important}
.cal-selected{background:#dcfce7 !important;outline:2px solid var(--g500);outline-offset:-2px}
.cal-has-ev{cursor:pointer}

.cal-tgl{font-size:13px;font-weight:700;color:var(--gray700);width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:50%;flex-shrink:0}
.cal-today .cal-tgl{background:var(--g600);color:white}

.ev-pill{font-size:10px;font-weight:600;color:var(--g700);padding:1px 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;line-height:1.4;display:flex;align-items:center;gap:3px}
.ev-pill::before{content:'';width:5px;height:5px;background:var(--g500);border-radius:50%;flex-shrink:0}
.ev-more{font-size:10px;color:var(--g700);font-weight:600;padding:0 4px}

.cal-hint{display:flex;align-items:center;gap:6px;padding:10px 14px;font-size:11px;color:var(--gray400);background:var(--gray50);border-top:1px solid var(--gray100)}

/* Legenda */
.cal-legend{display:flex;align-items:center;gap:18px}
.legend-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray500)}
.leg-dot{width:11px;height:11px;border-radius:50%;flex-shrink:0}
.leg-dot.today{background:var(--g600)}.leg-dot.event{background:var(--g500)}.leg-dot.selected{background:#86efac;border:2px solid var(--g500)}

/* Rangkuman */
.rangkuman{display:flex;flex-direction:column;gap:14px}
.rangkuman-title{font-family:var(--fd);font-size:20px;font-weight:700;color:var(--gray900);margin:0;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.table-wrap{border:1px solid var(--gray200);border-radius:10px;overflow:hidden;overflow-x:auto}
.data-table{width:100%;border-collapse:collapse;font-size:13px}
.data-table thead{background:var(--gray50)}
.data-table th{padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--gray500);letter-spacing:.06em;text-transform:uppercase;border-bottom:1px solid var(--gray200);white-space:nowrap}
.th-no{width:48px;text-align:center}
.tr-row{border-bottom:1px solid var(--gray100);transition:background .15s}
.tr-row:last-child{border-bottom:none}.tr-row:hover{background:var(--gray50)}
.data-table td{padding:11px 16px;vertical-align:middle}
.td-no{text-align:center;font-size:12px;color:var(--gray400)}
.td-judul{font-size:13px;font-weight:600;color:var(--gray900)}
.td-tgl{font-size:13px;color:var(--gray600);white-space:nowrap}

.empty-state{text-align:center;padding:48px 0;color:var(--gray400)}
.empty-icon{display:block;margin:0 auto 14px}
.empty-state p{font-size:14px}

.popover-overlay{position:fixed;inset:0;z-index:998;background:transparent}

.pop-enter-active{transition:opacity .18s ease,transform .2s cubic-bezier(.34,1.56,.64,1)}
.pop-leave-active{transition:opacity .12s ease,transform .12s ease}
.pop-enter-from,.pop-leave-to{opacity:0;transform:scale(.9) translateY(-6px)}

@media(max-width:540px){
    .cal-cell{min-height:52px;padding:4px 2px}
    .cal-tgl{font-size:11px;width:20px;height:20px}
    .ev-pill{display:none}
    .cal-has-ev .cal-tgl::after{content:'';display:block;width:5px;height:5px;background:var(--g500);border-radius:50%;margin:2px auto 0}
    .cal-head-cell{font-size:9px;padding:8px 2px}
}
</style>

<style>
.popover-box {
    position: fixed;
    z-index: 999;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.15), 0 1px 4px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    overflow: visible;
}
.popover-box.arr-down::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    border-top: 12px solid white;
    filter: drop-shadow(0 2px 2px rgba(0,0,0,0.08));
}
.popover-box.arr-down::before {
    content: '';
    position: absolute;
    bottom: -14px;
    left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 13px solid transparent;
    border-right: 13px solid transparent;
    border-top: 13px solid #e5e7eb;
}
.popover-box.arr-up::after {
    content: '';
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    border-bottom: 12px solid white;
    filter: drop-shadow(0 -2px 2px rgba(0,0,0,0.06));
}
.popover-box.arr-up::before {
    content: '';
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 13px solid transparent;
    border-right: 13px solid transparent;
    border-bottom: 13px solid #e5e7eb;
}
.pop-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 11px 14px 10px;
    border-bottom: 1px solid #f3f4f6;
    border-radius: 10px 10px 0 0;
    background: white;
}
.pop-date {
    font-size: 13px; font-weight: 700;
    color: #15803d;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.pop-close {
    width: 22px; height: 22px; border-radius: 6px;
    border: none; background: #f3f4f6; color: #6b7280;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .2s; flex-shrink: 0;
}
.pop-close:hover { background: #e5e7eb; color: #374151; }
.pop-body { padding: 6px 0; max-height: 200px; overflow-y: auto; border-radius: 0 0 10px 10px; }
.pop-item { display: flex; align-items: flex-start; gap: 10px; padding: 8px 14px; border-bottom: 1px solid #f9fafb; }
.pop-item:last-child { border-bottom: none; }
.pop-dot { width: 7px; height: 7px; flex-shrink: 0; background: #22c55e; border-radius: 50%; margin-top: 5px; }
.pop-judul { font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 3px; line-height: 1.4; font-family: 'Plus Jakarta Sans', sans-serif; }
.pop-range { font-size: 11px; color: #6b7280; margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
</style>
