<x-layout title="المواد">
    <h1 class="page-title mb-1">المواد</h1>
    <p class="page-subtitle mb-6">المواد الدراسية المتاحة في النظام</p>

    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card p-5 mb-6">
        <h2 class="section-title">إضافة مادة</h2>
        <form method="POST" action="/subjects" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="label">اسم المادة</label>
                <input type="text" name="name" placeholder="مثلًا: رياضيات" class="input">
            </div>
            <button type="submit" class="btn-primary">+ إضافة</button>
        </form>
    </div>

    <div class="card divide-y divide-ink-100 dark:divide-ink-700">
        @foreach ($subjects as $subject)
            <div class="list-row">
                <span class="font-medium">{{ $subject->name }}</span>
                <form method="POST" action="/subjects/{{ $subject->id }}" data-confirm="تأكيد حذف مادة {{ $subject->name }}؟">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="icon-btn" title="حذف">✕</button>
                </form>
            </div>
        @endforeach
    </div>
</x-layout>
