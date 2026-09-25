<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Stage;
use App\Models\Term;
use App\Services\AttendanceStats;
use App\Support\CsvExporter;
use Illuminate\Http\Request;

class SupervisorReportController extends Controller
{
    public function index(Request $request)
    {
        $level = $request->query('level');
        $stageId = $request->query('stage_id');
        $classId = $request->query('class_id');

        if (! in_array($level, StageController::LEVELS, true)) {
            $level = null;
        }

        $term = Term::current();
        $period = $request->query('period', 'term');
        $selected = $request->query('day') ?: ($request->query('week') ?: $request->query('month'));

        if (! array_key_exists($period, AttendanceStats::PERIODS)) {
            $period = 'term';
        }

        $stages = Stage::orderBy('order')->get();
        $classes = ClassRoom::ordered()->with('stage')->get();

        if (! $term) {
            return view('reports.supervisor', [
                'term' => null,
                'period' => $period,
                'stages' => $stages,
                'classes' => $classes,
                'filters' => ['level' => $level, 'stage_id' => $stageId, 'class_id' => $classId],
                'stageRows' => collect(),
                'classRows' => collect(),
                'weeks' => collect(),
                'months' => collect(),
                'selectedWeek' => null,
                'selectedMonth' => null,
                'selectedDay' => null,
            ]);
        }

        [$start, $end] = AttendanceStats::dateRange($term, $period, $selected);

        $stageRows = $stages
            ->when($classId, fn ($c) => $c->filter(fn ($s) => (string) $s->id === (string) $classes->firstWhere('id', $classId)?->stage_id))
            ->when(! $classId && $stageId, fn ($c) => $c->filter(fn ($s) => (string) $s->id === (string) $stageId))
            ->when(! $classId && ! $stageId && $level, fn ($c) => $c->filter(fn ($s) => $s->level === $level))
            ->values()
            ->map(function ($stage) use ($term, $start, $end) {
                $query = AttendanceStats::baseQuerySupervisor($term, $start, $end)
                    ->whereHas('classRoom', fn ($q) => $q->where('stage_id', $stage->id));

                return array_merge(['stage' => $stage], AttendanceStats::summarize($query));
            });

        $classRows = $classes
            ->when($classId, fn ($c) => $c->filter(fn ($cl) => (string) $cl->id === (string) $classId))
            ->when(! $classId && $stageId, fn ($c) => $c->filter(fn ($cl) => (string) $cl->stage_id === (string) $stageId))
            ->when(! $classId && ! $stageId && $level, fn ($c) => $c->filter(fn ($cl) => $cl->stage?->level === $level))
            ->values()
            ->map(function ($class) use ($term, $start, $end) {
                $query = AttendanceStats::baseQuerySupervisor($term, $start, $end)
                    ->where('class_id', $class->id);

                return array_merge(['class' => $class], AttendanceStats::summarize($query));
            });

        if ($request->boolean('export')) {
            return CsvExporter::download('تقرير_غياب_الإداري.csv', ['الفصل', 'حاضر', 'غايب', 'غياب بعذر', 'نسبة الحضور', 'نسبة الغياب'],
                $classRows->map(fn ($r) => [$r['class']->stage?->name.' - فصل '.$r['class']->name, $r['present'], $r['absent'], $r['excused'], $r['attendance_rate'].'%', $r['absence_rate'].'%']));
        }

        return view('reports.supervisor', [
            'term' => $term,
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'stages' => $stages,
            'classes' => $classes,
            'filters' => ['level' => $level, 'stage_id' => $stageId, 'class_id' => $classId],
            'stageRows' => $stageRows,
            'classRows' => $classRows,
            'weeks' => AttendanceStats::weeksInTerm($term),
            'months' => AttendanceStats::monthsInTerm($term),
            'selectedWeek' => $request->query('week'),
            'selectedMonth' => $request->query('month'),
            'selectedDay' => $request->query('day'),
        ]);
    }
}
