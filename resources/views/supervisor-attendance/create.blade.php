<x-layout title="غياب المدرسة">
    <h1 class="page-title mb-1">غياب المدرسة كلها</h1>
    <p class="page-subtitle mb-6">التاريخ: {{ $today }}</p>

    @if (session('success'))
        <div class="rounded-xl p-3 mb-6 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800/50">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
            {{ $errors->first() }}
        </div>
    @endif

    @if (! $term)
        <div class="rounded-xl p-3 bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-800/50">
            لا يوجد فصل دراسي مفعّل حاليًا، يجب على الأدمن تفعيله أولًا.
        </div>
    @elseif ($alreadyTaken)
        <div class="rounded-xl p-3 mb-6 bg-gold-50 text-gold-800 border border-gold-200 dark:bg-gold-500/10 dark:text-gold-300 dark:border-gold-800/50">
            تم تسجيل غياب المدرسة اليوم بالفعل{{ $takenBy ? ' بواسطة '.$takenBy->name : '' }}. لا يمكن تسجيله مرة أخرى اليوم.
        </div>

        <div class="table-wrap">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>الصف</th>
                        <th>الفصل</th>
                        <th class="text-center">حاضر</th>
                        <th class="text-center">غايب</th>
                        <th class="text-center">غياب بعذر</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary as $row)
                        <tr>
                            <td>{{ $row['stage']->name }}</td>
                            <td>{{ $row['class']->name }}</td>
                            <td class="text-center">{{ $row['present'] }}</td>
                            <td class="text-center">{{ $row['absent'] }}</td>
                            <td class="text-center">{{ $row['excused'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <form method="POST" action="/supervisor-attendance">
            @csrf

            @php $levels = $stages->groupBy('level'); @endphp

            @foreach ($levels as $level => $levelStages)
                @if ($levelStages->sum(fn($s) => $s->classRooms->count()) > 0)
                    <div class="level-section">
                        <div class="level-header">
                            <span class="level-badge">{{ mb_substr($level, 0, 1) }}</span>
                            <h2 class="text-lg font-extrabold text-ink-900 dark:text-white">المرحلة {{ $level }}</h2>
                        </div>

                        @foreach ($levelStages as $stage)
                            @if ($stage->classRooms->isNotEmpty())
                                <div class="mb-6">
                                    <h3 class="font-semibold text-sm text-gold-700 dark:text-gold-400 mb-2">{{ $stage->name }}</h3>

                                    @foreach ($stage->classRooms as $classRoom)
                                        <div class="mb-4">
                                            <div class="font-medium text-sm text-ink-400 mb-2">فصل {{ $classRoom->name }}</div>

                                            <div class="table-wrap">
                                                <table class="table-base min-w-[640px]">
                                                    <thead>
                                                        <tr>
                                                            <th>م</th>
                                                            <th>اسم الطالب</th>
                                                            <th class="text-center">حاضر</th>
                                                            <th class="text-center">غايب</th>
                                                            <th class="text-center">غياب بعذر</th>
                                                            <th>السبب (بحد أقصى 4 كلمات)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($classRoom->students as $student)
                                                            <tr>
                                                                <td>{{ $student->serial_number }}</td>
                                                                <td class="font-medium text-ink-900 dark:text-white">{{ $student->name }}</td>
                                                                <td class="text-center">
                                                                    <input type="hidden" name="class_id[{{ $student->id }}]" value="{{ $classRoom->id }}">
                                                                    <input type="radio" name="status[{{ $student->id }}]" value="present"
                                                                           class="w-4 h-4 accent-emerald-600" checked>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input type="radio" name="status[{{ $student->id }}]" value="absent"
                                                                           class="w-4 h-4 accent-red-600">
                                                                </td>
                                                                <td class="text-center">
                                                                    <input type="radio" name="status[{{ $student->id }}]" value="excused"
                                                                           class="w-4 h-4 accent-gold-500">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="reason[{{ $student->id }}]" maxlength="50"
                                                                           placeholder="مثلًا: ظرف عائلي طارئ"
                                                                           value="{{ old('reason.'.$student->id) }}"
                                                                           class="input py-1.5 text-sm">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach

            <button type="submit" class="btn-primary sticky bottom-4 shadow-lg">
                حفظ غياب المدرسة
            </button>
        </form>
    @endif
</x-layout>
