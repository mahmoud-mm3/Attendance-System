<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SupervisorAttendance;
use App\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AttendanceStats
{
    public const PERIODS = ['day' => 'يوم محدد', 'week' => 'الأسبوع الحالي', 'month' => 'الشهر الحالي', 'term' => 'الفصل الدراسي كله'];

    private const ARABIC_MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
        7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    // كل أسابيع الفصل الدراسي (من الأحد للخميس)، كل أسبوع بقيمته (تاريخ بداية الأسبوع) واسمه المعروض
    public static function weeksInTerm(Term $term): array
    {
        $weeks = [];
        $cursor = Carbon::parse($term->start_date)->startOfWeek(Carbon::SUNDAY);
        $termEnd = Carbon::parse($term->end_date);

        while ($cursor->lte($termEnd)) {
            $weekEnd = $cursor->copy()->addDays(4);
            $weeks[] = [
                'value' => $cursor->toDateString(),
                'label' => 'من '.$cursor->format('Y-m-d').' إلى '.$weekEnd->format('Y-m-d'),
            ];
            $cursor = $cursor->copy()->addWeek();
        }

        return $weeks;
    }

    // جميع الشهور التي يغطيها الفصل الدراسي
    public static function monthsInTerm(Term $term): array
    {
        $months = [];
        $cursor = Carbon::parse($term->start_date)->startOfMonth();
        $termEnd = Carbon::parse($term->end_date);

        while ($cursor->lte($termEnd)) {
            $months[] = [
                'value' => $cursor->format('Y-m'),
                'label' => self::ARABIC_MONTHS[(int) $cursor->format('n')].' '.$cursor->format('Y'),
            ];
            $cursor = $cursor->copy()->addMonthNoOverflow();
        }

        return $months;
    }

    // نطاق التاريخ المطلوب حساب الإحصائية عليه، محصور داخل حدود الفصل الدراسي نفسه
    // $selected: يوم محدد (YYYY-MM-DD) لو period=day، أو تاريخ بداية أسبوع محدد لو period=week، أو شهر محدد (YYYY-MM) لو period=month
    public static function dateRange(Term $term, string $period, ?string $selected = null): array
    {
        $termStart = Carbon::parse($term->start_date);
        $termEnd = Carbon::parse($term->end_date);

        if ($period === 'day') {
            $start = $selected ? Carbon::parse($selected) : now()->copy();
            $end = $start->copy();
        } elseif ($period === 'week') {
            if ($selected) {
                $start = Carbon::parse($selected)->startOfWeek(Carbon::SUNDAY);
            } else {
                $start = now()->copy()->startOfWeek(Carbon::SUNDAY);
            }
            $end = $start->copy()->addDays(4); // الأحد للخميس
        } elseif ($period === 'month') {
            if ($selected) {
                $start = Carbon::createFromFormat('Y-m-d', $selected.'-01')->startOfMonth();
            } else {
                $start = now()->copy()->startOfMonth();
            }
            $end = $start->copy()->endOfMonth();
        } else {
            $start = $termStart;
            $end = $termEnd;
        }

        // يتم تقليص النطاق إلى حدود الفصل الدراسي حتى لا نتجاوز فترته
        $start = $start->lt($termStart) ? $termStart : $start;
        $end = $end->gt($termEnd) ? $termEnd : $end;

        return [$start, $end];
    }

    // بداية Query عامة: غياب طلاب في فصل دراسي معين وخلال نطاق تاريخ معين
    public static function baseQuery(Term $term, Carbon $start, Carbon $end): Builder
    {
        return Attendance::whereHas('assignment', fn ($q) => $q->where('term_id', $term->id))
            ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()]);
    }

    // الفكرة نفسها بالضبط لكن لغياب الإداري (منفصل عن غياب المعلمين تمامًا)
    public static function baseQuerySupervisor(Term $term, Carbon $start, Carbon $end): Builder
    {
        return SupervisorAttendance::where('term_id', $term->id)
            ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()]);
    }

    // يلخّص أي Query على جدول Attendance لأرقام حاضر/غايب/غياب بعذر ونسبة الحضور والغياب
    public static function summarize(Builder $attendanceQuery): array
    {
        $present = (clone $attendanceQuery)->where('status', 'present')->count();
        $absent = (clone $attendanceQuery)->where('status', 'absent')->count();
        $excused = (clone $attendanceQuery)->where('status', 'excused')->count();
        $excusedCounted = (clone $attendanceQuery)->where('status', 'excused')
            ->where('excused_counts_as_absence', true)->count();

        $total = $present + $absent + $excused;
        $absenceEquivalent = $absent + $excusedCounted;

        return [
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'total' => $total,
            'attendance_rate' => $total > 0 ? round((($total - $absenceEquivalent) / $total) * 100) : 0,
            'absence_rate' => $total > 0 ? round(($absenceEquivalent / $total) * 100) : 0,
        ];
    }
}
