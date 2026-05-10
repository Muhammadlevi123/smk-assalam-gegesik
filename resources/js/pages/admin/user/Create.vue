<script setup lang="ts">
import AppLayout from '../../../layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '../../../types';

// ── Icons ──────────────────────────────────────────────────────────────────
const UserPlusIcon  = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>`;
const SaveIcon      = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`;
const EyeIcon       = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
const EyeOffIcon    = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>`;
const ArrowLeftIcon = () => `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>`;
const ErrIcon       = () => `<svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>`;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Users', href: '/admin/users' },
    { title: 'Tambah User', href: '/admin/users/create' },
];

const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    role:                  'user',
});

const showPassword             = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
    });
};

const clearForm = () => { form.reset(); };

const getUserInitials = (name: string) => {
    if (!name) return '';
    return name.split(' ').map(w => w.charAt(0).toUpperCase()).slice(0, 2).join('');
};

const getRoleColor = (role: string) => {
    switch (role.toLowerCase()) {
        case 'admin': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
        case 'user':  return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        default:      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
    }
};

const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return { score: 0, text: '', color: '' };
    let score = 0;
    if (p.length >= 8)              score++;
    if (p.match(/[a-z]/))           score++;
    if (p.match(/[A-Z]/))           score++;
    if (p.match(/[0-9]/))           score++;
    if (p.match(/[^a-zA-Z0-9]/))   score++;
    if (score <= 2) return { score, text: 'Lemah',   color: 'bg-red-500' };
    if (score === 3) return { score, text: 'Cukup',  color: 'bg-yellow-500' };
    if (score === 4) return { score, text: 'Bagus',  color: 'bg-blue-500' };
    return { score, text: 'Kuat', color: 'bg-green-500' };
});

const roles = [
    { value: 'user',  label: 'User',  description: 'Pengguna standar dengan akses terbatas' },
    { value: 'admin', label: 'Admin', description: 'Administrator dengan akses penuh ke sistem' },
];
</script>

<template>
    <Head title="Tambah User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-50/50 dark:bg-gray-950/50 min-h-screen">
            <div class="mx-auto max-w-4xl space-y-6 px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">

                <!-- ══ Header ══ -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Tambah User Baru</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 sm:text-base">Buat akun pengguna baru dengan hak akses yang sesuai</p>
                </div>

                <!-- ══ Form Card ══ -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:rounded-2xl">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50 sm:px-6 sm:py-4">
                        <div class="flex items-center gap-2">
                            <span v-html="UserPlusIcon()" class="text-gray-500 dark:text-gray-400"></span>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Formulir Informasi User</h3>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-4 sm:p-6 space-y-6">

                        <!-- Nama -->
                        <div class="space-y-1.5">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input id="name" v-model="form.name" type="text" placeholder="Masukkan nama lengkap"
                                :class="form.errors.name ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                            <p class="text-xs text-gray-500 dark:text-gray-400">Masukkan nama lengkap pengguna</p>
                            <p v-if="form.errors.name" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Alamat Email <span class="text-red-500">*</span>
                            </label>
                            <input id="email" v-model="form.email" type="email" placeholder="contoh@email.com"
                                :class="form.errors.email ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                class="block w-full rounded-xl border-0 bg-gray-50 py-3 px-4 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                            <p class="text-xs text-gray-500 dark:text-gray-400">Digunakan untuk login dan notifikasi</p>
                            <p v-if="form.errors.email" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password & Konfirmasi -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Password -->
                            <div class="space-y-1.5">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input id="password" v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Masukkan password"
                                        :class="form.errors.password ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-11 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span v-html="showPassword ? EyeOffIcon() : EyeIcon()"></span>
                                    </button>
                                </div>
                                <!-- Password Strength -->
                                <div v-if="form.password" class="mt-1.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full dark:bg-gray-700">
                                            <div :class="['h-1.5 rounded-full transition-all duration-300', passwordStrength.color]"
                                                :style="{ width: `${(passwordStrength.score / 5) * 100}%` }"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ passwordStrength.text }}</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Minimal 8 karakter</p>
                                <p v-if="form.errors.password" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="space-y-1.5">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Konfirmasi Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input id="password_confirmation" v-model="form.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        placeholder="Ulangi password"
                                        :class="form.errors.password_confirmation ? 'ring-red-400 focus:ring-red-500' : 'ring-gray-200 focus:ring-blue-600'"
                                        class="block w-full rounded-xl border-0 bg-gray-50 py-3 pl-4 pr-11 text-sm text-gray-900 ring-1 ring-inset placeholder:text-gray-400 focus:bg-white focus:ring-2 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:bg-gray-700" />
                                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span v-html="showPasswordConfirmation ? EyeOffIcon() : EyeIcon()"></span>
                                    </button>
                                </div>
                                <!-- Password Match -->
                                <div v-if="form.password_confirmation && form.password" class="mt-1.5">
                                    <div v-if="form.password === form.password_confirmation" class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span class="text-xs">Password cocok</span>
                                    </div>
                                    <div v-else class="flex items-center gap-1 text-red-600 dark:text-red-400">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                        <span class="text-xs">Password tidak cocok</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Ulangi password untuk konfirmasi</p>
                                <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <span v-html="ErrIcon()"></span>{{ form.errors.password_confirmation }}
                                </p>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Role User <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div v-for="roleOption in roles" :key="roleOption.value" class="relative">
                                    <input :id="`role-${roleOption.value}`" v-model="form.role"
                                        :value="roleOption.value" type="radio" name="role" class="peer sr-only" />
                                    <label :for="`role-${roleOption.value}`"
                                        class="flex cursor-pointer rounded-xl border border-gray-300 bg-white p-4 text-sm font-medium shadow-sm hover:bg-gray-50 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-600 dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:peer-checked:border-blue-500 dark:peer-checked:ring-blue-500 transition-all">
                                        <div class="flex flex-1 items-center gap-3">
                                            <div class="h-4 w-4 rounded-full border-2 border-gray-300 peer-checked:border-blue-600 dark:border-gray-600 flex items-center justify-center flex-shrink-0"
                                                :class="form.role === roleOption.value ? 'border-blue-600 bg-blue-600 dark:border-blue-500 dark:bg-blue-500' : ''">
                                                <div v-if="form.role === roleOption.value" class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ roleOption.label }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ roleOption.description }}</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <p v-if="form.errors.role" class="text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                                <span v-html="ErrIcon()"></span>{{ form.errors.role }}
                            </p>
                        </div>

                        <!-- Preview -->
                        <div v-if="form.name || form.email"
                            class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 flex-shrink-0 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white dark:ring-gray-900">
                                    {{ getUserInitials(form.name) || '?' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-1.5">Pratinjau User</p>
                                    <div class="space-y-0.5">
                                        <p class="text-sm text-blue-800 dark:text-blue-200"><span class="font-medium">Nama:</span> {{ form.name || '-' }}</p>
                                        <p class="text-sm text-blue-800 dark:text-blue-200"><span class="font-medium">Email:</span> {{ form.email || '-' }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Role:</span>
                                            <span :class="getRoleColor(form.role)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                                {{ form.role.charAt(0).toUpperCase() + form.role.slice(1) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                            <button type="submit" :disabled="form.processing"
                                class="group inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-900">
                                <span v-if="!form.processing" v-html="SaveIcon()" class="transition-transform group-hover:scale-110"></span>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan User' }}
                            </button>

                            <button type="button" @click="clearForm"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                Bersihkan Form
                            </button>

                            <Link :href="route('admin.users.index')"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">
                                <span v-html="ArrowLeftIcon()"></span>
                                Kembali
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
