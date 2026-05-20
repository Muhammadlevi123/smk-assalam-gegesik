<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login — SMK Assalam Gegesik" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet" />

    <div class="login-root">

        <!-- ══════════════════════════════════
             KIRI — Branding & Visual
        ═══════════════════════════════════ -->
        <div class="login-left">
            <div class="left-bg"></div>
            <div class="left-overlay"></div>
            <div class="left-pattern">
                <svg width="100%" height="100%" viewBox="0 0 400 600" preserveAspectRatio="xMidYMid slice">
                    <circle cx="350" cy="80"  r="160" fill="rgba(255,255,255,0.03)" />
                    <circle cx="50"  cy="500" r="200" fill="rgba(255,255,255,0.03)" />
                    <circle cx="200" cy="300" r="120" fill="rgba(255,255,255,0.02)" />
                </svg>
            </div>
            <div class="left-content">
                <div class="left-brand">
                    <a href="/" class="left-brand-link" title="Kembali ke Beranda">
                        <img src="/storage/img/logo/logo-white.png" alt="Logo SMK Assalam Gegesik" class="left-logo" />
                    </a>
                </div>
                <div class="left-body">
                    <h1 class="left-title">
                        Selamat Datang<br>
                        <em>Kembali</em>
                    </h1>
                    <p class="left-sub">
                        Portal informasi dan manajemen<br>
                        SMK Assalam Gegesik
                    </p>
                    <div class="left-divider">
                        <span class="divider-line"></span>
                        <span class="divider-dot"></span>
                        <span class="divider-line"></span>
                    </div>
                    <p class="left-quote">
                        "Membentuk Generasi Unggul,<br>
                        Berkarakter, dan Berdaya Saing Global"
                    </p>
                </div>
                <div class="left-footer">
                    <a href="/" class="footer-home-link">← Kembali ke Beranda</a>
                    <span class="dot-sep">·</span>
                    <span>© {{ new Date().getFullYear() }} SMK Assalam Gegesik</span>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════
             KANAN — Form Login
        ═══════════════════════════════════ -->
        <div class="login-right">
            <div class="right-top-accent"></div>
            <div class="form-wrapper">

                <div class="form-header">
                    <div class="mobile-brand">
                        <a href="/" class="mobile-brand-link" title="Kembali ke Beranda">
                            <img src="/storage/img/logo/logo.png" alt="Logo" class="mobile-logo" />
                            <span class="mobile-school-name">SMK Assalam Gegesik</span>
                        </a>
                    </div>
                    <div class="form-title-group">
                        <span class="form-eyebrow">Portal Sekolah</span>
                        <h2 class="form-title">Masuk ke Akun</h2>
                        <p class="form-desc">Gunakan username dan password yang telah didaftarkan</p>
                    </div>
                </div>

                <div v-if="status" class="status-banner">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="login-form">

                    <!-- Email -->
                    <div class="field-group">
                        <label for="email" class="field-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Username
                        </label>
                        <div class="input-wrap" :class="{ 'has-error': form.errors.email }">
                            <input
                                id="email"
                                type="email"
                                name="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="nama@email.com"
                                class="field-input"
                            />
                        </div>
                        <InputError :message="form.errors.email" class="field-error" />
                    </div>

                    <!-- Password -->
                    <div class="field-group">
                        <div class="field-label-row">
                            <label for="password" class="field-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Password
                            </label>
                        </div>
                        <div class="input-wrap" :class="{ 'has-error': form.errors.password }">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="field-input"
                            />
                        </div>
                        <InputError :message="form.errors.password" class="field-error" />
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="btn-login"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="btn-spinner">
                            <span class="spinner-ring"></span>
                        </span>
                        <span v-else class="btn-label">
                            Masuk
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                    </button>

                </form>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* ══════════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.login-root {
    --green-500: #22c55e;
    --green-600: #16a34a;
    --green-700: #15803d;
    --green-800: #166534;
    --green-900: #14532d;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-900: #111827;
    --white: #ffffff;

    --font-display: 'Fraunces', Georgia, serif;
    --font-body: 'Plus Jakarta Sans', sans-serif;

    --radius: 10px;
    --radius-lg: 16px;
    --transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);

    display: flex;
    min-height: 100vh;
    font-family: var(--font-body);
    background: #f8fafb;
}

