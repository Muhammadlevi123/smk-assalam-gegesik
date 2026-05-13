<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    user: Object,
    loginUrl: String,
    registerUrl: String,
    logoutUrl: String,
});

const isMobileMenuOpen = ref(false);
const isProfileDropdownOpen = ref(false);
const isSearchOpen = ref(false);
const isScrolled = ref(false);
const isScrollingDown = ref(false);
const lastScrollY = ref(0);
const searchQuery = ref('');

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) isSearchOpen.value = false;
};

const toggleProfileDropdown = () => {
    isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
};

const toggleSearch = (event: Event) => {
    event.stopPropagation();
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        isMobileMenuOpen.value = false;
        setTimeout(() => {
            const searchInput = document.querySelector('#search-input') as HTMLInputElement;
            if (searchInput) searchInput.focus();
        }, 300);
    }
};

const preventSearchClose = (event: Event) => { event.stopPropagation(); };

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.get('/search', { q: searchQuery.value });
        searchQuery.value = '';
        isSearchOpen.value = false;
    }
};

const handleLogin = () => {
    setTimeout(() => {
        if (props.user) {
            const role = (props.user as any)?.role;
            if (role === 'admin') {
                router.get('/admin/dashboard');
            } else {
                router.get('/dashboard');
            }
        } else {
            router.get(props.loginUrl || '/login');
        }
    }, 0);
};

const handleLogout = () => {
    router.post(props.logoutUrl || '/logout');
};

const handleKontak = () => {
    const page = usePage();
    const currentUrl = page.url;
    if (currentUrl === '/' || currentUrl === '') {
        const el = document.getElementById('contact-section');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        closeAllDropdowns();
    } else {
        router.get('/#contact-section');
    }
};

const closeDropdowns = (event: Event) => {
    const target = event.target as Element;
    const searchContainer = document.querySelector('.search-container');
    const searchButton = document.querySelector('.search-button');
    if (searchContainer && searchButton) {
        if (!searchContainer.contains(target) && !searchButton.contains(target)) {
            isSearchOpen.value = false;
        }
    }
    const navbar = document.querySelector('.navbar-container');
    if (navbar && !navbar.contains(target)) {
        isMobileMenuOpen.value = false;
        isProfileDropdownOpen.value = false;
    }
};

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    isScrollingDown.value = currentScrollY > lastScrollY.value && currentScrollY > 100;
    isScrolled.value = currentScrollY > 50;
    lastScrollY.value = currentScrollY;
};

const closeAllDropdowns = () => {
    isMobileMenuOpen.value = false;
    isProfileDropdownOpen.value = false;
    isSearchOpen.value = false;
    navigationItems.forEach((item: any) => { if (item.dropdown) item.isOpen = false; });
};

const navigationItems = [
    { name: 'HOME', href: '/', current: false },
    {
        name: 'PROFIL SEKOLAH',
        href: '/profil',
        current: false,
        isOpen: false,
        dropdown: [
            { name: 'Sejarah', href: '/profil/sejarah' },
            { name: 'Visi Misi', href: '/profil/visi-misi' },
            { name: 'Tenaga Pendidik dan Kependidikan', href: '/profil/tenaga-pendidik' },
        ],
    },
    {
        name: 'INFORMASI',
        href: '/informasi',
        current: false,
        isOpen: false,
        dropdown: [
            { name: 'Kalender Akademik', href: '/informasi/kalender-akademik' },
            { name: 'Berita', href: '/informasi/berita' },
            { name: 'Artikel', href: '/informasi/artikel' },
        ],
    },
    {
        name: 'APLIKASI DIGITAL',
        href: '/aplikasi',
        current: false,
        isOpen: false,
        dropdown: [
            { name: 'Coming Soon', href: '#' },
        ],
    },
    { name: 'PRESTASI', href: '/prestasi', current: false },
    { name: 'KONTAK', href: '#contact-section', isKontak: true, current: false },
];

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') closeAllDropdowns();
};

