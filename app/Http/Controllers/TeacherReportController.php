<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Term;
use App\Services\AttendanceStats;
use App\Support\CsvExporter;
use Illuminate\Http\Request;

class TeacherReportController extends Controller
{
    public function index(Request $request)
    {
        $term = Term::current();
        $period = $request->query('period', 'term');
        $selected = $request->query('day') ?: ($request->query('week') ?: $request->query('month'));

        if (! array_key_exists($period, AttendanceStats::PERIODS)) {
            $period = 'term';
        }

        if (! $term) {
            return view('reports.teachers', [
                'term' => null,
                'period' => $period,
                'rows' => collect(),
                'weeks' => collect(),
                'months' => collect(),
                'selectedWeek' => null,
                'selectedMonth' => null,
                'selectedDay' => null,
            ]);
        }

        [$start, $end] = AttendanceStats::dateRange($term, $period, $selected);

        // كل توليفة (معلم × صف) موجودة فعليًا في توزيع الفصل الدراسي الحالي
        $pairs = Assignment::where('term_id', $term->id)
            ->with(['teacher', 'classRoom.stage'])
            ->get()
            ->filter(fn ($a) => $a->teacher && $a->classRoom?->stage)
            ->unique(fn ($a) => $a->teacher_id.'-'.$a->classRoom->stage_id)
            ->map(fn ($a) => ['teacher' => $a->teacher, 'stage' => $a->classRoom->stage])
            ->sortBy([
                fn ($p) => $p['stage']->order,
                fn ($p) => $p['teacher']->name,
            ]);

        $rows = $pairs->map(function ($pair) use ($term, $start, $end) {
            $query = AttendanceStats::baseQuery($term, $start, $end)
                ->whereHas('assignment', function ($q) use ($pair) {
                    $q->where('teacher_id', $pair['teacher']->id)
                        ->whereHas('classRoom', fn ($q2) => $q2->where('stage_id', $pair['stage']->id));
                });

            return array_merge(
                ['teacher' => $pair['teacher'], 'stage' => $pair['stage']],
                AttendanceStats::summarize($query)
            );
        })->values();

        if ($request->boolean('export')) {
            return CsvExporter::download('تقرير_المعلمين.csv', ['المعلم', 'الصف', 'حاضر', 'غايب', 'غياب بعذر', 'نسبة الحضور', 'نسبة الغياب'],
                $rows->map(fn ($r) => [$r['teacher']->name, $r['stage']->name, $r['present'], $r['absent'], $r['excused'], $r['attendance_rate'].'%', $r['absence_rate'].'%']));
        }

        return view('reports.teachers', [
            'term' => $term,
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'rows' => $rows,
            'weeks' => AttendanceStats::weeksInTerm($term),
            'months' => AttendanceStats::monthsInTerm($term),
            'selectedWeek' => $request->query('week'),
            'selectedMonth' => $request->query('month'),
            'selectedDay' => $request->query('day'),
        ]);
    }
}
