<x-layout title="المعلمين">
    <h1 class="page-title mb-1">المعلمين</h1>
    <p class="page-subtitle mb-6">حسابات المعلمين الذين سيسجّلون الحضور</p>

    @if (session('success'))
        <div class="rounded-xl p-3 mb-6 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800/50">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">{{ $errors->first() }}</div>
    @endif

    <div class="card p-5 mb-6" data-row-adder>
        <h2 class="section-title">إضافة معلمين</h2>
        <p class="text-sm text-ink-400 mb-3">أضف جميع المعلمين الذين تريدهم صفًا صفًا، ثم احفظهم جميعًا مرة واحدة</p>

        <form method="POST" action="/teachers">
            @csrf
            <div data-rows class="space-y-2 mb-3">
                <div data-row class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[160px]">
                        <label class="label">الاسم</label>
                        <input type="text" name="name[]" class="input">
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="label">البريد الإلكتروني</label>
                        <input type="email" name="email[]" class="input">
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="label">كلمة المرور</label>
                        <input type="password" name="password[]" class="input">
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

    <input type="text" data-live-search="[data-teacher-row]" placeholder="ابحث باسم المعلم أو البريد الإلكتروني..." class="input mb-4">

    <div class="card divide-y divide-ink-100 dark:divide-ink-700">
        @foreach ($teachers as $teacher)
            <div class="list-row" data-teacher-row data-search-text="{{ $teacher->name }} {{ $teacher->email }}">
                <span><span class="font-medium">{{ $teacher->name }}</span> <span class="text-ink-400 text-sm">- {{ $teacher->email }}</span></span>
                <form method="POST" action="/teachers/{{ $teacher->id }}" data-confirm="تأكيد حذف {{ $teacher->name }}؟">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="icon-btn" title="حذف">✕</button>
                </form>
            </div>
        @endforeach
    </div>
</x-layout>
