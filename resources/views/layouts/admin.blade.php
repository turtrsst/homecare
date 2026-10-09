@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source './../**/*.blade.php';

@theme {
    --font-sans: 'Inter', 'Segoe UI', sans-serif;

    --color-soeradji-50: #eef7ff;
    --color-soeradji-100: #dceeff;
    --color-soeradji-200: #bfdfff;
    --color-soeradji-300: #8ec4ff;
    --color-soeradji-400: #5aa1f5;
    --color-soeradji-500: #2f7ae5;
    --color-soeradji-600: #1f5fc2;
    --color-soeradji-700: #1a4f9a;
    --color-soeradji-800: #174380;
    --color-soeradji-900: #15396a;

    --color-medical-50: #ecfdf5;
    --color-medical-100: #d1fae5;
    --color-medical-200: #a7f3d0;
    --color-medical-300: #6ee7b7;
    --color-medical-400: #34d399;
    --color-medical-500: #10b981;
    --color-medical-600: #059669;
    --color-medical-700: #047857;
    --color-medical-800: #065f46;
    --color-medical-900: #064e3b;

    --color-clinic-50: #f7fafc;
    --color-clinic-100: #edf3f8;
    --color-clinic-200: #dfeaf3;
    --color-clinic-300: #c7d8e6;
    --color-clinic-400: #8cadc7;
    --color-clinic-500: #628ca9;
    --color-clinic-600: #446f8d;
    --color-clinic-700: #2f5675;
    --color-clinic-800: #264861;
    --color-clinic-900: #203d53;

    --color-sand-50: #f8f5f2;
    --color-sand-100: #f2ece4;
    --color-sand-200: #e8dcc9;

    --shadow-soft: 0 12px 36px rgba(18, 39, 66, 0.08);
    --shadow-pop: 0 22px 48px rgba(23, 55, 89, 0.14);
}

@layer base {
    html {
        scroll-behavior: smooth;
        -webkit-text-size-adjust: 100%;
    }

    body {
        @apply bg-sand-50 text-clinic-900 antialiased;
        font-feature-settings: 'cv02', 'cv03', 'cv04';
    }

    h1, h2, h3, h4, h5, h6 {
        @apply tracking-tight text-clinic-900;
        text-wrap: balance;
    }

    :focus-visible {
        @apply outline-2 outline-offset-2 outline-soeradji-600;
    }

    button, [role='button'], input[type='submit'] {
        min-height: 2.75rem;
    }
}

@layer components {
    .container-app {
        @apply mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8;
    }

    .container-narrow {
        @apply mx-auto w-full max-w-3xl px-4 sm:px-6;
    }

    .brand-badge {
        @apply inline-flex items-center gap-2 rounded-full border border-soeradji-200 bg-soeradji-50 px-3 py-1.5 text-xs font-bold text-soeradji-700;
    }

    .stat-card {
        @apply rounded-[24px] border border-stone-200 bg-white p-4 shadow-soft;
    }

    .shadow-panel {
        box-shadow: var(--shadow-pop);
    }

    .service-card-hover {
        transition: all 0.25s ease;
    }

    .service-card-hover:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-soft);
    }
}

@keyframes shimmer {
    100% { transform: translateX(100%); }
}

.skeleton {
    @apply relative overflow-hidden rounded-xl bg-clinic-100;
}

.skeleton::after {
    content: '';
    @apply absolute inset-0;
    transform: translateX(-100%);
    background-image: linear-gradient(90deg, transparent, rgba(255,255,255,0.55), transparent);
    animation: shimmer 1.4s infinite;
}

[x-cloak] {
    display: none !important;
}
