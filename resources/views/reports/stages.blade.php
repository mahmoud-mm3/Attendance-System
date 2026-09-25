<x-layout title="تقارير الصفوف">
    <h1 class="page-title mb-1">تقارير الصفوف</h1>

    @if (! $term)
        <div class="rounded-xl p-3 mb-6 bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-800/50">
            يجب تفعيل فصل دراسي أولًا من صفحة "الأعوام الدراسية" حتى يظهر التقرير.
        </div>
    @else
        <p class="page-subtitle mb-4">{{ $term->name }} — من {{ $start->format('Y-m-d') }} إلى {{ $end->format('Y-m-d') }}</p>

        <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn-ghost btn-sm inline-block mb-4">⬇ تصدير Excel</a>

        @include('reports._period-filter')

        <div class="table-wrap">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>الصف</th>
                        <th class="text-center">حاضر</th>
                        <th class="text-center">غايب</th>
                        <th class="text-center">غياب بعذر</th>
                        <th class="text-center">نسبة الحضور</th>
                        <th class="text-center">نسبة الغياب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="font-medium text-ink-900 dark:text-white">{{ $row['stage']->name }}</td>
                            <td class="text-center">{{ $row['present'] }}</td>
                            <td class="text-center">{{ $row['absent'] }}</td>
                            <td class="text-center">{{ $row['excused'] }}</td>
                            <td class="text-center text-emerald-600 dark:text-emerald-400 font-semibold">{{ $row['attendance_rate'] }}%</td>
                            <td class="text-center text-red-600 dark:text-red-400 font-semibold">{{ $row['absence_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-4 text-center text-ink-400">لا توجد بيانات في هذا النطاق</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</x-layout>
