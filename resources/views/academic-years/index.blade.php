<x-layout title="الأعوام الدراسية">
    <h1 class="page-title mb-1">الأعوام والفصول الدراسية</h1>
    <p class="page-subtitle mb-6">إدارة الأعوام الدراسية والفصول (الترمات) وتفعيل الفصل الحالي</p>

    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card p-5 mb-8">
        <h2 class="section-title">إضافة عام دراسي</h2>
        <form method="POST" action="/academic-years" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="label">العام الدراسي</label>
                <input type="text" name="name" placeholder="مثلًا: 2026/2027" class="input">
            </div>
            <button type="submit" class="btn-primary">+ إضافة عام دراسي</button>
        </form>
    </div>

    @foreach ($academicYears as $academicYear)
        <div class="card p-5 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-ink-900 dark:text-white flex items-center gap-2">
                    {{ $academicYear->name }}
                    @if ($academicYear->is_current)
                        <span class="badge-success">العام الحالي</span>
                    @endif
                </h2>
                <form method="POST" action="/academic-years/{{ $academicYear->id }}" data-confirm="تأكيد حذف عام {{ $academicYear->name }} بكل فصوله وتوزيعاته وسجلات الغياب المرتبطة بيه؟">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm">حذف العام</button>
                </form>
            </div>

            <div class="divide-y divide-ink-100 dark:divide-ink-700 mb-4">
                @foreach ($academicYear->terms as $term)
                    <div class="flex flex-wrap justify-between items-center gap-2 py-3">
                        <div>
                            <span class="font-medium">{{ $term->name }}</span>
                            <span class="text-sm text-ink-400">
                                من {{ $term->start_date->format('Y-m-d') }}
                                إلى {{ $term->end_date->format('Y-m-d') }}
                            </span>
                            @if ($term->is_current)
                                <span class="badge-success">الفصل الحالي</span>
                            @endif
                        </div>
                        <div class="flex gap-4 items-center text-sm">
                            @unless ($term->is_current)
                                <form method="POST" action="/terms/{{ $term->id }}/activate">
                                    @csrf
                                    <button type="submit" class="text-gold-600 dark:text-gold-400 font-semibold hover:underline">تفعيل</button>
                                </form>
                            @endunless
                            <form method="POST" action="/terms/{{ $term->id }}" data-confirm="تأكيد حذف {{ $term->name }} وكل سجلات الغياب فيه؟">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">حذف</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($academicYear->terms->count() < 2)
                <form method="POST" action="/academic-years/{{ $academicYear->id }}/terms" class="flex flex-wrap gap-3 items-end pt-2 border-t border-ink-100 dark:border-ink-700">
                    @csrf
                    <div>
                        <label class="label">الفصل</label>
                        <select name="term_number" class="input">
                            <option value="1">الفصل الدراسي الأول</option>
                            <option value="2">الفصل الدراسي الثاني</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">تاريخ البداية</label>
                        <input type="date" name="start_date" class="input">
                    </div>
                    <div>
                        <label class="label">تاريخ النهاية</label>
                        <input type="date" name="end_date" class="input">
                    </div>
                    <button type="submit" class="btn-primary">+ إضافة فصل دراسي</button>
                </form>
            @endif
        </div>
    @endforeach
</x-layout>
