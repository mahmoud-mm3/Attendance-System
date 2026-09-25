<x-layout title="الطلاب">
    <h1 class="page-title mb-1">الطلاب</h1>
    <p class="page-subtitle mb-6">إدارة بيانات الطلاب وربطهم بالفصول</p>

    @if (session('success'))
        <div class="rounded-xl p-3 mb-6 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800/50">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">{{ $errors->first() }}</div>
    @endif

    <div class="card p-5 mb-6" data-row-adder>
        <h2 class="section-title">إضافة طلاب</h2>
        <p class="text-sm text-ink-400 mb-3">أضف جميع الطلاب الذين تريدهم صفًا صفًا، ثم احفظهم جميعًا مرة واحدة. يُحتسب الرقم التسلسلي تلقائيًا حسب رقم الهوية داخل كل فصل.</p>

        <form method="POST" action="/students">
            @csrf
            <div data-rows class="space-y-2 mb-3">
                <div data-row class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[160px]">
                        <label class="label">اسم الطالب</label>
                        <input type="text" name="name[]" class="input">
                    </div>
                    <div class="w-44">
                        <label class="label">رقم الهوية</label>
                        <input type="text" name="national_id[]" class="input">
                    </div>
                    <div class="min-w-[180px]">
                        <label class="label">الفصل</label>
                        <select name="class_id[]" class="input">
                            <option value="">اختر الفصل</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->stage?->name }} - فصل {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
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

    <form method="GET" action="/students" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="ابحث باسم الطالب أو رقم الهوية..."
               class="input flex-1">
        <button type="submit" class="btn-primary">بحث</button>
        @if ($search !== '')
            <a href="/students" class="btn-ghost">مسح</a>
        @endif
    </form>

    <div class="table-wrap">
        <table class="table-base">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الاسم</th>
                    <th>رقم الهوية</th>
                    <th>الفصل</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $lastClassId = null; @endphp
                @foreach ($students as $student)
                    @if ($student->class_id !== $lastClassId)
                        @php $lastClassId = $student->class_id; @endphp
                        <tr class="bg-ink-50 dark:bg-ink-800/60">
                            <td colspan="5" class="font-bold text-gold-600 dark:text-gold-400">
                                {{ $student->classRoom->stage?->name }} — فصل {{ $student->classRoom->name }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>{{ $student->serial_number }}</td>
                        <td class="font-medium text-ink-900 dark:text-white">{{ $student->name }}</td>
                        <td>{{ $student->national_id }}</td>
                        <td><span class="badge-gold">{{ $student->classRoom->stage?->name }} - فصل {{ $student->classRoom->name }}</span></td>
                        <td>
                            <form method="POST" action="/students/{{ $student->id }}" data-confirm="تأكيد حذف الطالب {{ $student->name }}؟ سيُحذف معه كل سجل غيابه.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" title="حذف">✕</button>
                            </form>
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
