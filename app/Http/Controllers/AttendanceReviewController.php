<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SupervisorAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceReviewController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $classId = $request->query('class_id');

        $attendances = Attendance::with(['student', 'schedule', 'assignment.classRoom.stage', 'assignment.subject', 'assignment.teacher'])
            ->where('session_date', $date)
            ->when($classId, fn ($q) => $q->whereHas('assignment', fn ($aq) => $aq->where('class_id', $classId)))
            ->get();

        // تكون كل حصة (schedule) مجموعة قائمة بذاتها ومنفصلة عن أي حصة أخرى حتى لو كانت لنفس الفصل والمادة.
        // السجلات القديمة التي لا تحتوي على schedule_id (قبل التحديث) تُجمَّع حسب التوزيع بدلًا من ذلك
        $groups = $attendances
            ->groupBy(fn ($a) => $a->schedule_id ? 'schedule-'.$a->schedule_id : 'legacy-'.$a->assignment_id)
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'schedule_id' => $first->schedule_id,
                    'assignment_id' => $first->assignment_id,
                    'period_number' => $first->schedule?->period_number,
                    'teacher' => $first->assignment->teacher,
                    'subject' => $first->assignment->subject,
                    'classRoom' => $first->assignment->classRoom,
                    'rows' => $rows->sortBy(fn ($a) => $a->student->serial_number ?? 0)->values(),
                    'pendingCount' => $rows->whereNull('reviewed_at')->count(),
                ];
            })
            ->sortBy([
                fn ($g) => $g['pendingCount'] === 0, // المجموعات التي لا تزال معلقة تظهر أولًا
                fn ($g) => $g['period_number'] ?? 0,
                fn ($g) => $g['teacher']->name ?? '',
            ])
            ->values();

        // ثم تُجمَّع جميع هذه الحصص تحت فصلها، حتى يتمكن الأدمن من طي فصل انتهت مراجعته
        // والتركيز فقط على الفصول التي لا يزال فيها غياب معلق - وهذا مهم جدًا عندما يكبر العدد
        $classGroups = $groups
            ->groupBy(fn ($g) => $g['classRoom']->id)
            ->map(function ($sessions) {
                $classRoom = $sessions->first()['classRoom'];

                return [
                    'classRoom' => $classRoom,
                    'stageOrder' => $classRoom->stage->order ?? 0,
                    'sessions' => $sessions->values(),
                    'pendingCount' => $sessions->sum('pendingCount'),
                ];
            })
            ->sortBy([
                fn ($g) => $g['pendingCount'] === 0,
                fn ($g) => $g['stageOrder'],
            ])
            ->values();

        // تتم مراجعة غياب الإداري هنا أيضًا بمفرده، لكنه يبقى منفصلًا عن غياب المعلمين، ومجمّعًا حسب الفصل بالأسلوب نفسه
        $supervisorAttendances = SupervisorAttendance::with(['student', 'classRoom.stage', 'supervisor'])
            ->where('session_date', $date)
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->get();

        $supervisorGroups = $supervisorAttendances
            ->groupBy('class_id')
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'class_id' => $first->class_id,
                    'classRoom' => $first->classRoom,
                    'supervisor' => $first->supervisor,
                    'stageOrder' => $first->classRoom->stage->order ?? 0,
                    'rows' => $rows->sortBy(fn ($a) => $a->student->serial_number ?? 0)->values(),
                    'pendingCount' => $rows->whereNull('reviewed_at')->count(),
                ];
            })
            ->sortBy([
                fn ($g) => $g['pendingCount'] === 0,
                fn ($g) => $g['stageOrder'],
            ])
            ->values();

        return view('attendance-review.index', [
            'classGroups' => $classGroups,
            'supervisorGroups' => $supervisorGroups,
            'date' => $date,
            'classId' => $classId,
            'classes' => \App\Models\ClassRoom::ordered()->with('stage')->get(),
        ]);
    }

    // اعتماد كل غياب لا يزال معلقًا في حصة معينة دفعة واحدة، دون المساس بحالة أي طالب
    public function approveGroup(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $query = Attendance::where('session_date', $request->date)->whereNull('reviewed_at');

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        } elseif ($request->filled('assignment_id')) {
            // توافقًا مع سجلات قديمة قبل ربط الغياب بالحصة تحديدًا
            $query->whereNull('schedule_id')->where('assignment_id', $request->assignment_id);
        } else {
            return back()->withErrors(['name' => 'محتاج تحدد الحصة الأول.']);
        }

        $count = $query->update(['reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        return back()->with('success', "تم اعتماد $count غياب دفعة واحدة");
    }

    // نفس الفكرة لغياب الإداري، لكن مجمّع حسب الفصل بدل التوزيع
    public function approveSupervisorGroup(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        $count = SupervisorAttendance::where('class_id', $request->class_id)
            ->where('session_date', $request->date)
            ->whereNull('reviewed_at')
            ->update(['reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        return back()->with('success', "تم اعتماد $count غياب دفعة واحدة");
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,excused',
            'reason' => 'nullable|string|max:50',
            'excused_counts_as_absence' => 'nullable|in:0,1',
        ]);

        $reason = trim((string) $request->reason);

        if ($request->status === 'excused') {
            if ($reason === '') {
                return back()->withErrors(['reason' => 'يجب أن يكون هناك سبب لغياب بعذر.']);
            }
            if (mb_strlen($reason) > 50) {
                return back()->withErrors(['reason' => 'السبب يجب ألا يتجاوز 50 حرفًا.']);
            }
        } else {
            $reason = null;
        }

        $attendance->update([
            'status' => $request->status,
            'excuse_reason' => $reason,
            'excused_counts_as_absence' => $request->status === 'excused'
                ? $request->boolean('excused_counts_as_absence')
                : null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد الغياب');
    }

    public function updateSupervisor(Request $request, SupervisorAttendance $supervisorAttendance)
    {
        $request->validate([
            'status' => 'required|in:present,absent,excused',
            'reason' => 'nullable|string|max:50',
            'excused_counts_as_absence' => 'nullable|in:0,1',
        ]);

        $reason = trim((string) $request->reason);

        if ($request->status === 'excused') {
            if ($reason === '') {
                return back()->withErrors(['reason' => 'يجب أن يكون هناك سبب لغياب بعذر.']);
            }
            if (mb_strlen($reason) > 50) {
                return back()->withErrors(['reason' => 'السبب يجب ألا يتجاوز 50 حرفًا.']);
            }
        } else {
            $reason = null;
        }

        $supervisorAttendance->update([
            'status' => $request->status,
            'excuse_reason' => $reason,
            'excused_counts_as_absence' => $request->status === 'excused'
                ? $request->boolean('excused_counts_as_absence')
                : null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد غياب الإداري');
    }
}
