<x-layout title="الصفوف الدراسية">
    <h1 class="page-title mb-1">الصفوف الدراسية</h1>
    <p class="page-subtitle mb-6">إدارة الصفوف داخل كل مرحلة من المراحل الثلاث</p>

    @if ($errors->any())
        <div class="rounded-xl p-3 mb-6 bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card p-5 mb-10">
        <h2 class="section-title">إضافة صف دراسي جديد</h2>
        <form method="POST" action="/stages" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="min-w-[160px]">
                <label class="label">المرحلة</label>
                <select name="level" class="input">
                    @foreach ($levels as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="label">اسم الصف</label>
                <input type="text" name="name" placeholder="مثلًا: الصف الأول الابتدائي" class="input">
            </div>
            <div class="min-w-[140px]">
                <label class="label">ترتيب الصف</label>
                <input type="number" name="order" placeholder="0، 1، 2..." min="0" class="input">
            </div>
            <button type="submit" class="btn-primary">+ إضافة</button>
        </form>
    </div>

    @foreach ($levels as $level)
        <div class="level-section">
            <div class="level-header">
                <span class="level-badge">{{ mb_substr($level, 0, 1) }}</span>
                <h2 class="text-xl font-extrabold text-ink-900 dark:text-white">المرحلة {{ $level }}</h2>
            </div>

            <div class="card divide-y divide-ink-100 dark:divide-ink-700">
                @forelse ($stagesByLevel->get($level, collect()) as $stage)
                    <div class="list-row">
                        <span>
                            <span class="font-medium">{{ $stage->name }}</span>
                            <span class="text-ink-400 text-sm">({{ $stage->class_rooms_count }} فصل)</span>
                        </span>
                        <form method="POST" action="/stages/{{ $stage->id }}" data-confirm="تأكيد حذف {{ $stage->name }}؟">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn" title="حذف">✕</button>
                        </form>
                    </div>
                @empty
                    <p class="p-4 text-ink-400">لا توجد صفوف في هذه المرحلة بعد.</p>
                @endforelse
            </div>
        </div>
    @endforeach
</x-layout>
