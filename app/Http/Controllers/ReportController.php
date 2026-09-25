<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Stage;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\AttendanceStats;
use App\Support\CsvExporter;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $level = $request->input('level');
        $stageId = $request->input('stage_id');
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $teacherId = $request->input('teacher_id');

        if (! in_array($level, StageController::LEVELS, true)) {
            $level = null;
        }

        // فلتر الصف/الفصل: كل ما هو أكثر تحديدًا (الفصل) يُغني عمّا هو أعم (الصف ثم المرحلة)
        $applyGradeFilter = function ($q) use ($level, $stageId, $classId) {
            if ($classId) {
                $q->where('class_id', $classId);
            } elseif ($stageId) {
                $q->whereHas('classRoom', fn ($q2) => $q2->where('stage_id', $stageId));
            } elseif ($level) {
                $q->whereHas('classRoom.stage', fn ($q2) => $q2->where('level', $level));
            }
        };

        // فلتر الفترة الزمنية (يوم/أسبوع/شهر/الفصل الدراسي كله) — يعتمد على وجود فصل دراسي مفعّل
        $term = Term::current();
        $period = $request->query('period', 'term');
        $selected = $request->query('day') ?: ($request->query('week') ?: $request->query('month'));

        if (! array_key_exists($period, AttendanceStats::PERIODS)) {
            $period = 'term';
        }

        $start = null;
        $end = null;
        if ($term) {
            [$start, $end] = AttendanceStats::dateRange($term, $period, $selected);
        }

        $applyDateFilter = function ($q) use ($start, $end) {
            if ($start && $end) {
                $q->whereBetween('session_date', [$start->toDateString(), $end->toDateString()]);
            }
        };

        $baseQuery = function () use ($applyGradeFilter, $applyDateFilter, $subjectId) {
            return tap(Attendance::whereHas('assignment', function ($q) use ($applyGradeFilter, $subjectId) {
                $applyGradeFilter($q);
                if ($subjectId) {
                    $q->where('subject_id', $subjectId);
                }
            }), $applyDateFilter);
        };

        // إجمالي عام
        $totalPresent = $baseQuery()->where('status', 'present')->count();
        $totalAbsent = $baseQuery()->where('status', 'absent')->count();
        $totalSessions = $totalPresent + $totalAbsent;
        $overallAttendanceRate = $totalSessions > 0 ? round($totalPresent / $totalSessions * 100) : 0;
        $overallAbsenceRate = $totalSessions > 0 ? round($totalAbsent / $totalSessions * 100) : 0;

        // حسب كل فصل
        $classesQuery = ClassRoom::ordered()->with('stage')
            ->when($classId, fn ($q) => $q->where('classes.id', $classId))
            ->when(! $classId && $stageId, fn ($q) => $q->where('stage_id', $stageId))
            ->when(! $classId && ! $stageId && $level, fn ($q) => $q->whereHas('stage', fn ($q2) => $q2->where('level', $level)))
            ->get();

        $perClass = $classesQuery->map(function ($class) use ($subjectId, $applyDateFilter) {
            $q = tap(Attendance::whereHas('assignment', function ($aq) use ($class, $subjectId) {
                $aq->where('class_id', $class->id);
                if ($subjectId) {
                    $aq->where('subject_id', $subjectId);
                }
            }), $applyDateFilter);
            $present = (clone $q)->where('status', 'present')->count();
            $absent = (clone $q)->where('status', 'absent')->count();
            $total = $present + $absent;

            return [
                'class' => $class,
                'present' => $present,
                'absent' => $absent,
                'attendance_rate' => $total > 0 ? round($present / $total * 100) : 0,
                'absence_rate' => $total > 0 ? round($absent / $total * 100) : 0,
            ];
        });

        // حسب كل مادة (قابل للتضييق أيضًا حسب المعلم)
        $perSubject = Subject::when($subjectId, fn ($q) => $q->where('id', $subjectId))
            ->orderBy('name')
            ->get()
            ->map(function ($subject) use ($applyGradeFilter, $applyDateFilter, $teacherId) {
                $q = tap(Attendance::whereHas('assignment', function ($aq) use ($subject, $applyGradeFilter, $teacherId) {
                    $aq->where('subject_id', $subject->id);
                    $applyGradeFilter($aq);
                    if ($teacherId) {
                        $aq->where('teacher_id', $teacherId);
                    }
                }), $applyDateFilter);
                $present = (clone $q)->where('status', 'present')->count();
                $absent = (clone $q)->where('status', 'absent')->count();
                $total = $present + $absent;

                return [
                    'subject' => $subject,
                    'present' => $present,
                    'absent' => $absent,
                    'attendance_rate' => $total > 0 ? round($present / $total * 100) : 0,
                    'absence_rate' => $total > 0 ? round($absent / $total * 100) : 0,
                ];
            });

        // حسب كل معلم
        $perTeacher = User::teachersOnly()
            ->when($teacherId, fn ($q) => $q->where('id', $teacherId))
            ->orderBy('name')
            ->get()
            ->map(function ($teacher) use ($applyGradeFilter, $applyDateFilter, $subjectId) {
                $q = tap(Attendance::whereHas('assignment', function ($aq) use ($teacher, $applyGradeFilter, $subjectId) {
                    $aq->where('teacher_id', $teacher->id);
                    $applyGradeFilter($aq);
                    if ($subjectId) {
                        $aq->where('subject_id', $subjectId);
                    }
                }), $applyDateFilter);
                $present = (clone $q)->where('status', 'present')->count();
                $absent = (clone $q)->where('status', 'absent')->count();
                $total = $present + $absent;

                return [
                    'teacher' => $teacher,
                    'present' => $present,
                    'absent' => $absent,
                    'attendance_rate' => $total > 0 ? round($present / $total * 100) : 0,
                    'absence_rate' => $total > 0 ? round($absent / $total * 100) : 0,
                ];
            })
            ->filter(fn ($row) => $row['present'] + $row['absent'] > 0)
            ->values();

        // تفاصيل كل طالب
        // ملاحظة أداء: بنجيب كل سجلات الغياب مع كل الطلاب بـquery واحد إضافي (Eager Loading)
        // بدلًا من تنفيذ استعلام منفصل داخل map() لكل طالب - إذ كان ذلك يتسبب في مئات الاستعلامات
        // في مدرسة فيها 300 طالب، وهو السبب الرئيسي في بطء صفحة التقارير.
        $students = Student::with([
                'classRoom.stage',
                'attendances' => function ($q) use ($subjectId, $applyDateFilter) {
                    $q->when($subjectId, fn ($qq) => $qq->whereHas('assignment', fn ($aq) => $aq->where('subject_id', $subjectId)))
                        ->with('assignment.subject');
                    $applyDateFilter($q);
                },
            ])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when(! $classId && $stageId, fn ($q) => $q->whereHas('classRoom', fn ($q2) => $q2->where('stage_id', $stageId)))
            ->when(! $classId && ! $stageId && $level, fn ($q) => $q->whereHas('classRoom.stage', fn ($q2) => $q2->where('level', $level)))
            ->orderBy('name')
            ->get()
            ->map(function ($student) {
                $attendances = $student->attendances;

                $present = $attendances->where('status', 'present')->count();
                $absent = $attendances->where('status', 'absent')->count();
                $total = $present + $absent;

                $missedSubjects = $attendances->where('status', 'absent')
                    ->pluck('assignment.subject.name')
                    ->filter()
                    ->unique()
                    ->values();

                // علم "غياب متكرر": غاب في يوم واحد على الأقل من كل يوم من آخر 3 أيام
                // وجود سجل غياب للطالب (لا يُشترط غيابه في جميع حصص اليوم، إذ يكفي غياب حصة واحدة ليُحتسب اليوم كله)
                $lastThreeDays = $attendances
                    ->groupBy(fn ($a) => $a->session_date->toDateString())
                    ->sortKeysDesc()
                    ->take(3);

                $flagged = $lastThreeDays->count() === 3
                    && $lastThreeDays->every(fn ($dayRows) => $dayRows->contains(fn ($a) => $a->status === 'absent'));

                return [
                    'student' => $student,
                    'present' => $present,
                    'absent' => $absent,
                    'attendance_rate' => $total > 0 ? round($present / $total * 100) : 0,
                    'missed_subjects' => $missedSubjects,
                    'flagged' => $flagged,
                ];
            });

        $flaggedCount = $students->where('flagged', true)->count();

        if ($request->boolean('export')) {
            return CsvExporter::download('تقرير_الطلاب.csv',
                ['اسم الطالب', 'الفصل', 'حاضر', 'غايب', 'نسبة الحضور', 'غياب متكرر', 'المواد التي تغيب عنها'],
                $students->map(fn ($r) => [
                    $r['student']->name,
                    $r['student']->classRoom->stage?->name.' - فصل '.$r['student']->classRoom->name,
                    $r['present'],
                    $r['absent'],
                    $r['attendance_rate'].'%',
                    $r['flagged'] ? 'نعم' : '-',
                    $r['missed_subjects']->implode('، '),
                ]));
        }

        // يتم احتساب جميع الطلاب حتى تكون الأرقام والتصدير صحيحة، لكن يتم العرض صفحة تلو الأخرى حتى لا تُثقَل الصفحة إذا كان عدد الطلاب كبيرًا
        $page = (int) $request->query('page', 1);
        $perPage = 20;
        $studentsPage = new \Illuminate\Pagination\LengthAwarePaginator(
            $students->forPage($page, $perPage)->values(),
            $students->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('reports.index', [
            'levels' => StageController::LEVELS,
            'stages' => Stage::orderBy('order')->get(),
            'classes' => ClassRoom::ordered()->with('stage')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => User::teachersOnly()->orderBy('name')->get(),
            'filters' => [
                'level' => $level,
                'stage_id' => $stageId,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
            ],
            'term' => $term,
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'weeks' => $term ? AttendanceStats::weeksInTerm($term) : collect(),
            'months' => $term ? AttendanceStats::monthsInTerm($term) : collect(),
            'selectedWeek' => $request->query('week'),
            'selectedMonth' => $request->query('month'),
            'selectedDay' => $request->query('day'),
            'totalPresent' => $totalPresent,
            'totalAbsent' => $totalAbsent,
            'overallAttendanceRate' => $overallAttendanceRate,
            'overallAbsenceRate' => $overallAbsenceRate,
            'perClass' => $perClass,
            'perSubject' => $perSubject,
            'perTeacher' => $perTeacher,
            'students' => $studentsPage,
            'flaggedCount' => $flaggedCount,
            'year' => Attendance::currentAcademicYear(),
        ]);
    }
}
