<x-layout title="تسجيل الغياب">
    <div class="flex flex-wrap justify-between items-start gap-2 mb-1">
        <h1 class="page-title">
            <span class="badge-gold ml-1">حصة {{ $schedule->period_number }}</span>
            {{ $assignment->subject->name }} -
            {{ $assignment->classRoom->stage?->name }} فصل {{ $assignment->classRoom->name }}
        </h1>
        <a href="/attendance/{{ $assignment->id }}/stats" class="text-sm text-gold-600 dark:text-gold-400 font-semibold hover:underline">
            عرض إحصائيات الفصل
        </a>
    </div>
    <p class="page-subtitle mb-6">التاريخ: {{ $today }}</p>

    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/attendance/session/{{ $schedule->id }}">
        @csrf

        <div class="table-wrap mb-6">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>اسم الطالب</th>
                        <th>رقم الهوية</th>
                        <th class="text-center">حاضر</th>
                        <th class="text-center">غايب</th>
                        <th class="text-center">غياب بعذر</th>
                        <th>السبب (بحد أقصى 4 كلمات)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        @php $existingStatus = $existing[$student->id] ?? 'present'; @endphp
                        <tr>
                            <td>{{ $student->serial_number }}</td>
                            <td class="font-medium text-ink-900 dark:text-white">{{ $student->name }}</td>
                            <td>{{ $student->national_id }}</td>
                            <td class="text-center">
                                <input type="radio" name="status[{{ $student->id }}]" value="present"
                                       class="w-4 h-4 accent-emerald-600"
                                    {{ $existingStatus === 'present' ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="radio" name="status[{{ $student->id }}]" value="absent"
                                       class="w-4 h-4 accent-red-600"
                                    {{ $existingStatus === 'absent' ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="radio" name="status[{{ $student->id }}]" value="excused"
                                       class="w-4 h-4 accent-gold-500"
                                    {{ $existingStatus === 'excused' ? 'checked' : '' }}>
                            </td>
                            <td>
                                <input type="text" name="reason[{{ $student->id }}]" maxlength="50"
                                       placeholder="مثلًا: ظرف عائلي طارئ"
                                       value="{{ old("reason.$student->id", $existingReasons[$student->id] ?? '') }}"
                                       class="input py-1.5 text-sm">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn-primary">حفظ الغياب</button>
    </form>
</x-layout>
