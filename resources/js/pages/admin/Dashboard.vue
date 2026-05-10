<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import { type BreadcrumbItem } from '../../types';
import { Head, router } from '@inertiajs/vue3';
import { Users, GraduationCap, School, BookOpen, UserCheck, FileText, Filter, RefreshCw } from 'lucide-vue-next';

// Props dari controller
interface Props {
  statistics: {
    total_siswa: number;
    siswa_aktif: number;
    siswa_lulus: number;
    siswa_pindah: number;
    siswa_nonaktif: number;
    total_guru: number;
    guru_aktif: number;
    guru_nonaktif: number;
    total_tenaga_kependidikan: number;
    tenaga_kependidikan_aktif: number;
    total_kelas: number;
    total_mata_pelajaran: number;
    total_alumni: number;
    total_organisasi: number;
  };
  chartData: {
    siswaPerTingkat: Array<{ tingkat: string; jumlah: number }>;
    siswaPerJurusan: Array<{ jurusan: string; jumlah: number }>;
    genderSiswa: Array<{ gender: string; jumlah: number }>;
    statusSiswa: Array<{ status: string; jumlah: number }>;
    perkembanganSiswa: Array<{ tahun_ajaran: string; jumlah_siswa: number }>;
    alumniPerTahun: Array<{ tahun: number; jumlah: number }>;
    guruPerMapel: Array<{ mata_pelajaran: string; jumlah_guru: number }>;
    kontenPerBulan: Array<{ bulan: string; artikel: number; berita: number; total: number }>;
    kelasPerTingkatJurusan: Array<{ kategori: string; tingkat: string; jurusan: string; jumlah: number }>;
  };
  tahunAjaranAktif: {
    id: number;
    tahun: string;
  } | null;
  tahunAjaranTerbaru: {
    id: number;
    tahun: string;
  } | null;
  tahunAjaranList: Array<{
    id: number;
    tahun: string;
    label: string;
  }>;
  selectedTahunAjaran: number | string;
  aktivitasTerbaru: Array<{
    type: string;
    title: string;
    date: string;
    kategori: string;
  }>;
  quickStats: {
    rasio_guru_siswa: number;
    rata_rata_siswa_per_kelas: number;
    persentase_alumni: number;
  };
  isFilteredByYear: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

// Chart refs
const siswaPerTingkatChart = ref<HTMLCanvasElement>();
const siswaPerJurusanChart = ref<HTMLCanvasElement>();
const genderChart = ref<HTMLCanvasElement>();
const statusSiswaChart = ref<HTMLCanvasElement>();
const perkembanganChart = ref<HTMLCanvasElement>();
const alumniChart = ref<HTMLCanvasElement>();
const guruPerMapelChart = ref<HTMLCanvasElement>();
const kontenChart = ref<HTMLCanvasElement>();

// Loading state
const isLoading = ref(false);

// Chart instances untuk destroy
let chartInstances: any = {};

// Function untuk format angka
const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('id-ID').format(num);
};

// Function untuk filter tahun ajaran
const filterByTahunAjaran = async (tahunAjaranId: number | string) => {
  if (isLoading.value) return;

  isLoading.value = true;

  const params: any = {};
  if (tahunAjaranId !== 'all') {
    params.tahun_ajaran_id = tahunAjaranId;
  }

  router.get(route('admin.dashboard'), params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Reset filter ke semua tahun ajaran
const resetFilter = () => {
  if (isLoading.value) return;

  isLoading.value = true;

  router.get(route('admin.dashboard'), {}, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false;
    }
  });
};

// Function untuk destroy semua chart instances
const destroyCharts = () => {
  Object.keys(chartInstances).forEach(key => {
    if (chartInstances[key]) {
      chartInstances[key].destroy();
      delete chartInstances[key];
    }
  });
};

