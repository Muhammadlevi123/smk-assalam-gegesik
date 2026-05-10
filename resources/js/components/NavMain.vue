<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
    title?: string;
}>();

const page = usePage();
const currentUrl = computed(() => page.url);

const { state } = useSidebar();

const isActive = (href: string | undefined): boolean => {
    if (!href) return false;

    let comparePath = href;

    // Kalau URL absolut (http/https), ambil pathname-nya saja
    if (href.startsWith('http://') || href.startsWith('https://')) {
        try {
            if (typeof window !== 'undefined' && window.URL) {
                const urlObj = new window.URL(href);
                comparePath = urlObj.pathname;
            } else {
                const match = href.match(/^https?:\/\/[^\/]+(.*)$/);
                comparePath = match ? match[1] : href;
            }
        } catch (e) {
            const match = href.match(/^https?:\/\/[^\/]+(.*)$/);
            comparePath = match ? match[1] : href;
        }
    }

    // Ambil pathname saja (tanpa query string)
    const currentPath = currentUrl.value.split('?')[0];

    // Exact match untuk dashboard
    if (comparePath.endsWith('/dashboard')) {
        return currentPath === comparePath;
    }

    // startsWith untuk halaman lain agar sub-route tetap highlight
    return currentPath === comparePath || currentPath.startsWith(comparePath + '/');
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <!-- Label grup hanya muncul saat sidebar expanded -->
        <SidebarGroupLabel
            v-if="title && state === 'expanded'"
            class="px-2 py-2 text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ title }}
        </SidebarGroupLabel>

        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :tooltip="item.title">
                    <Link
                        :href="item.href ?? '#'"
                        :class="[
                            'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors w-full',
                            isActive(item.href)
                                ? 'bg-green-50 text-green-800 font-semibold shadow-sm border-l-4 border-green-600 dark:bg-green-900/20 dark:text-green-300 dark:border-green-500'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 border-l-4 border-transparent dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200'
                        ]">
                        <component
                            :is="item.icon"
                            :class="[
                                'h-4 w-4 shrink-0',
                                isActive(item.href) ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'
                            ]" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
