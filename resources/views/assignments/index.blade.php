<x-layout title="التوزيع">
    <h1 class="page-title mb-1">توزيع المعلمين على الفصول والمواد</h1>
    <p class="page-subtitle mb-6">حدد لكل معلم فصل ومادة، وحدد مواعيد حصصه في الأسبوع</p>

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

    @if ($currentTerm)
        <p class="text-sm text-ink-400 mb-4">التوزيع الحالي بيخص: <span class="badge-gold">{{ $currentTerm->name }} ({{ $currentTerm->academicYear->name }})</span></p>
    @else
        <div class="rounded-xl p-3 mb-6 bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-800/50">
            يجب تفعيل فصل دراسي أولًا من صفحة "الأعوام الدراسية" حتى تتمكن من إنشاء توزيع.
        </div>
    @endif

    <div class="card p-5 mb-8">
        <h2 class="section-title">توزيع جديد</h2>
        <form method="POST" action="/assignments" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[160px]">
                <label class="label">المعلم</label>
                <select name="teacher_id" class="input">
                    <option value="">اختر المعلم</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="label">الفصل</label>
                <select name="class_id" class="input">
                    <option value="">اختر الفصل</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->stage?->name }} - فصل {{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="label">المادة</label>
                <select name="subject_id" class="input">
                    <option value="">اختر المادة</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">+ إضافة</button>
        </form>
    </div>

    <div class="space-y-4">
        @foreach ($assignments as $assignment)
            <div class="card p-5">
                <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                    <span class="font-semibold text-ink-900 dark:text-white">
                        {{ $assignment->teacher->name }}
                        <span class="text-ink-400 font-normal">-</span>
                        {{ $assignment->subject->name }}
                        <span class="text-ink-400 font-normal">-</span>
                        <span class="badge-gold">{{ $assignment->classRoom->stage?->name }} فصل {{ $assignment->classRoom->name }}</span>
                    </span>
                    <form method="POST" action="/assignments/{{ $assignment->id }}" data-confirm="تأكيد حذف هذا التوزيع؟ ستُحذف معه جميع سجلات الغياب والمواعيد المرتبطة به.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm">حذف التوزيع</button>
                    </form>
                </div>

                <div class="flex flex-wrap gap-2 mb-3">
                    @forelse ($assignment->schedules as $schedule)
                        <span class="badge-gold flex items-center gap-2 py-1">
                            {{ $schedule->day_name }} - حصة {{ $schedule->period_number }}
                            <form method="POST" action="/schedules/{{ $schedule->id }}" data-confirm="تأكيد حذف الميعاد ده؟">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400">×</button>
                            </form>
                        </span>
                    @empty
                        <span class="text-ink-400 text-sm">لا توجد مواعيد محددة بعد لهذا التوزيع</span>
                    @endforelse
                </div>

                <form method="POST" action="/assignments/{{ $assignment->id }}/schedules" data-row-adder>
                    @csrf
                    <div data-rows class="space-y-2 mb-2">
                        <div data-row class="flex gap-2 items-center">
                            <select name="day_of_week[]" class="input py-1.5 text-sm w-auto">
                                @foreach ($days as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="period_number[]" class="input py-1.5 text-sm w-auto">
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i }}">حصة {{ $i }}</option>
                                @endfor
                            </select>
                            <button type="button" data-remove-row class="icon-btn" title="حذف الصف">✕</button>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-add-row class="btn-ghost btn-sm">+ ميعاد تاني</button>
                        <button type="submit" class="btn-primary btn-sm">حفظ كل المواعيد</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</x-layout>
