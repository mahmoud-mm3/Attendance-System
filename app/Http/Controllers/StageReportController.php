<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Term;
use App\Services\AttendanceStats;
use App\Support\CsvExporter;
use Illuminate\Http\Request;

class StageReportController extends Controller
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
            return view('reports.stages', [
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

        $rows = Stage::orderBy('order')->get()->map(function ($stage) use ($term, $start, $end) {
            $query = AttendanceStats::baseQuery($term, $start, $end)
                ->whereHas('assignment.classRoom', fn ($q) => $q->where('stage_id', $stage->id));

            return array_merge(['stage' => $stage], AttendanceStats::summarize($query));
        });

        if ($request->boolean('export')) {
            return CsvExporter::download('تقرير_الصفوف.csv', ['الصف', 'حاضر', 'غايب', 'غياب بعذر', 'نسبة الحضور', 'نسبة الغياب'],
                $rows->map(fn ($r) => [$r['stage']->name, $r['present'], $r['absent'], $r['excused'], $r['attendance_rate'].'%', $r['absence_rate'].'%']));
        }

        return view('reports.stages', [
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
