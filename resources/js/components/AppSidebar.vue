<script setup lang="ts">
import NavMain from '@/components/NavMain.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem
} from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import {
    LayoutGrid,
    GraduationCap,
    UserCheck,
    UserCog,
    School,
    Calendar,
    CalendarDays,
    Building,
    Trophy,
    Users2,
    Newspaper,
    FileText,
    MessageSquare,
    BookOpen,
} from 'lucide-vue-next'
import NavAdmin from '@/components/NavAdmin.vue'
import { route } from 'ziggy-js'

// ── 1. Dashboard ──────────────────────────────────────────────────
const dashboardItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('admin.dashboard'),
        icon: LayoutGrid,
    },
]

// ── 2. Akademik ───────────────────────────────────────────────────
// Tahun ajaran, kalender, mata pelajaran, dan kelas — fondasi sistem akademik
const akademikItems: NavItem[] = [
    {
        title: 'Tahun Ajaran',
        href: route('admin.tahun-ajaran.index'),
        icon: Calendar,
    },
    {
        title: 'Kalender Akademik',
        href: route('admin.kalender-akademik.index'),
        icon: CalendarDays,
    },
    {
        title: 'Mata Pelajaran',
        href: route('admin.mata-pelajaran.index'),
        icon: BookOpen,
    },
    {
        title: 'Kelas',
        href: route('admin.kelas.index'),
        icon: School,
    },
]

// ── 3. Warga Sekolah ──────────────────────────────────────────────
// Semua orang yang terlibat di sekolah
const wargaSekolahItems: NavItem[] = [
    {
        title: 'Siswa',
        href: route('admin.siswa.index'),
        icon: GraduationCap,
    },
    {
        title: 'Guru',
        href: route('admin.guru.index'),
        icon: UserCheck,
    },
    {
        title: 'Tenaga Kependidikan',
        href: route('admin.tenaga-kependidikan.index'),
        icon: UserCog,
    },
    {
        title: 'Alumni',
        href: route('admin.alumni.index'),
        icon: Users2,
    },
]

// ── 4. Kegiatan & Prestasi ────────────────────────────────────────
// Aktivitas dan pencapaian sekolah
const kegiatanItems: NavItem[] = [
    {
        title: 'Organisasi',
        href: route('admin.organisasi.index'),
        icon: Building,
    },
    {
        title: 'Prestasi',
        href: route('admin.prestasi.index'),
        icon: Trophy,
    },
]

// ── 5. Publikasi ──────────────────────────────────────────────────
// Konten yang dipublikasikan ke publik
const publikasiItems: NavItem[] = [
    {
        title: 'Berita',
        href: route('admin.berita.index'),
        icon: Newspaper,
    },
    {
        title: 'Artikel',
        href: route('admin.artikel.index'),
        icon: FileText,
    },
]

// ── 6. Pesan Masuk ────────────────────────────────────────────────
const pesanItems: NavItem[] = [
    {
        title: 'Pesan Masuk',
        href: route('admin.contact-messages.index'),
        icon: MessageSquare,
    },
]
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('admin.dashboard')">
                            <div class="flex aspect-square size-8 items-center justify-center rounded-lg">
                                <img
                                    src="/storage/img/logo/logo.png"
                                    alt="Logo"
                                    class="size-8 object-contain"
                                />
                            </div>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">SMK ASSALAM GEGESIK</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="dashboardItems" />
            <NavMain :items="akademikItems"      title="Akademik" />
            <NavMain :items="wargaSekolahItems"  title="Warga Sekolah" />
            <NavMain :items="kegiatanItems"      title="Kegiatan & Prestasi" />
            <NavMain :items="publikasiItems"     title="Publikasi" />
            <NavMain :items="pesanItems"         title="Saran & Kritik" />
        </SidebarContent>

        <SidebarFooter>
            <NavAdmin />
        </SidebarFooter>
    </Sidebar>
</template>
