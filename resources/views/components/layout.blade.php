<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'نظام الغياب' }} - مدارس أجيال أملج الأهلية</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 dark:bg-ink-900 min-h-screen text-ink-800 dark:text-ink-100">
@auth
<nav class="brand-gradient shadow-lg px-4 sm:px-6 py-3 sticky top-0 z-30 border-b border-gold-500/20">
    <div class="flex items-center justify-between">
        <a href="/dashboard" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="شعار المدرسة"
                 class="w-11 h-11 rounded-full object-cover ring-2 ring-gold-400/60"
                 onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='flex';">
            <span id="logo-fallback" style="display:none" class="w-11 h-11 rounded-full bg-gold-400 text-ink-900 items-center justify-center text-lg font-black">أ</span>
            <span class="leading-tight">
                <span class="block font-extrabold text-gold-300 text-sm sm:text-base">مدارس أجيال أملج الأهلية</span>
                <span class="block text-[11px] text-ink-300">نظام الحضور والغياب</span>
            </span>
        </a>

        @php $__currentTerm = \App\Models\Term::current(); @endphp
        @if ($__currentTerm)
            <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-gold-400/20 text-[11px] text-gold-200">
                {{ $__currentTerm->academicYear->name }} — {{ $__currentTerm->name }}
            </span>
        @endif

        <div class="flex items-center gap-1">
            <button id="theme-toggle" type="button"
                    class="p-2 rounded-lg text-gold-300/80 hover:text-gold-300 hover:bg-white/5 transition-colors"
                    aria-label="تبديل الوضع الليلي">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="4" stroke-linecap="round" stroke-linejoin="round" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.07-7.07l-1.41 1.41M6.34 17.66l-1.41 1.41m12.14 0l-1.41-1.41M6.34 6.34L4.93 4.93" />
                </svg>
            </button>

            <button id="nav-toggle" type="button"
                    class="lg:hidden p-2 rounded-lg text-gold-300/80 hover:text-gold-300 hover:bg-white/5 transition-colors"
                    aria-label="فتح القائمة">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    {{-- نافيجيشن الشاشات الكبيرة: أزرار رئيسية + قوائم منسدلة --}}
    <div class="hidden lg:flex items-center gap-1 mt-3 pt-3 border-t border-white/10 text-sm">
        <a href="/dashboard" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            لوحة التحكم
        </a>

        @if (auth()->user()->is_admin)
        <div class="relative" data-dropdown>
            <button type="button" data-dropdown-trigger class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4 2 2 0 000 4zm0 0v2m0-6V4m6 6v10m6-2a2 2 0 100-4 2 2 0 000 4zm0 0v2m0-6V4" /></svg>
                الإدارة
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div data-dropdown-menu class="dropdown-menu hidden absolute top-full right-0 mt-2 w-56 bg-white dark:bg-ink-800 rounded-xl shadow-xl border border-ink-100 dark:border-ink-700 py-2 z-40">
                <a href="/academic-years" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">الأعوام الدراسية</a>
                <a href="/stages" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">الصفوف الدراسية</a>
                <a href="/classes" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">الفصول</a>
                <a href="/subjects" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">المواد</a>
                <a href="/students" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">الطلاب</a>
                <a href="/teachers" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">المعلمين</a>
                <a href="/supervisors" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">الإداريون</a>
                <a href="/educational-supervisors" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">المشرفون التربويون</a>
                <a href="/assignments" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">التوزيع</a>
            </div>
        </div>

        <div class="relative" data-dropdown>
            <button type="button" data-dropdown-trigger class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                التقارير
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div data-dropdown-menu class="dropdown-menu hidden absolute top-full right-0 mt-2 w-56 bg-white dark:bg-ink-800 rounded-xl shadow-xl border border-ink-100 dark:border-ink-700 py-2 z-40">
                <a href="/reports" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقرير عام</a>
                <a href="/reports/stages" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير الصفوف</a>
                <a href="/reports/classes" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير الفصول</a>
                <a href="/reports/teachers" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير المعلمين</a>
                <a href="/reports/supervisor" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير غياب الإداريين</a>
                @if (auth()->user()->is_admin)
                <div class="my-1 border-t border-ink-100 dark:border-ink-700"></div>
                <a href="/attendance-review" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">مراجعة الغياب</a>
                @endif
            </div>
        </div>
        @endif

        @if (auth()->user()->is_educational_supervisor)
        <div class="relative" data-dropdown>
            <button type="button" data-dropdown-trigger class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                التقارير
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div data-dropdown-menu class="dropdown-menu hidden absolute top-full right-0 mt-2 w-56 bg-white dark:bg-ink-800 rounded-xl shadow-xl border border-ink-100 dark:border-ink-700 py-2 z-40">
                <a href="/reports" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقرير عام</a>
                <a href="/reports/stages" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير الصفوف</a>
                <a href="/reports/classes" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير الفصول</a>
                <a href="/reports/teachers" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير المعلمين</a>
                <a href="/reports/supervisor" class="block px-4 py-2 text-ink-700 dark:text-ink-200 hover:bg-gold-50 dark:hover:bg-ink-700 hover:text-gold-700 dark:hover:text-gold-300 transition-colors">تقارير غياب الإداريين</a>
            </div>
        </div>
        @endif

        @if (auth()->user()->is_supervisor)
        <a href="/supervisor-attendance" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            غياب المدرسة
        </a>
        @endif

        @unless (auth()->user()->is_supervisor || auth()->user()->is_educational_supervisor || auth()->user()->is_admin)
        <a href="/attendance" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            حصصي
        </a>
        @endunless

        <div class="mr-auto flex items-center gap-2">
            <span class="text-ink-400 text-xs">{{ auth()->user()->name }}</span>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-400 hover:bg-red-500/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    خروج
                </button>
            </form>
        </div>
    </div>

    {{-- نافيجيشن الموبايل: قائمة مسطحة بالكامل --}}
    <div id="nav-menu" class="hidden lg:hidden flex-col space-y-1 mt-3 pt-3 border-t border-white/10 text-sm">
        <a href="/dashboard" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">لوحة التحكم</a>

        @if (auth()->user()->is_admin)
        <p class="px-3 pt-2 pb-1 text-[11px] font-bold text-gold-400/70 uppercase">الإدارة</p>
        <a href="/academic-years" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">الأعوام الدراسية</a>
        <a href="/stages" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">الصفوف الدراسية</a>
        <a href="/classes" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">الفصول</a>
        <a href="/subjects" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">المواد</a>
        <a href="/students" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">الطلاب</a>
        <a href="/teachers" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">المعلمين</a>
        <a href="/supervisors" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">الإداريون</a>
        <a href="/educational-supervisors" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">المشرفون التربويون</a>
        <a href="/assignments" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">التوزيع</a>

        <p class="px-3 pt-2 pb-1 text-[11px] font-bold text-gold-400/70 uppercase">التقارير</p>
        <a href="/reports" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقرير عام</a>
        <a href="/reports/stages" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير الصفوف</a>
        <a href="/reports/classes" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير الفصول</a>
        <a href="/reports/teachers" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير المعلمين</a>
        <a href="/reports/supervisor" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير غياب الإداريين</a>
        <a href="/attendance-review" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">مراجعة الغياب</a>
        @endif

        @if (auth()->user()->is_educational_supervisor)
        <p class="px-3 pt-2 pb-1 text-[11px] font-bold text-gold-400/70 uppercase">التقارير</p>
        <a href="/reports" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقرير عام</a>
        <a href="/reports/stages" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير الصفوف</a>
        <a href="/reports/classes" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير الفصول</a>
        <a href="/reports/teachers" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير المعلمين</a>
        <a href="/reports/supervisor" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">تقارير غياب الإداريين</a>
        @endif

        @if (auth()->user()->is_supervisor)
        <a href="/supervisor-attendance" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">غياب المدرسة</a>
        @endif

        @unless (auth()->user()->is_supervisor || auth()->user()->is_educational_supervisor || auth()->user()->is_admin)
        <a href="/attendance" class="block px-3 py-2 rounded-lg text-ink-200 hover:text-gold-300 hover:bg-white/5">حصصي</a>
        @endunless

        <form method="POST" action="/logout" class="pt-2 mt-1 border-t border-white/10">
            @csrf
            <button type="submit" class="block w-full text-right px-3 py-2 rounded-lg text-red-400 hover:bg-red-500/10">خروج</button>
        </form>
    </div>
</nav>
@endauth

<main id="page-content" class="max-w-6xl mx-auto p-4 sm:p-6">
    {{ $slot }}
</main>
</body>
</html>