/* ══════════════════════════════════════════
   KIRI — Branding
══════════════════════════════════════════ */
.login-left {
    position: relative;
    width: 44%;
    min-height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.left-bg {
    position: absolute; inset: 0;
    background: url('/storage/img/landingpage/cover1.png') center/cover no-repeat;
}

.left-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        135deg,
        rgba(15, 60, 30, 0.92) 0%,
        rgba(22, 101, 52, 0.85) 50%,
        rgba(20, 83, 45, 0.90) 100%
    );
}

.left-pattern {
    position: absolute; inset: 0; z-index: 1; pointer-events: none;
}

.left-content {
    position: relative; z-index: 2;
    flex: 1; display: flex; flex-direction: column;
    padding: 44px 48px;
}

.left-brand { margin-bottom: auto; }

.left-brand-link {
    display: inline-block;
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.left-brand-link:hover { opacity: 0.85; transform: translateY(-1px); }

.left-logo {
    height: 64px; width: auto; object-fit: contain;
    object-position: left;
    filter: drop-shadow(0 2px 12px rgba(0,0,0,0.3));
    display: block;
}

.left-body { padding: 48px 0 40px; }

.left-title {
    font-family: var(--font-display);
    font-size: clamp(36px, 4vw, 52px);
    font-weight: 700;
    color: white;
    line-height: 1.1;
    margin-bottom: 16px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.3);
}
.left-title em { color: #86efac; font-style: italic; }

.left-sub {
    font-size: 15px;
    color: rgba(255,255,255,0.65);
    line-height: 1.7;
    margin-bottom: 28px;
}

.left-divider {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 28px;
}
.divider-line { height: 1px; width: 56px; background: rgba(255,255,255,0.25); }
.divider-dot  { width: 5px; height: 5px; background: #86efac; border-radius: 50%; }

.left-quote {
    font-family: var(--font-display);
    font-style: italic;
    font-size: 15px;
    color: rgba(255,255,255,0.55);
    line-height: 1.75;
}

.left-footer {
    display: flex; align-items: center; gap: 10px;
    font-size: 12px; color: rgba(255,255,255,0.35);
    letter-spacing: 0.03em;
}
.dot-sep { color: rgba(255,255,255,0.2); }

.footer-home-link {
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    font-size: 12px; font-weight: 600;
    letter-spacing: 0.03em;
    transition: color 0.2s ease;
}
.footer-home-link:hover { color: rgba(255,255,255,0.85); }

.mobile-brand-link {
    display: flex; align-items: center; gap: 12px;
    text-decoration: none;
    transition: opacity 0.2s ease;
}
.mobile-brand-link:hover { opacity: 0.8; }

/* ══════════════════════════════════════════
   KANAN — Form
══════════════════════════════════════════ */
.login-right {
    flex: 1; display: flex; flex-direction: column;
    position: relative; background: var(--white); overflow-y: auto;
}

.right-top-accent {
    height: 4px;
    background: linear-gradient(90deg, var(--green-600), var(--green-400, #4ade80), var(--green-600));
    flex-shrink: 0;
}

.form-wrapper {
    flex: 1; display: flex; flex-direction: column;
    justify-content: center;
    padding: 52px 56px;
    max-width: 480px;
    margin: 0 auto;
    width: 100%;
}

.mobile-brand {
    display: none;
    align-items: center; gap: 12px;
    margin-bottom: 32px;
    padding-bottom: 28px;
    border-bottom: 1px solid var(--gray-100);
}
.mobile-logo { width: 36px; height: 36px; object-fit: contain; }
.mobile-school-name {
    font-size: 14px; font-weight: 700;
    color: var(--green-800); letter-spacing: 0.02em;
}

.form-header { margin-bottom: 36px; }
.form-eyebrow {
    display: inline-block;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--green-600);
    margin-bottom: 10px;
}
.form-title {
    font-family: var(--font-display);
    font-size: clamp(26px, 3.5vw, 36px);
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1.15;
    margin-bottom: 8px;
}
.form-desc {
    font-size: 14px;
    color: var(--gray-500);
    line-height: 1.6;
}

.status-banner {
    display: flex; align-items: center; gap: 8px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    color: var(--green-700);
    padding: 12px 16px; border-radius: var(--radius);
    font-size: 13px; font-weight: 500;
    margin-bottom: 24px;
}

.login-form { display: flex; flex-direction: column; gap: 22px; }

.field-group { display: flex; flex-direction: column; gap: 7px; }

.field-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600;
    color: var(--gray-700);
}

.field-label-row {
    display: flex; align-items: center; justify-content: space-between;
}

.input-wrap { position: relative; }

.field-input {
    width: 100%; height: 48px;
    padding: 0 16px;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius);
    font-family: var(--font-body);
    font-size: 14px; color: var(--gray-900);
    background: #fafafa;
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
}
.field-input::placeholder { color: var(--gray-400); }
.field-input:focus {
    border-color: var(--green-500);
    background: white;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}
.input-wrap.has-error .field-input {
    border-color: #f87171;
    background: #fff8f8;
}
.input-wrap.has-error .field-input:focus {
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.12);
}

