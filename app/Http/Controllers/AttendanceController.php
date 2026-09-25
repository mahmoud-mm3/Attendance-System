<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // حصص اليوم بس (الأحد للخميس) بالنسبة للمعلم
    public function index()
    {
        $todayDow = now()->dayOfWeek; // 0 = الأحد ... 6 = السبت

        $schedulesToday = collect();

        if ($todayDow <= 4) {
            $schedulesToday = Schedule::whereHas('assignment', function ($query) {
                $query->where('teacher_id', Auth::id());
            })
                ->where('day_of_week', $todayDow)
                ->with(['assignment.classRoom.stage', 'assignment.subject'])
                ->orderBy('period_number')
                ->get();

            // علامة "تم التسجيل بالفعل" لكل حصة حتى يعرف المعلم ما الذي انتهى وما الذي لا يزال متبقيًا
            $takenScheduleIds = Attendance::whereIn('schedule_id', $schedulesToday->pluck('id'))
                ->where('session_date', now()->toDateString())
                ->pluck('schedule_id')
                ->unique();

            $schedulesToday->each(function ($schedule) use ($takenScheduleIds) {
                $schedule->already_taken = $takenScheduleIds->contains($schedule->id);
            });
        }

        // جميع تخصيصات المعلم حتى يتمكن من رؤية إحصائياته حتى لو لم يكن ذلك اليوم
        $assignments = Assignment::with(['classRoom.stage', 'subject'])
            ->where('teacher_id', Auth::id())
            ->get();

        return view('attendance.index', [
            'schedulesToday' => $schedulesToday,
            'assignments' => $assignments,
        ]);
    }

    // أصبح غياب كل حصة (schedule) الآن منفصلًا تمامًا عن أي حصة أخرى، حتى لو كانت لنفس الفصل والمادة
    public function create(Schedule $schedule)
    {
        $assignment = $schedule->assignment;

        if ($assignment->teacher_id !== Auth::id()) {
            abort(403);
        }

        $students = $assignment->classRoom->students()->orderBy('serial_number')->orderBy('name')->get();

        $today = now()->toDateString();

        $existing = Attendance::where('schedule_id', $schedule->id)
            ->where('session_date', $today)
            ->pluck('status', 'student_id');

        $existingReasons = Attendance::where('schedule_id', $schedule->id)
            ->where('session_date', $today)
            ->whereNotNull('excuse_reason')
            ->pluck('excuse_reason', 'student_id');

        return view('attendance.create', [
            'assignment' => $assignment,
            'schedule' => $schedule,
            'students' => $students,
            'today' => $today,
            'existing' => $existing,
            'existingReasons' => $existingReasons,
        ]);
    }

    public function store(Request $request, Schedule $schedule)
    {
        $assignment = $schedule->assignment;

        if ($assignment->teacher_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|array',
            'status.*' => 'required|in:present,absent,excused',
            'reason' => 'array',
            'reason.*' => 'nullable|string|max:50',
        ]);

        foreach ($request->status as $studentId => $status) {
            $reason = trim($request->input("reason.$studentId", ''));

            if ($status === 'excused') {
                if ($reason === '') {
                    return back()->withErrors(['reason' => 'يجب كتابة سبب الغياب بعذر.'])->withInput();
                }

                if (mb_strlen($reason) > 50) {
                    return back()->withErrors(['reason' => 'سبب الغياب بعذر يجب ألا يتجاوز 50 حرفًا.'])->withInput();
                }
            } else {
                $reason = null;
            }

            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'schedule_id' => $schedule->id,
                    'session_date' => now()->toDateString(),
                ],
                [
                    'assignment_id' => $assignment->id,
                    'status' => $status,
                    'academic_year' => Attendance::currentAcademicYear(),
                    'excuse_reason' => $reason,
                    // أي تعديل من المعلم يلغي اعتماد الأدمن السابق، إلى أن يراجعه مرة أخرى
                    'excused_counts_as_absence' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]
            );
        }

        return redirect('/attendance')->with('success', 'تم حفظ غياب الحصة');
    }

    // إحصائيات الحضور والغياب لطلاب فصل معين بمادة المعلم على مدار العام الدراسي
    // تقوم هذه الدالة بتجميع جميع حصص التوزيع معًا (وليس حصة واحدة فقط) لإعطاء صورة عامة عن المادة
    public function stats(Assignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id()) {
            abort(403);
        }

        $year = Attendance::currentAcademicYear();

        $students = $assignment->classRoom->students()
            ->withCount([
                'attendances as present_count' => function ($query) use ($assignment, $year) {
                    $query->where('assignment_id', $assignment->id)
                        ->where('academic_year', $year)
                        ->where('status', 'present');
                },
                'attendances as absent_count' => function ($query) use ($assignment, $year) {
                    $query->where('assignment_id', $assignment->id)
                        ->where('academic_year', $year)
                        ->where('status', 'absent');
                },
                'attendances as excused_count' => function ($query) use ($assignment, $year) {
                    $query->where('assignment_id', $assignment->id)
                        ->where('academic_year', $year)
                        ->where('status', 'excused');
                },
                // الغياب بعذر الذي قرر الأدمن أن يُحتسب غيابًا فعليًا في النسبة
                'attendances as excused_counted_count' => function ($query) use ($assignment, $year) {
                    $query->where('assignment_id', $assignment->id)
                        ->where('academic_year', $year)
                        ->where('status', 'excused')
                        ->where('excused_counts_as_absence', true);
                },
                // جميع الحصص التي سُجِّل فيها غياب أيًا كانت حالتها، حتى تُحسب النسبة عليها جميعًا
                'attendances as total_count' => function ($query) use ($assignment, $year) {
                    $query->where('assignment_id', $assignment->id)
                        ->where('academic_year', $year);
                },
            ])
            ->orderBy('serial_number')
            ->orderBy('name')
            ->get();

        return view('attendance.stats', [
            'assignment' => $assignment,
            'students' => $students,
            'year' => $year,
        ]);
    }
}
