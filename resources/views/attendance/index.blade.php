<x-layout title="حصصي">
    <h1 class="page-title mb-1">حصصي</h1>
    <p class="page-subtitle mb-6">سجّل غياب حصص اليوم أو شوف باقي موادك</p>

    @if (session('success'))
        <div class="rounded-xl px-4 py-2 mb-4 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800/50">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="section-title">حصص اليوم</h2>
    <div class="card divide-y divide-ink-100 dark:divide-ink-700 mb-8">
        @forelse ($schedulesToday as $schedule)
            <a href="/attendance/session/{{ $schedule->id }}" class="list-row hover:bg-gold-50 dark:hover:bg-ink-700/50">
                <span>
                    <span class="badge-gold ml-1">حصة {{ $schedule->period_number }}</span>
                    {{ $schedule->assignment->subject->name }} -
                    {{ $schedule->assignment->classRoom->stage?->name }} فصل {{ $schedule->assignment->classRoom->name }}
                </span>
                @if ($schedule->already_taken)
                    <span class="badge-success">✓ تم التسجيل</span>
                @else
                    <span class="text-gold-600 dark:text-gold-400 font-semibold">تسجيل &larr;</span>
                @endif
            </a>
        @empty
            <div class="p-4 text-ink-400">لا توجد حصص لهذا اليوم</div>
        @endforelse
    </div>

    <h2 class="section-title">كل موادي</h2>
    <div class="card divide-y divide-ink-100 dark:divide-ink-700">
        @foreach ($assignments as $assignment)
            <div class="list-row">
                <span>{{ $assignment->subject->name }} - {{ $assignment->classRoom->stage?->name }} فصل {{ $assignment->classRoom->name }}</span>
                <a href="/attendance/{{ $assignment->id }}/stats" class="text-ink-500 dark:text-ink-400 hover:underline text-sm">الإحصائيات</a>
            </div>
        @endforeach
    </div>
</x-layout>
