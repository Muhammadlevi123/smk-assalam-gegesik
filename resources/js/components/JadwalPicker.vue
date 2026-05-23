<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
    hasError?:  boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', val: string): void;
}>();

// ── Konstanta ──────────────────────────────────────────────────────
const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// ── Type ───────────────────────────────────────────────────────────
interface JadwalEntry {
    hari:          string;
    jamMulai:      string; // format "HH:MM" untuk input type="time"
    jamSelesai:    string;
    sampaiSelesai: boolean;
}

// ── Helpers format ────────────────────────────────────────────────
// "08:00" → "08.00"
const toDisplay = (t: string) => t.replace(':', '.');
// "08.00" → "08:00"
const toTime    = (t: string) => t.replace('.', ':');

// ── State ──────────────────────────────────────────────────────────
const entries = ref<JadwalEntry[]>([]);

// ── Parse modelValue (untuk edit) ────────────────────────────────
// Format: "Senin 08.00–10.00; Rabu 13.00–Selesai"
const parseValue = (val: string) => {
    if (!val) return;
    const parsed: JadwalEntry[] = [];
    const parts = val.split('; ');
    for (const part of parts) {
        const match = part.match(/^(\w+)\s+(\d{2}\.\d{2})–([\d.]+|Selesai)$/);
        if (match) {
            const [, hari, mulai, akhir] = match;
            if (HARI.includes(hari)) {
                parsed.push({
                    hari,
                    jamMulai:      toTime(mulai),
                    jamSelesai:    akhir === 'Selesai' ? '17:00' : toTime(akhir),
                    sampaiSelesai: akhir === 'Selesai',
                });
            }
        }
    }
    if (parsed.length > 0) entries.value = parsed;
};

parseValue(props.modelValue);
watch(() => props.modelValue, (val) => {
    if (val !== buildResult()) parseValue(val);
});

// ── Toggle hari ───────────────────────────────────────────────────
const toggleHari = (hari: string) => {
    const idx = entries.value.findIndex(e => e.hari === hari);
    if (idx === -1) {
        const newEntries = [...entries.value, {
            hari,
            jamMulai:      '15:00',
            jamSelesai:    '17:00',
            sampaiSelesai: false,
        }];
        entries.value = HARI.flatMap(h => newEntries.filter(e => e.hari === h));
    } else {
        entries.value.splice(idx, 1);
    }
};

const selectedHari = computed(() => entries.value.map(e => e.hari));

// ── Build result ──────────────────────────────────────────────────
const buildResult = () => {
    if (entries.value.length === 0) return '';
    return entries.value
        .map(e => {
            const akhir = e.sampaiSelesai ? 'Selesai' : toDisplay(e.jamSelesai);
            return `${e.hari} ${toDisplay(e.jamMulai)}–${akhir}`;
        })
        .join('; ');
};

watch(entries, () => {
    emit('update:modelValue', buildResult());
}, { deep: true });

// ── Preview ───────────────────────────────────────────────────────
const hasEntries  = computed(() => entries.value.length > 0);
const previewLines = computed(() =>
    entries.value.map(e => ({
        hari: e.hari,
        jam:  e.sampaiSelesai
            ? `${toDisplay(e.jamMulai)} – Selesai`
            : `${toDisplay(e.jamMulai)} – ${toDisplay(e.jamSelesai)}`,
    }))
);
</script>

<template>
    <div class="space-y-3">

        <!-- Pilihan Hari -->
        <div class="space-y-1.5">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Pilih Hari <span class="text-gray-400 ml-1">(bisa lebih dari satu)</span>
            </p>
            <div class="flex flex-wrap gap-1.5">
                <button
                    v-for="hari in HARI" :key="hari"
                    type="button"
                    @click="toggleHari(hari)"
                    :class="[
                        'px-2.5 py-1 rounded-md text-xs font-semibold border transition-all select-none',
                        selectedHari.includes(hari)
                            ? 'bg-blue-600 border-blue-600 text-white shadow-sm'
                            : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-blue-300 hover:bg-blue-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:border-blue-600 dark:hover:bg-blue-900/20'
                    ]"
                >
                    {{ hari.slice(0, 3) }}
                </button>
            </div>
        </div>

        <!-- Baris jam per hari -->
        <div v-if="entries.length > 0" class="space-y-1.5">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Jam per Hari</p>
            <div class="space-y-1.5">
                <div
                    v-for="entry in entries" :key="entry.hari"
                    class="flex items-center gap-2 flex-wrap rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <span class="w-14 flex-shrink-0 text-xs font-bold text-gray-900 dark:text-white">{{ entry.hari }}</span>

                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Mulai</span>
                        <input type="time" v-model="entry.jamMulai"
                            class="rounded-md border-0 bg-white py-1 px-2 text-xs text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-blue-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 cursor-pointer" />
                    </div>

                    <span class="text-gray-300 text-xs">–</span>

                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Selesai</span>
                        <input v-if="!entry.sampaiSelesai" type="time" v-model="entry.jamSelesai"
                            class="rounded-md border-0 bg-white py-1 px-2 text-xs text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-blue-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 cursor-pointer" />
                        <span v-else class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-500 italic">Selesai</span>
                    </div>

                    <label class="flex items-center gap-1 cursor-pointer select-none ml-auto">
                        <div class="relative flex-shrink-0">
                            <input type="checkbox" v-model="entry.sampaiSelesai" class="sr-only peer" />
                            <div class="w-7 h-3.5 bg-gray-200 rounded-full peer-checked:bg-blue-600 dark:bg-gray-600 transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-2.5 h-2.5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-3.5"></div>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">s/d selesai</span>
                    </label>

                    <button type="button" @click="toggleHari(entry.hari)"
                        class="flex-shrink-0 w-5 h-5 rounded-full bg-red-100 hover:bg-red-200 dark:bg-red-900/20 dark:hover:bg-red-900/40 flex items-center justify-center transition-colors"
                        title="Hapus hari ini">
                        <svg class="w-3 h-3 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 py-4 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-500">Pilih hari di atas untuk mengatur jadwal</p>
        </div>

        <!-- Preview ringkasan -->
        <div v-if="hasEntries" class="rounded-lg bg-blue-50 border border-blue-200 dark:bg-blue-900/10 dark:border-blue-800 px-3 py-2 space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400">Ringkasan</p>
            <div class="flex flex-wrap gap-x-4 gap-y-0.5">
                <div v-for="line in previewLines" :key="line.hari" class="flex items-center gap-1 text-xs">
                    <span class="font-semibold text-blue-900 dark:text-blue-200">{{ line.hari }}</span>
                    <span class="text-blue-600 dark:text-blue-400">{{ line.jam }}</span>
                </div>
            </div>
        </div>

    </div>
</template>