onMounted(() => {
    document.addEventListener('click', closeDropdowns);
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div :class="['navbar-container transition-all duration-500 ease-in-out', isScrollingDown ? '-translate-y-full' : 'translate-y-0']">

        <!-- Top header bar -->
        <div v-show="!isScrolled" class="bg-gray-800 px-4 py-2 text-white transition-all duration-300 ease-in-out lg:px-6">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between">
                <div class="flex items-center space-x-6 text-sm">
                    <div class="hidden lg:flex lg:items-center lg:space-x-6">
                        <div class="flex items-center space-x-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21L8.497 10.9a11.025 11.025 0 002.606 2.606l1.513-1.727a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z"/>
                            </svg>
                            <span>0231 8830069</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>info@smkassalamgegesik.sch.id</span>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1 text-xs lg:hidden">
                        <div class="flex items-center space-x-2">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21L8.497 10.9a11.025 11.025 0 002.606 2.606l1.513-1.727a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z"/>
                            </svg>
                            <span>0231 8830069</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>info@smkassalamgegesik.sch.id</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <template v-if="user">
                        <button @click="handleLogin" class="flex cursor-pointer items-center space-x-2 text-sm text-white transition-colors duration-200 hover:text-green-300" :title="`Masuk ke dashboard`">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-600 ring-2 ring-green-400/40">
                                <span class="text-xs font-semibold text-white">{{ (user as any).name?.charAt(0).toUpperCase() || 'U' }}</span>
                            </div>
                            <span class="hidden lg:inline">{{ (user as any).name || 'User' }}</span>
                            <span class="hidden lg:inline-block rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                :class="(user as any)?.role === 'admin' ? 'bg-amber-500/20 text-amber-300' : 'bg-green-500/20 text-green-300'">
                                {{ (user as any)?.role || 'user' }}
                            </span>
                        </button>
                    </template>
                    <template v-else>
                        <button @click="handleLogin" class="flex cursor-pointer items-center space-x-1 text-sm text-white transition-colors duration-200 hover:text-gray-300" title="Login">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Main navbar -->
        <div :class="[
            'relative z-20 px-4 py-5 transition-all duration-500 ease-in-out lg:px-6',
            isScrolled ? 'border-b border-gray-200 bg-white shadow-lg' : 'bg-transparent',
            isMobileMenuOpen ? 'mobile-menu-open' : ''
        ]">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between">

                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button
                        :class="[
                            'transition-colors duration-300',
                            isMobileMenuOpen ? 'text-gray-700 hover:text-green-600'
                                : isScrolled ? 'text-gray-700 hover:text-green-600'
                                : 'text-white hover:text-green-200'
                        ]"
                        @click="toggleMobileMenu"
                    >
                        <svg v-if="!isMobileMenuOpen" class="pointer-events-none h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg v-else class="pointer-events-none h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Logo -->
                <div class="absolute left-1/2 -translate-x-1/2 transform lg:static lg:translate-x-0 lg:transform-none">
                    <Link href="/" class="flex items-center space-x-3">
                        <div class="flex items-center justify-center overflow-hidden transition-all duration-500 h-16 w-50 lg:h-16 lg:w-80">
                            <img
                                :src="(isScrolled || isMobileMenuOpen) ? '/storage/img/logo/logo-black.png' : '/storage/img/logo/logo-white.png'"
                                alt="Logo Sekolah"
                                class="object-contain transition-all duration-500 h-16 w-50 lg:h-16 lg:w-80"
                            />
                        </div>
                    </Link>
                </div>

                <!-- Desktop nav -->
                <div class="hidden cursor-pointer items-center space-x-1 lg:flex">
                    <template v-for="item in navigationItems" :key="item.name">

                        <div v-if="!item.dropdown">
                            <button
                                v-if="item.isKontak"
                                @click="handleKontak"
                                :class="['px-4 py-3 text-sm font-medium transition-colors duration-300 cursor-pointer bg-transparent border-none', isScrolled ? 'text-gray-700 hover:text-green-600' : 'text-white drop-shadow-sm hover:text-green-200']"
                            >
                                {{ item.name }}
                            </button>
                            <Link v-else :href="item.href"
                                :class="['px-4 py-3 text-sm font-medium transition-colors duration-300', isScrolled ? 'text-gray-700 hover:text-green-600' : 'text-white drop-shadow-sm hover:text-green-200']">
                                {{ item.name }}
                            </Link>
                        </div>

                        <div v-else class="nav-dropdown">
                            <div
                                :class="['nav-dropdown-trigger flex items-center px-4 py-3 text-sm font-medium transition-colors duration-300 cursor-pointer select-none', isScrolled ? 'text-gray-700 hover:text-green-600' : 'text-white drop-shadow-sm hover:text-green-200']"
                            >
                                {{ item.name }}
                                <svg class="ml-1 h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="nav-dropdown-panel">
                                <div class="nav-dropdown-bridge"></div>
                                <ul class="nav-dropdown-list">
                                    <li v-for="(subItem, index) in item.dropdown" :key="subItem.name"
                                        :class="[index < item.dropdown.length - 1 ? 'border-b border-gray-100' : '']">
                                        <Link :href="subItem.href"
                                            class="block px-5 py-3.5 text-sm text-gray-700 transition-all duration-200 hover:bg-gray-50 hover:text-green-600 hover:pl-6">
                                            {{ subItem.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </template>
                </div>

                <!-- Search + User -->
                <div class="flex items-center space-x-2">
                    <button @click="toggleSearch" class="search-button"
                        :class="[
                            'flex h-8 w-8 items-center justify-center rounded-full transition-all duration-300',
                            isMobileMenuOpen ? 'text-gray-700 hover:bg-gray-100 hover:text-green-600'
                                : isScrolled ? 'text-gray-700 hover:bg-gray-100 hover:text-green-600'
                                : 'text-white hover:bg-white/10 hover:text-green-200'
                        ]"
                    >
                        <svg class="pointer-events-none h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <template v-if="user">
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn avatar btn-circle btn-ghost btn-sm" @click="toggleProfileDropdown">
                                <div class="w-8 rounded-full">
                                    <div :class="['flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-300', isScrolled ? 'bg-green-600' : 'border-2 border-white/30 bg-white/20 backdrop-blur-sm']">
                                        <span class="text-xs font-semibold text-white">{{ (user as any).name?.charAt(0).toUpperCase() || 'U' }}</span>
                                    </div>
                                </div>
                            </div>
                            <ul v-if="isProfileDropdownOpen" tabindex="0" class="dropdown-content menu menu-sm z-50 mt-3 w-52 overflow-hidden border bg-white p-0 shadow-xl">
                                <li class="border-b border-gray-200">
                                    <div class="px-4 py-3">
                                        <span class="font-semibold">{{ (user as any).name || 'User' }}</span>
                                        <span class="ml-2 badge badge-sm" :class="(user as any)?.role === 'admin' ? 'badge-warning' : 'badge-success'">{{ (user as any)?.role || 'user' }}</span>
                                    </div>
                                </li>
                                <li class="border-b border-gray-200">
                                    <Link :href="(user as any)?.role === 'admin' ? '/admin/dashboard' : '/dashboard'" class="px-4 py-3 transition-colors duration-200 hover:bg-gray-50">Dashboard</Link>
                                </li>
                                <template v-if="(user as any)?.role !== 'admin'">
                                    <li class="border-b border-gray-200">
                                        <Link href="/profile" class="px-4 py-3 transition-colors duration-200 hover:bg-gray-50">Profil Saya</Link>
                                    </li>
                                </template>
                                <li>
                                    <button @click="handleLogout" class="w-full rounded-none px-4 py-3 text-left transition-colors duration-200 hover:bg-red-50 hover:text-red-600">Logout</button>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="isMobileMenuOpen" class="mobile-menu-body mt-4 border-t border-gray-200 pt-4 lg:hidden">
                <div class="space-y-0">
                    <template v-for="item in navigationItems" :key="item.name">
                        <div v-if="!item.dropdown">
                            <button
                                v-if="item.isKontak"
                                @click="handleKontak"
                                class="block w-full border-b border-gray-100 px-4 py-4 text-left text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-green-50 hover:text-green-700 bg-transparent cursor-pointer"
                            >
                                {{ item.name }}
                            </button>
                            <Link v-else :href="item.href" @click="closeAllDropdowns"
                                class="block w-full border-b border-gray-100 px-4 py-4 text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-green-50 hover:text-green-700">
                                {{ item.name }}
                            </Link>
                        </div>
                        <div v-else class="collapse-arrow collapse border-b border-gray-100">
                            <input type="checkbox" class="peer" />
                            <label class="collapse-title block w-full cursor-pointer px-4 py-4 text-sm font-medium text-gray-700">
                                {{ item.name }}
                            </label>
                            <div class="collapse-content bg-gray-50">
                                <div class="py-1">
                                    <Link v-for="subItem in item.dropdown" :key="subItem.name" :href="subItem.href" @click="closeAllDropdowns"
                                        class="block w-full border-b border-gray-100 px-6 py-3 text-sm text-gray-600 transition-colors duration-200 last:border-0 hover:bg-white hover:text-green-700">
                                        {{ subItem.name }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="user">
                        <div class="border-t border-gray-200 pt-1">
                            <button @click="handleLogout" class="block w-full px-4 py-4 text-left text-sm font-medium text-red-500 transition-colors duration-200 hover:bg-red-50">
                                Logout
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 transform -translate-y-2 scale-95" enter-to-class="opacity-100 transform translate-y-0 scale-100" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 transform translate-y-0 scale-100" leave-to-class="opacity-0 transform -translate-y-2 scale-95">
            <div v-if="isSearchOpen" :class="['search-container absolute top-full z-50 mt-2', 'right-0 lg:right-30']" style="max-width: calc(100vw - 2rem)" @click="preventSearchClose">
                <form @submit.prevent="handleSearch" class="flex items-center justify-end">
                    <div class="relative">
                        <input id="search-input" v-model="searchQuery" type="text" placeholder="Search..." class="w-64 max-w-[calc(100vw-4rem)] rounded-full border border-gray-200 bg-white px-4 py-2 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-lg transition-all duration-200 focus:border-green-400 focus:ring-2 focus:ring-green-400 focus:outline-none" @click="preventSearchClose" />
                        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-4 w-4 text-gray-400 transition-colors hover:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.navbar-container { position: fixed; top: 0; left: 0; right: 0; z-index: 50; }
.search-container { position: relative; z-index: 51; }
.drop-shadow-sm { filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1)); }
.backdrop-blur-sm { backdrop-filter: blur(4px); }
#search-input { transition: all 0.2s ease; }
#search-input:focus { box-shadow: 0 0 0 3px rgba(34,197,94,0.1); }

@media (max-width: 1023px) {
    .mobile-menu-open {
        background-color: white !important;
        border-bottom: 1px solid #e5e7eb !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12) !important;
    }
}
.mobile-menu-body { background-color: white; }

.nav-dropdown { position: relative; }

.nav-dropdown-panel {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 50;
    min-width: 260px;
}

.nav-dropdown:hover .nav-dropdown-panel {
    display: block;
    animation: dropFadeIn 0.18s ease-out;
}

.nav-dropdown-bridge {
    height: 8px;
    background: transparent;
    cursor: default;
}

/* ✅ HANYA INI YANG DIUBAH: border-radius: 0 (lancip/kotak) */
.nav-dropdown-list {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
    list-style: none;
    margin: 0;
    padding: 0;
}

@keyframes dropFadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
