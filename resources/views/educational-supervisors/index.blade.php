<x-layout title="المشرفون التربويون">
    <h1 class="page-title mb-1">المشرفون التربويون</h1>
    <p class="page-subtitle mb-6">حسابات اطّلاع فقط على تقارير الغياب، بدون صلاحية تسجيل أو تعديل</p>

    <div class="card p-5 mb-6">
        <h2 class="section-title">إضافة مشرف تربوي</h2>
        <form method="POST" action="/educational-supervisors" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[160px]">
                <label class="label">الاسم</label>
                <input type="text" name="name" class="input">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="label">البريد الإلكتروني</label>
                <input type="email" name="email" class="input">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="label">كلمة المرور</label>
                <input type="password" name="password" class="input">
            </div>
            <button type="submit" class="btn-primary">+ إضافة</button>
        </form>
    </div>

    <div class="card divide-y divide-ink-100 dark:divide-ink-700">
        @forelse ($educationalSupervisors as $supervisor)
            <div class="list-row">
                <span><span class="font-medium">{{ $supervisor->name }}</span> <span class="text-ink-400 text-sm">- {{ $supervisor->email }}</span></span>
                <form method="POST" action="/educational-supervisors/{{ $supervisor->id }}" data-confirm="تأكيد حذف {{ $supervisor->name }}؟">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="icon-btn" title="حذف">✕</button>
                </form>
            </div>
        @empty
            <p class="p-4 text-ink-400">لا يوجد مشرفون تربويون حتى الآن.</p>
        @endforelse
    </div>
</x-layout>
