<x-layout title="إحصائيات الفصل">
    <h1 class="page-title mb-1">
        {{ $assignment->subject->name }} -
        {{ $assignment->classRoom->stage?->name }} فصل {{ $assignment->classRoom->name }}
    </h1>
    <p class="page-subtitle mb-6">العام الدراسي: {{ $year }}</p>

    <div class="table-wrap">
        <table class="table-base">
            <thead>
                <tr>
                    <th>م</th>
                    <th>اسم الطالب</th>
                    <th class="text-center">حاضر</th>
                    <th class="text-center">غايب</th>
                    <th class="text-center">غياب بعذر</th>
                    <th class="text-center">نسبة الحضور</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    @php
                        $absenceEquivalent = $student->absent_count + $student->excused_counted_count;
                        $percentage = $student->total_count > 0
                            ? round((($student->total_count - $absenceEquivalent) / $student->total_count) * 100)
                            : 0;
                    @endphp
                    <tr>
                        <td>{{ $student->serial_number }}</td>
                        <td class="font-medium text-ink-900 dark:text-white">{{ $student->name }}</td>
                        <td class="text-center">{{ $student->present_count }}</td>
                        <td class="text-center">{{ $student->absent_count }}</td>
                        <td class="text-center">{{ $student->excused_count }}</td>
                        <td class="text-center">
                            <span class="{{ $percentage < 75 ? 'badge-danger' : 'badge-success' }}">
                                {{ $percentage }}%
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
