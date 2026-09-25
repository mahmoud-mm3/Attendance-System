<x-layout title="مراجعة الغياب">
    <h1 class="page-title mb-1">مراجعة واعتماد الغياب</h1>
    <p class="page-subtitle mb-6">مجمّعة حسب الفصل، وداخل كل فصل تظهر كل حصة على حدة. اعتمد الحصة كاملة بزر واحد إذا لم يكن هناك تعديل مطلوب</p>

    @if (session('success'))
        <div class="rounded-xl p-3 mb-6 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800/50">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="/attendance-review" class="flex flex-wrap gap-3 mb-6 items-end">
        <div>
            <label class="label">اليوم</label>
            <input type="date" name="date" value="{{ $date }}" class="input">
        </div>
        <div class="min-w-[220px]">
            <label class="label">الفصل (اختياري)</label>
            <select name="class_id" class="input">
                <option value="">كل الفصول</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>
                        {{ $class->stage?->name }} - فصل {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">عرض</button>
    </form>

    <h2 class="section-title">غياب المعلمين</h2>
    @if ($classGroups->isEmpty())
        <p class="text-ink-400 mb-6">لا يوجد غياب مسجَّل في هذا اليوم حتى الآن.</p>
    @endif

    <div class="space-y-4 mb-10">
        @foreach ($classGroups as $classGroup)
            <details class="card overflow-hidden" {{ $classGroup['pendingCount'] > 0 ? 'open' : '' }}>
                <summary class="p-4 flex flex-wrap justify-between items-center gap-3 bg-ink-50 dark:bg-ink-800/60 cursor-pointer select-none">
                    <span class="font-bold text-ink-900 dark:text-white">
                        {{ $classGroup['classRoom']->stage?->name }} — فصل {{ $classGroup['classRoom']->name }}
                    </span>
                    @if ($classGroup['pendingCount'] > 0)
                        <span class="badge-gold">{{ $classGroup['pendingCount'] }} غياب معلق في {{ $classGroup['sessions']->count() }} حصة</span>
                    @else
                        <span class="badge-success">✓ كل حصص الفصل معتمدة</span>
                    @endif
                </summary>

                <div class="p-4 space-y-4 border-t border-ink-100 dark:border-ink-700">
                    @foreach ($classGroup['sessions'] as $session)
                        <div class="rounded-xl border border-ink-100 dark:border-ink-700 overflow-hidden">
                            <div class="p-3 flex flex-wrap justify-between items-center gap-3 bg-ink-50/60 dark:bg-ink-800/40">
                                <div>
                                    @if ($session['period_number'])
                                        <span class="badge-gold ml-1">حصة {{ $session['period_number'] }}</span>
                                    @endif
                                    <span class="font-semibold text-ink-900 dark:text-white">{{ $session['teacher']->name ?? 'معلم محذوف' }}</span>
                                    <span class="text-ink-400"> - {{ $session['subject']->name ?? '' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($session['pendingCount'] > 0)
                                        <span class="badge-gold">{{ $session['pendingCount'] }} لا يزال معلقًا</span>
                                        <form method="POST" action="/attendance-review/approve-group">
                                            @csrf
                                            @if ($session['schedule_id'])
                                                <input type="hidden" name="schedule_id" value="{{ $session['schedule_id'] }}">
                                            @else
                                                <input type="hidden" name="assignment_id" value="{{ $session['assignment_id'] }}">
                                            @endif
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <button type="submit" class="btn-primary btn-sm">✓ اعتماد الكل ({{ $session['pendingCount'] }})</button>
                                        </form>
                                    @else
                                        <span class="badge-success">✓ معتمدة</span>
                                    @endif
                                </div>
                            </div>

                            <div class="divide-y divide-ink-100 dark:divide-ink-700">
                                @foreach ($session['rows'] as $attendance)
                                    <form method="POST" action="/attendance-review/{{ $attendance->id }}" class="p-3 flex flex-wrap gap-3 items-center">
                                        @csrf
                                        @method('PATCH')

                                        <div class="w-40 font-medium text-ink-900 dark:text-white">{{ $attendance->student->name }}</div>

                                        <select name="status" class="input py-1.5 text-sm w-auto">
                                            <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>حاضر</option>
                                            <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>غايب</option>
                                            <option value="excused" {{ $attendance->status === 'excused' ? 'selected' : '' }}>غياب بعذر</option>
                                        </select>

                                        <input type="text" name="reason" placeholder="السبب (حد أقصى 50 حرفًا)" maxlength="50"
                                               value="{{ $attendance->excuse_reason }}"
                                               class="input py-1.5 text-sm flex-1 min-w-[10rem]">

                                        <label class="text-sm flex items-center gap-1.5 text-ink-600 dark:text-ink-300">
                                            <input type="checkbox" name="excused_counts_as_absence" value="1" class="accent-gold-500"
                                                {{ $attendance->excused_counts_as_absence ? 'checked' : '' }}>
                                            يتحسب غياب
                                        </label>

                                        <button type="submit" class="btn-primary btn-sm">
                                            {{ $attendance->reviewed_at ? 'تحديث الاعتماد' : 'اعتماد' }}
                                        </button>

                                        @if ($attendance->reviewed_at)
                                            <span class="badge-success">✓ معتمد</span>
                                        @else
                                            <span class="badge-gold">بانتظار المراجعة</span>
                                        @endif
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>

    <h2 class="section-title">مراجعة غياب الإداري</h2>

    @if ($supervisorGroups->isEmpty())
        <p class="text-ink-400">لا يوجد غياب مسجَّل من الإداري في هذا اليوم.</p>
    @endif

    <div class="space-y-5">
        @foreach ($supervisorGroups as $group)
            <div class="card overflow-hidden">
                <div class="p-4 flex flex-wrap justify-between items-center gap-3 bg-ink-50 dark:bg-ink-800/60 border-b border-ink-100 dark:border-ink-700">
                    <div>
                        <span class="badge-gold">{{ $group['classRoom']->stage?->name }} فصل {{ $group['classRoom']->name }}</span>
                        <span class="text-ink-400 text-sm"> - الإداري: {{ $group['supervisor']->name ?? '' }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($group['pendingCount'] > 0)
                            <span class="badge-gold">{{ $group['pendingCount'] }} لا يزال معلقًا</span>
                            <form method="POST" action="/attendance-review/approve-supervisor-group">
                                @csrf
                                <input type="hidden" name="class_id" value="{{ $group['class_id'] }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" class="btn-primary btn-sm">✓ اعتماد الكل ({{ $group['pendingCount'] }})</button>
                            </form>
                        @else
                            <span class="badge-success">✓ الفصل كله معتمد</span>
                        @endif
                    </div>
                </div>

                <div class="divide-y divide-ink-100 dark:divide-ink-700">
                    @foreach ($group['rows'] as $attendance)
                        <form method="POST" action="/attendance-review/supervisor/{{ $attendance->id }}" class="p-3 flex flex-wrap gap-3 items-center">
                            @csrf
                            @method('PATCH')

                            <div class="w-40 font-medium text-ink-900 dark:text-white">{{ $attendance->student->name }}</div>

                            <select name="status" class="input py-1.5 text-sm w-auto">
                                <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>حاضر</option>
                                <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>غايب</option>
                                <option value="excused" {{ $attendance->status === 'excused' ? 'selected' : '' }}>غياب بعذر</option>
                            </select>

                            <input type="text" name="reason" placeholder="السبب (حد أقصى 50 حرفًا)" maxlength="50"
                                   value="{{ $attendance->excuse_reason }}"
                                   class="input py-1.5 text-sm flex-1 min-w-[10rem]">

                            <label class="text-sm flex items-center gap-1.5 text-ink-600 dark:text-ink-300">
                                <input type="checkbox" name="excused_counts_as_absence" value="1" class="accent-gold-500"
                                    {{ $attendance->excused_counts_as_absence ? 'checked' : '' }}>
                                يتحسب غياب
                            </label>

                            <button type="submit" class="btn-primary btn-sm">
                                {{ $attendance->reviewed_at ? 'تحديث الاعتماد' : 'اعتماد' }}
                            </button>

                            @if ($attendance->reviewed_at)
                                <span class="badge-success">✓ معتمد</span>
                            @else
                                <span class="badge-gold">بانتظار المراجعة</span>
                            @endif
                        </form>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layout>
