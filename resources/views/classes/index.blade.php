<x-layout title="الفصول">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">الفصول</h1>
            <p class="page-subtitle">كل الصفوف والفصول الدراسية</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl p-3 mb-6 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800/50">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">{{ $errors->first() }}</div>
    @endif

    <div class="card p-5 mb-10" data-row-adder>
        <h2 class="section-title">إضافة فصول</h2>
        <p class="text-sm text-ink-400 mb-3">أضف جميع الفصول التي تريدها صفًا صفًا، ثم احفظها كلها مرة واحدة</p>

        <form method="POST" action="/classes">
            @csrf
            <div data-rows class="space-y-2 mb-3">
                <div data-row class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[180px]">
                        <label class="label">الصف</label>
                        <select name="stage_id[]" class="input">
                            <option value="">اختر الصف</option>
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }} ({{ $stage->level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[140px]">
                        <label class="label">اسم الفصل</label>
                        <input type="text" name="name[]" placeholder="مثلًا: أ" class="input">
                    </div>
                    <div class="min-w-[140px]">
                        <label class="label">ترتيب الشعبة</label>
                        <input type="number" name="order[]" placeholder="0 = أ, 1 = ب..." min="0" class="input">
                    </div>
                    <button type="button" data-remove-row class="icon-btn" title="حذف الصف">✕</button>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" data-add-row class="btn-ghost btn-sm">+ صف جديد</button>
                <button type="submit" class="btn-primary">حفظ الكل</button>
            </div>
        </form>
    </div>

    @php $levels = $stages->groupBy('level'); @endphp

    @foreach ($levels as $level => $levelStages)
        @if ($levelStages->sum(fn($s) => $s->classRooms->count()) > 0)
            <div class="level-section">
                <div class="level-header">
                    <span class="level-badge">{{ mb_substr($level, 0, 1) }}</span>
                    <h2 class="text-xl font-extrabold text-ink-900 dark:text-white">المرحلة {{ $level }}</h2>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($levelStages as $stage)
                        @if ($stage->classRooms->isNotEmpty())
                            <div class="card hover-lift overflow-hidden">
                                <div class="px-4 py-3 bg-ink-900 dark:bg-ink-950 text-gold-300 font-semibold text-sm">
                                    {{ $stage->name }}
                                </div>
                                <div>
                                    @foreach ($stage->classRooms as $class)
                                        <div class="list-row">
                                            <a href="/classes/{{ $class->id }}" class="hover:text-gold-600 dark:hover:text-gold-400">فصل {{ $class->name }}</a>
                                            <form method="POST" action="/classes/{{ $class->id }}" data-confirm="تأكيد حذف فصل {{ $class->name }}؟">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-btn" title="حذف">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</x-layout>