// Chart initialization using Chart.js
const initCharts = async () => {
  try {
    // Dynamic import Chart.js untuk menghindari SSR issues
    const { Chart, registerables } = await import('chart.js');
    Chart.register(...registerables);

    // Destroy existing charts first
    destroyCharts();

    // Chart 1: Siswa per Tingkat (Pie Chart)
    if (siswaPerTingkatChart.value && props.chartData.siswaPerTingkat.length > 0) {
      chartInstances['siswaPerTingkat'] = new Chart(siswaPerTingkatChart.value, {
        type: 'pie',
        data: {
          labels: props.chartData.siswaPerTingkat.map(item => item.tingkat),
          datasets: [{
            data: props.chartData.siswaPerTingkat.map(item => item.jumlah),
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    // Chart 2: Siswa per Jurusan (Bar Chart)
    if (siswaPerJurusanChart.value && props.chartData.siswaPerJurusan.length > 0) {
      chartInstances['siswaPerJurusan'] = new Chart(siswaPerJurusanChart.value, {
        type: 'bar',
        data: {
          labels: props.chartData.siswaPerJurusan.map(item => item.jurusan),
          datasets: [{
            label: 'Jumlah Siswa',
            data: props.chartData.siswaPerJurusan.map(item => item.jumlah),
            backgroundColor: '#3B82F6',
            borderColor: '#2563EB',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    }

    // Chart 3: Gender Distribution (Doughnut Chart)
    if (genderChart.value && props.chartData.genderSiswa.length > 0) {
      chartInstances['genderSiswa'] = new Chart(genderChart.value, {
        type: 'doughnut',
        data: {
          labels: props.chartData.genderSiswa.map(item => {
            // Handle berbagai format gender
            if (item.gender === 'L' || item.gender === 'Laki-laki' || item.gender === 'laki-laki') {
              return 'Laki-laki';
            } else if (item.gender === 'P' || item.gender === 'Perempuan' || item.gender === 'perempuan') {
              return 'Perempuan';
            }
            return item.gender; // fallback untuk format lain
          }),
          datasets: [{
            data: props.chartData.genderSiswa.map(item => item.jumlah),
            backgroundColor: ['#3B82F6', '#EF4444'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    // Chart 4: Status Siswa (Doughnut Chart)
    if (statusSiswaChart.value && props.chartData.statusSiswa.length > 0) {
      chartInstances['statusSiswa'] = new Chart(statusSiswaChart.value, {
        type: 'doughnut',
        data: {
          labels: props.chartData.statusSiswa.map(item => item.status),
          datasets: [{
            data: props.chartData.statusSiswa.map(item => item.jumlah),
            backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    // Chart 5: Perkembangan Siswa (Line Chart)
    if (perkembanganChart.value && props.chartData.perkembanganSiswa.length > 0) {
      chartInstances['perkembanganSiswa'] = new Chart(perkembanganChart.value, {
        type: 'line',
        data: {
          labels: props.chartData.perkembanganSiswa.map(item => item.tahun_ajaran),
          datasets: [{
            label: 'Jumlah Siswa',
            data: props.chartData.perkembanganSiswa.map(item => item.jumlah_siswa),
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }

    // Chart 6: Alumni per Tahun (Bar Chart)
    if (alumniChart.value && props.chartData.alumniPerTahun.length > 0) {
      chartInstances['alumniPerTahun'] = new Chart(alumniChart.value, {
        type: 'bar',
        data: {
          labels: props.chartData.alumniPerTahun.map(item => item.tahun.toString()),
          datasets: [{
            label: 'Jumlah Alumni',
            data: props.chartData.alumniPerTahun.map(item => item.jumlah),
            backgroundColor: '#10B981',
            borderColor: '#059669',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    }

    // Chart 7: Guru per Mata Pelajaran (Horizontal Bar Chart)
    if (guruPerMapelChart.value && props.chartData.guruPerMapel.length > 0) {
      chartInstances['guruPerMapel'] = new Chart(guruPerMapelChart.value, {
        type: 'bar',
        data: {
          labels: props.chartData.guruPerMapel.map(item => item.mata_pelajaran),
          datasets: [{
            label: 'Jumlah Guru',
            data: props.chartData.guruPerMapel.map(item => item.jumlah_guru),
            backgroundColor: '#8B5CF6',
            borderColor: '#7C3AED',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          scales: {
            x: {
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    }

    // Chart 8: Konten per Bulan (Multi-line Chart)
    if (kontenChart.value && props.chartData.kontenPerBulan.length > 0) {
      chartInstances['kontenPerBulan'] = new Chart(kontenChart.value, {
        type: 'line',
        data: {
          labels: props.chartData.kontenPerBulan.map(item => item.bulan),
          datasets: [
            {
              label: 'Artikel',
              data: props.chartData.kontenPerBulan.map(item => item.artikel),
              borderColor: '#3B82F6',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              borderWidth: 3,
              tension: 0.4
            },
            {
              label: 'Berita',
              data: props.chartData.kontenPerBulan.map(item => item.berita),
              borderColor: '#10B981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              borderWidth: 3,
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }

  } catch (error) {
    console.error('Error initializing charts:', error);
  }
};

// Watch for prop changes to reinitialize charts
watch(
  () => props.chartData,
  () => {
    // Reinitialize charts when data changes
    setTimeout(() => {
      initCharts();
    }, 100);
  },
  { deep: true }
);

onMounted(() => {
  initCharts();
});
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">

      <!-- Header Section -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Dashboard Sekolah
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-300">
            <span v-if="!isFilteredByYear">
              Data dari Semua Tahun Ajaran
            </span>
            <span v-else>
              Tahun Ajaran: {{ tahunAjaranAktif?.tahun }}
            </span>
          </p>
        </div>

        <!-- Filter Tahun Ajaran -->
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2">
            <Filter class="h-4 w-4 text-gray-500" />
            <label class="text-sm text-gray-600 dark:text-gray-300">Filter Tahun:</label>
          </div>
          <select
            :value="selectedTahunAjaran"
            @change="filterByTahunAjaran(($event.target as HTMLSelectElement).value)"
            :disabled="isLoading"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50"
          >
            <option value="all">Semua Tahun Ajaran</option>
            <option v-for="tahun in tahunAjaranList" :key="tahun.id" :value="tahun.id">
              {{ tahun.label }}
            </option>
          </select>
          <button
            @click="resetFilter"
            :disabled="isLoading"
            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white disabled:opacity-50 flex items-center gap-1"
          >
            <RefreshCw class="h-3 w-3" :class="{ 'animate-spin': isLoading }" />
            Reset
          </button>
        </div>
      </div>

      <!-- Stats Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Siswa -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Siswa</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(statistics.total_siswa) }}</p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-blue-500/10 flex items-center justify-center">
              <Users class="h-6 w-6 text-blue-600" />
            </div>
          </div>
          <div class="flex flex-wrap gap-2 text-xs">
            <span class="text-green-600 dark:text-green-400">Aktif: {{ formatNumber(statistics.siswa_aktif) }}</span>
            <span class="text-blue-600 dark:text-blue-400">Lulus: {{ formatNumber(statistics.siswa_lulus) }}</span>
            <span class="text-yellow-600 dark:text-yellow-400">Pindah: {{ formatNumber(statistics.siswa_pindah) }}</span>
            <span class="text-red-600 dark:text-red-400">Nonaktif: {{ formatNumber(statistics.siswa_nonaktif) }}</span>
          </div>
        </div>

        <!-- Total Guru -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Guru</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(statistics.total_guru) }}</p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-green-500/10 flex items-center justify-center">
              <GraduationCap class="h-6 w-6 text-green-600" />
            </div>
          </div>
          <div class="flex flex-wrap gap-2 text-xs">
            <span class="text-green-600 dark:text-green-400">Aktif: {{ formatNumber(statistics.guru_aktif) }}</span>
            <span class="text-red-600 dark:text-red-400">Nonaktif: {{ formatNumber(statistics.guru_nonaktif) }}</span>
          </div>
        </div>

        <!-- Total Kelas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Kelas</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(statistics.total_kelas) }}</p>
              <p class="text-xs text-yellow-500 mt-3">{{ quickStats.rata_rata_siswa_per_kelas }} siswa/kelas</p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-yellow-500/10 flex items-center justify-center">
              <School class="h-6 w-6 text-yellow-600" />
            </div>
          </div>
        </div>

        <!-- Total Alumni -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Alumni</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(statistics.total_alumni) }}</p>
              <p class="text-xs text-purple-600 mt-3">{{ quickStats.persentase_alumni }}% dari total</p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-purple-500/10 flex items-center justify-center">
              <UserCheck class="h-6 w-6 text-purple-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Grid Row 1: Charts that are affected by year filter -->
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">

        <!-- Chart 1: Siswa per Tingkat -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Distribusi Siswa per Tingkat
            <span v-if="isFilteredByYear && tahunAjaranAktif" class="text-sm font-normal text-gray-500 ml-2">
              ({{ tahunAjaranAktif.tahun }})
            </span>
            <span v-else-if="!isFilteredByYear" class="text-sm font-normal text-gray-500 ml-2">
              (Semua Tahun)
            </span>
          </h3>
          <div class="relative h-64">
            <canvas ref="siswaPerTingkatChart" v-if="chartData.siswaPerTingkat.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">
                <span v-if="isFilteredByYear">Belum ada data siswa untuk tahun ajaran ini</span>
                <span v-else>Belum ada data siswa</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Chart 2: Siswa per Jurusan -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Siswa per Jurusan
            <span v-if="isFilteredByYear && tahunAjaranAktif" class="text-sm font-normal text-gray-500 ml-2">
              ({{ tahunAjaranAktif.tahun }})
            </span>
            <span v-else-if="!isFilteredByYear" class="text-sm font-normal text-gray-500 ml-2">
              (Semua Tahun)
            </span>
          </h3>
          <div class="relative h-64">
            <canvas ref="siswaPerJurusanChart" v-if="chartData.siswaPerJurusan.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">
                <span v-if="isFilteredByYear">Belum ada data siswa untuk tahun ajaran ini</span>
                <span v-else>Belum ada data siswa</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Chart 3: Gender Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Distribusi Gender
            <span v-if="isFilteredByYear && tahunAjaranAktif" class="text-sm font-normal text-gray-500 ml-2">
              ({{ tahunAjaranAktif.tahun }})
            </span>
            <span v-else-if="!isFilteredByYear" class="text-sm font-normal text-gray-500 ml-2">
              (Semua Tahun)
            </span>
          </h3>
          <div class="relative h-64">
            <canvas ref="genderChart" v-if="chartData.genderSiswa.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">
                <span v-if="isFilteredByYear">Belum ada data siswa untuk tahun ajaran ini</span>
                <span v-else>Belum ada data siswa</span>
              </p>
            </div>
          </div>
        </div>

      </div>

      <!-- Charts Grid Row 2: Mixed charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">

        <!-- Chart 4: Status Siswa -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Status Siswa
            <span v-if="isFilteredByYear && tahunAjaranAktif" class="text-sm font-normal text-gray-500 ml-2">
              ({{ tahunAjaranAktif.tahun }})
            </span>
            <span v-else-if="!isFilteredByYear" class="text-sm font-normal text-gray-500 ml-2">
              (Semua Tahun)
            </span>
          </h3>
          <div class="relative h-64">
            <canvas ref="statusSiswaChart" v-if="chartData.statusSiswa.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">Belum ada data status siswa</p>
            </div>
          </div>
        </div>

        <!-- Chart 5: Perkembangan Siswa -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Perkembangan Jumlah Siswa
            <span class="text-sm font-normal text-gray-500 ml-2">(5 Tahun Terakhir)</span>
          </h3>
          <div class="relative h-64">
            <canvas ref="perkembanganChart" v-if="chartData.perkembanganSiswa.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">Belum ada data perkembangan siswa</p>
            </div>
          </div>
        </div>

        <!-- Chart 6: Alumni per Tahun -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Alumni per Tahun Lulus
            <span class="text-sm font-normal text-gray-500 ml-2">(5 Tahun Terakhir)</span>
          </h3>
          <div class="relative h-64">
            <canvas ref="alumniChart" v-if="chartData.alumniPerTahun.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">Belum ada data alumni</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Charts Grid Row 3: Full width charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Chart 7: Guru per Mata Pelajaran -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Guru per Mata Pelajaran
            <span v-if="isFilteredByYear && tahunAjaranAktif" class="text-sm font-normal text-gray-500 ml-2">
              ({{ tahunAjaranAktif.tahun }})
            </span>
            <span v-else-if="!isFilteredByYear" class="text-sm font-normal text-gray-500 ml-2">
              (Semua Tahun)
            </span>
          </h3>
          <div class="relative h-64">
            <canvas ref="guruPerMapelChart" v-if="chartData.guruPerMapel.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">
                <span v-if="isFilteredByYear">Belum ada data guru untuk tahun ajaran ini</span>
                <span v-else>Belum ada data guru</span>
              </p>
            </div>
          </div>
        </div>

        <!-- Chart 8: Konten per Bulan -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Publikasi Konten
            <span class="text-sm font-normal text-gray-500 ml-2">(6 Bulan Terakhir)</span>
          </h3>
          <div class="relative h-64">
            <canvas ref="kontenChart" v-if="chartData.kontenPerBulan.length > 0"></canvas>
            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
              <p class="text-sm">Belum ada data konten</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Additional Stats and Activities -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Quick Stats -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistik Lainnya</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-300">Mata Pelajaran</span>
              <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.total_mata_pelajaran }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-300">Tenaga Kependidikan</span>
              <span class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ statistics.total_tenaga_kependidikan }}
                <span class="text-xs text-green-600 ml-1">
                  ({{ statistics.tenaga_kependidikan_aktif || 0 }} aktif)
                </span>
              </span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-300">Organisasi</span>
              <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.total_organisasi }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600 dark:text-gray-300">Rasio Guru:Siswa</span>
              <span class="text-lg font-semibold text-gray-900 dark:text-white">1:{{ quickStats.rasio_guru_siswa }}</span>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Terbaru</h3>
          <div class="space-y-3" v-if="aktivitasTerbaru.length > 0">
            <div
              v-for="(activity, index) in aktivitasTerbaru"
              :key="index"
              class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"
            >
              <div class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <FileText class="h-4 w-4 text-blue-600" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ activity.title }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs px-2 py-1 rounded-full"
                    :class="activity.type === 'artikel' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'">
                    {{ activity.type }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">{{ activity.date }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 dark:text-gray-400 py-4">
            <FileText class="h-8 w-8 mx-auto mb-2 opacity-50" />
            <p class="text-sm">Belum ada aktivitas terbaru</p>
          </div>
        </div>

      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
/* Ensure canvas elements are properly sized */
canvas {
  max-width: 100%;
  height: auto;
}

/* Loading state styles */
.opacity-50 {
  opacity: 0.5;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
