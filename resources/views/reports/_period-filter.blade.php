@php
    $paramsWithoutPeriod = collect(request()->query())->except(['period', 'week', 'month', 'day'])->all();
@endphp
<div class="flex gap-2 mb-4 flex-wrap">
    @foreach (\App\Services\AttendanceStats::PERIODS as $key => $label)
        <a href="{{ request()->url() }}?{{ http_build_query(array_merge($paramsWithoutPeriod, ['period' => $key])) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ $period === $key ? 'bg-gold-500 text-ink-900' : 'bg-white dark:bg-ink-800 text-ink-600 dark:text-ink-300 border border-ink-200 dark:border-ink-600 hover:border-gold-400' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if ($period === 'day')
    <form method="GET" class="mb-6">
        @foreach ($paramsWithoutPeriod as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="period" value="day">
        <input type="date" name="day" class="input max-w-sm"
               value="{{ $selectedDay ?? now()->toDateString() }}"
               min="{{ \Illuminate\Support\Carbon::parse($term->start_date)->toDateString() }}"
               max="{{ \Illuminate\Support\Carbon::parse($term->end_date)->toDateString() }}"
               onchange="this.form.submit()">
    </form>
@endif

@if ($period === 'week' && !empty($weeks))
    <form method="GET" class="mb-6">
        @foreach ($paramsWithoutPeriod as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="period" value="week">
        <select name="week" class="input max-w-sm" onchange="this.form.submit()">
            <option value="">الأسبوع الحالي</option>
            @foreach ($weeks as $week)
                <option value="{{ $week['value'] }}" {{ $selectedWeek === $week['value'] ? 'selected' : '' }}>
                    {{ $week['label'] }}
                </option>
            @endforeach
        </select>
    </form>
@endif

@if ($period === 'month' && !empty($months))
    <form method="GET" class="mb-6">
        @foreach ($paramsWithoutPeriod as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="period" value="month">
        <select name="month" class="input max-w-sm" onchange="this.form.submit()">
            <option value="">الشهر الحالي</option>
            @foreach ($months as $month)
                <option value="{{ $month['value'] }}" {{ $selectedMonth === $month['value'] ? 'selected' : '' }}>
                    {{ $month['label'] }}
                </option>
            @endforeach
        </select>
    </form>
@endif
