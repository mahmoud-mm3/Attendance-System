<x-layout title="التقارير">
    <h1 class="page-title mb-1">تقرير الحضور والغياب</h1>
    <p class="page-subtitle mb-6">العام الدراسي: {{ $year }}</p>

    @if (! $term)
        <div class="rounded-xl p-3 mb-6 bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-800/50">
            يجب تفعيل فصل دراسي أولًا من صفحة "الأعوام الدراسية" حتى تتوفر تصفية الفترة الزمنية (يوم/أسبوع/شهر).
        </div>
    @else
        <p class="page-subtitle mb-4">{{ $term->name }} — من {{ $start->format('Y-m-d') }} إلى {{ $end->format('Y-m-d') }}</p>
        @include('reports._period-filter')
    @endif

    <div class="card p-5 mb-8">
        <h2 class="section-title">تصفية</h2>
        <form method="GET" action="/reports" class="flex flex-wrap gap-3 items-end">
            @if ($period)<input type="hidden" name="period" value="{{ $period }}">@endif
            @if ($selectedWeek)<input type="hidden" name="week" value="{{ $selectedWeek }}">@endif
            @if ($selectedMonth)<input type="hidden" name="month" value="{{ $selectedMonth }}">@endif
            @if ($selectedDay)<input type="hidden" name="day" value="{{ $selectedDay }}">@endif

            @php
                $selectedLevel = $filters['level'];
                $selectedStageId = $filters['stage_id'];
                $selectedClassId = $filters['class_id'];
            @endphp
            @include('reports._grade-filter')

            <div class="flex-1 min-w-[160px]">
                <label class="label">المادة</label>
                <select name="subject_id" class="input">
                    <option value="">كل المواد</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (string)$filters['subject_id'] === (string)$subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[160px]">
                <label class="label">المعلم</label>
                <select name="teacher_id" class="input">
                    <option value="">كل المعلمين</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ (string)$filters['teacher_id'] === (string)$teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary">تصفية</button>
            <a href="/reports" class="btn-ghost">إلغاء التصفية</a>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card hover-lift">
            <div class="text-3xl font-extrabold text-ink-900 dark:text-white">{{ $totalPresent }}</div>
            <div class="text-sm text-ink-400">إجمالي أيام الحضور</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="text-3xl font-extrabold text-ink-900 dark:text-white">{{ $totalAbsent }}</div>
            <div class="text-sm text-ink-400">إجمالي أيام الغياب</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $overallAttendanceRate }}%</div>
            <div class="text-sm text-ink-400">نسبة الحضور</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="text-3xl font-extrabold text-red-600 dark:text-red-400">{{ $overallAbsenceRate }}%</div>
            <div class="text-sm text-ink-400">نسبة الغياب</div>
        </div>
    </div>

    <h2 class="section-title">الإحصائيات حسب الفصل</h2>
    <div class="table-wrap mb-8">
        <table class="table-base">
            <thead>
                <tr>
                    <th>الفصل</th>
                    <th class="text-center">الحضور</th>
                    <th class="text-center">الغياب</th>
                    <th class="text-center">نسبة الحضور</th>
                    <th class="text-center">نسبة الغياب</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perClass as $row)
                    <tr>
                        <td>{{ $row['class']->stage?->name }} - فصل {{ $row['class']->name }}</td>
                        <td class="text-center">{{ $row['present'] }}</td>
                        <td class="text-center">{{ $row['absent'] }}</td>
                        <td class="text-center text-emerald-600 dark:text-emerald-400 font-semibold">{{ $row['attendance_rate'] }}%</td>
                        <td class="text-center text-red-600 dark:text-red-400 font-semibold">{{ $row['absence_rate'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 class="section-title">الإحصائيات حسب المادة</h2>
    <div class="table-wrap mb-8">
        <table class="table-base">
            <thead>
                <tr>
                    <th>المادة</th>
                    <th class="text-center">الحضور</th>
                    <th class="text-center">الغياب</th>
                    <th class="text-center">نسبة الحضور</th>
                    <th class="text-center">نسبة الغياب</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perSubject as $row)
                    <tr>
                        <td>{{ $row['subject']->name }}</td>
                        <td class="text-center">{{ $row['present'] }}</td>
                        <td class="text-center">{{ $row['absent'] }}</td>
                        <td class="text-center text-emerald-600 dark:text-emerald-400 font-semibold">{{ $row['attendance_rate'] }}%</td>
                        <td class="text-center text-red-600 dark:text-red-400 font-semibold">{{ $row['absence_rate'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 class="section-title">الإحصائيات حسب المعلمين</h2>
    <div class="table-wrap mb-8">
        <table class="table-base">
            <thead>
                <tr>
                    <th>المعلم</th>
                    <th class="text-center">الحضور</th>
                    <th class="text-center">الغياب</th>
                    <th class="text-center">نسبة الحضور</th>
                    <th class="text-center">نسبة الغياب</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($perTeacher as $row)
                    <tr>
                        <td>{{ $row['teacher']->name }}</td>
                        <td class="text-center">{{ $row['present'] }}</td>
                        <td class="text-center">{{ $row['absent'] }}</td>
                        <td class="text-center text-emerald-600 dark:text-emerald-400 font-semibold">{{ $row['attendance_rate'] }}%</td>
                        <td class="text-center text-red-600 dark:text-red-400 font-semibold">{{ $row['absence_rate'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-ink-400">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="section-title">تفاصيل الطلاب</h2>

    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn-ghost btn-sm inline-block mb-4">⬇ تصدير Excel</a>

    @if ($flaggedCount > 0)
        <div class="rounded-xl p-3 mb-4 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
            ⚠ يوجد {{ $flaggedCount }} طالب غاب في آخر 3 أيام متتالية له بها سجل غياب، وهم موضّحون بعلامة ⚠ أدناه.
        </div>
    @endif

    <div class="table-wrap">
        <table class="table-base">
            <thead>
                <tr>
                    <th>اسم الطالب</th>
                    <th>الفصل</th>
                    <th class="text-center">الحضور</th>
                    <th class="text-center">الغياب</th>
                    <th class="text-center">نسبة الحضور</th>
                    <th>المواد التي تغيب عنها</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $row)
                    <tr class="{{ $row['flagged'] ? 'bg-red-50/60 dark:bg-red-500/5' : '' }}">
                        <td class="font-medium text-ink-900 dark:text-white">
                            {{ $row['student']->name }}
                            @if ($row['flagged'])
                                <span title="غياب متكرر في آخر 3 أيام">⚠</span>
                            @endif
                        </td>
                        <td>{{ $row['student']->classRoom->stage?->name }} - فصل {{ $row['student']->classRoom->name }}</td>
                        <td class="text-center">{{ $row['present'] }}</td>
                        <td class="text-center">{{ $row['absent'] }}</td>
                        <td class="text-center">
                            <span class="{{ $row['attendance_rate'] < 75 ? 'badge-danger' : 'badge-success' }}">
                                {{ $row['attendance_rate'] }}%
                            </span>
                        </td>
                        <td class="text-sm text-ink-500 dark:text-ink-400">
                            {{ $row['missed_subjects']->isNotEmpty() ? $row['missed_subjects']->implode('، ') : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
</x-layout>
