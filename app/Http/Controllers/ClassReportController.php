<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Stage;
use App\Models\Term;
use App\Services\AttendanceStats;
use App\Support\CsvExporter;
use Illuminate\Http\Request;

class ClassReportController extends Controller
{
    public function index(Request $request)
    {
        $term = Term::current();
        $period = $request->query('period', 'term');
        $stageId = $request->query('stage_id');
        $selected = $request->query('day') ?: ($request->query('week') ?: $request->query('month'));

        if (! array_key_exists($period, AttendanceStats::PERIODS)) {
            $period = 'term';
        }

        $stages = Stage::orderBy('order')->get();

        if (! $term) {
            return view('reports.classes', [
                'term' => null,
                'period' => $period,
                'stages' => $stages,
                'stageId' => $stageId,
                'rows' => collect(),
                'weeks' => collect(),
                'months' => collect(),
                'selectedWeek' => null,
                'selectedMonth' => null,
                'selectedDay' => null,
            ]);
        }

        [$start, $end] = AttendanceStats::dateRange($term, $period, $selected);

        $classes = ClassRoom::ordered()->with('stage')
            ->when($stageId, fn ($q) => $q->where('stage_id', $stageId))
            ->get();

        $rows = $classes->map(function ($class) use ($term, $start, $end) {
            $query = AttendanceStats::baseQuery($term, $start, $end)
                ->whereHas('assignment', fn ($q) => $q->where('class_id', $class->id));

            return array_merge(['class' => $class], AttendanceStats::summarize($query));
        });

        if ($request->boolean('export')) {
            return CsvExporter::download('تقرير_الفصول.csv', ['الفصل', 'حاضر', 'غايب', 'غياب بعذر', 'نسبة الحضور', 'نسبة الغياب'],
                $rows->map(fn ($r) => [$r['class']->stage?->name.' - فصل '.$r['class']->name, $r['present'], $r['absent'], $r['excused'], $r['attendance_rate'].'%', $r['absence_rate'].'%']));
        }

        return view('reports.classes', [
            'term' => $term,
            'period' => $period,
            'stages' => $stages,
            'stageId' => $stageId,
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