.field-error { font-size: 12px; color: #dc2626; margin-top: 2px; }

/* ══════════════════════════════════════════
   ✅ LOGIN BUTTON — spinner fix
══════════════════════════════════════════ */
.btn-login {
    height: 50px; width: 100%;
    background: var(--green-700);
    color: white; border: none;
    border-radius: var(--radius);
    font-family: var(--font-body);
    font-size: 15px; font-weight: 700;
    cursor: pointer;
    transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
    display: flex; align-items: center; justify-content: center;
    margin-top: 4px;
    letter-spacing: 0.02em;
    /* ✅ Pastikan tombol tidak collapse saat loading */
    min-height: 50px;
}
.btn-login:hover:not(:disabled) {
    background: var(--green-800);
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(22, 101, 52, 0.35);
}
.btn-login:active:not(:disabled) { transform: translateY(0); box-shadow: none; }
.btn-login:disabled { opacity: 0.7; cursor: not-allowed; }

/* ✅ Reset DaisyUI/Tailwind override — pastikan tombol full width tidak collapse */
.btn-login.loading {
    pointer-events: none;
    width: 100% !important;
    height: 50px !important;
}

.btn-label { display: flex; align-items: center; gap: 8px; }

/* ✅ FIX: spinner kecil proporsional, tidak mendominasi tombol */
.btn-spinner {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
}

/* Ring spinner — bukan SVG besar, tapi border CSS yang tipis dan kecil */
.spinner-ring {
    width: 20px;
    height: 20px;
    border: 2.5px solid rgba(255, 255, 255, 0.35);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ══════════════════════════════════════════
   RESPONSIVE — Mobile
══════════════════════════════════════════ */
@media (max-width: 768px) {
    .login-root { flex-direction: column; }
    .login-left { display: none; }
    .login-right {
        flex: 1; min-height: 100vh;
        background: linear-gradient(160deg, #f8fafb 0%, #f0fdf4 100%);
    }
    .right-top-accent {
        height: 6px;
        background: linear-gradient(90deg, var(--green-800), var(--green-500), var(--green-800));
    }
    .form-wrapper { padding: 36px 24px 48px; max-width: 100%; }
    .mobile-brand { display: flex; }
    .form-title { font-size: 28px; }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .login-left { width: 40%; }
    .form-wrapper { padding: 44px 40px; }
    .left-content { padding: 36px 36px; }
    .left-title { font-size: 38px; }
}

/* Page load animation */
.login-left   { animation: slideInLeft  0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }
.form-wrapper { animation: slideInRight 0.7s cubic-bezier(0.22, 1, 0.36, 1) 0.1s both; }

@keyframes slideInLeft  { from { opacity: 0; transform: translateX(-32px); } to { opacity: 1; transform: translateX(0); } }
@keyframes slideInRight { from { opacity: 0; transform: translateX(24px);  } to { opacity: 1; transform: translateX(0); } }
</style>
