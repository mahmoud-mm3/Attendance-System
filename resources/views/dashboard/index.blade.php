<x-layout title="لوحة التحكم">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="page-title mb-1">مرحبًا {{ $roleLabel }}/{{ auth()->user()->name }} 👋</h1>
            @if ($currentTerm)
                <span class="badge-gold">{{ $currentTerm->name }} — {{ $currentTerm->academicYear->name }}</span>
            @else
                <p class="text-sm rounded-xl inline-block px-3 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-800/50">
                    لا يوجد فصل دراسي مفعّل حاليًا.
                </p>
            @endif
        </div>
        <img src="{{ asset('images/logo.png') }}" alt="" class="w-14 h-14 rounded-full ring-2 ring-gold-400/40 hidden sm:block">
    </div>

    @php
        $icon = fn($path) => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">'.$path.'</svg>';
        $cards = [];
        if (auth()->user()->is_admin) {
            $cards = [
                ['url' => '/academic-years', 'title' => 'الأعوام الدراسية', 'desc' => 'إدارة الأعوام والفصول الدراسية', 'icon' => 'calendar'],
                ['url' => '/stages', 'title' => 'الصفوف الدراسية', 'desc' => 'إدارة الصفوف داخل كل مرحلة', 'icon' => 'building'],
                ['url' => '/classes', 'title' => 'الفصول', 'desc' => 'الصفوف والفصول والشعب', 'icon' => 'building'],
                ['url' => '/students', 'title' => 'الطلاب', 'desc' => 'بيانات الطلاب', 'icon' => 'users'],
                ['url' => '/teachers', 'title' => 'المعلمين', 'desc' => 'حسابات المعلمين', 'icon' => 'user'],
                ['url' => '/supervisors', 'title' => 'الإداريين', 'desc' => 'حسابات الإداريين', 'icon' => 'shield'],
                ['url' => '/assignments', 'title' => 'التوزيع', 'desc' => 'توزيع المعلمين على الفصول والمواد', 'icon' => 'grid'],
                ['url' => '/reports/stages', 'title' => 'تقارير الصفوف', 'desc' => 'نسب الحضور والغياب لكل صف', 'icon' => 'chart'],
                ['url' => '/reports/classes', 'title' => 'تقارير الفصول', 'desc' => 'نسب الحضور والغياب لكل فصل', 'icon' => 'chart'],
                ['url' => '/reports/teachers', 'title' => 'تقارير المعلمين', 'desc' => 'غياب الطلاب في حصص كل معلم', 'icon' => 'chart'],
                ['url' => '/reports/supervisor', 'title' => 'تقارير غياب الإداريين', 'desc' => 'إحصائيات غياب الإداري', 'icon' => 'chart'],
                ['url' => '/attendance-review', 'title' => 'مراجعة الغياب', 'desc' => 'اعتماد أو تعديل الغياب المسجّل', 'icon' => 'check'],
            ];
        }
        if (auth()->user()->is_supervisor) {
            $cards[] = ['url' => '/supervisor-attendance', 'title' => 'غياب المدرسة', 'desc' => 'تسجيل غياب المدرسة كلها لليوم', 'icon' => 'clipboard'];
        }
        if (auth()->user()->is_educational_supervisor) {
            $cards = [
                ['url' => '/reports', 'title' => 'تقرير عام', 'desc' => 'نظرة عامة على الحضور والغياب', 'icon' => 'chart'],
                ['url' => '/reports/stages', 'title' => 'تقارير الصفوف', 'desc' => 'نسب الحضور والغياب لكل صف', 'icon' => 'chart'],
                ['url' => '/reports/classes', 'title' => 'تقارير الفصول', 'desc' => 'نسب الحضور والغياب لكل فصل', 'icon' => 'chart'],
                ['url' => '/reports/teachers', 'title' => 'تقارير المعلمين', 'desc' => 'غياب الطلاب في حصص كل معلم', 'icon' => 'chart'],
                ['url' => '/reports/supervisor', 'title' => 'تقارير غياب الإداريين', 'desc' => 'إحصائيات غياب الإداري', 'icon' => 'chart'],
            ];
        }
        if (! auth()->user()->is_admin && ! auth()->user()->is_supervisor && ! auth()->user()->is_educational_supervisor) {
            $cards[] = ['url' => '/attendance', 'title' => 'حصصي', 'desc' => 'تسجيل غياب حصصك اليوم', 'icon' => 'clipboard'];
        }

        $icons = [
            'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
            'building' => '<path d="M4 21V7l8-4 8 4v14M9 21v-6h6v6M9 12h.01M15 12h.01M9 9h.01M15 9h.01"/>',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-3a4 4 0 100-8 4 4 0 000 8zm6 3a4 4 0 00-3-3.87"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6M15 19V9M4 19V13m0 6h16"/>',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($cards as $card)
            <a href="{{ $card['url'] }}" class="card hover-lift p-5 flex items-start gap-4">
                <span class="w-12 h-12 shrink-0 rounded-xl bg-ink-900 dark:bg-gold-400 text-gold-300 dark:text-ink-900 flex items-center justify-center">
                    {!! $icon($icons[$card['icon']]) !!}
                </span>
                <span>
                    <span class="block font-bold text-ink-900 dark:text-white mb-0.5">{{ $card['title'] }}</span>
                    <span class="block text-sm text-ink-400">{{ $card['desc'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</x-layout>
