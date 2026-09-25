<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Stage;
use App\Models\SupervisorAttendance;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupervisorAttendanceController extends Controller
{
    // الإداري بياخد غياب المدرسة كلها مرة واحدة بس في اليوم
    public function index()
    {
        $term = Term::current();
        $today = now()->toDateString();

        $alreadyTaken = SupervisorAttendance::where('session_date', $today)->exists();

        if ($alreadyTaken) {
            $summary = SupervisorAttendance::with(['classRoom.stage', 'supervisor'])
                ->where('session_date', $today)
                ->get()
                ->groupBy(fn ($row) => $row->classRoom->stage->id.'-'.$row->class_id)
                ->map(function ($rows) {
                    return [
                        'stage' => $rows->first()->classRoom->stage,
                        'class' => $rows->first()->classRoom,
                        'present' => $rows->where('status', 'present')->count(),
                        'absent' => $rows->where('status', 'absent')->count(),
                        'excused' => $rows->where('status', 'excused')->count(),
                    ];
                })
                ->sortBy(fn ($row) => $row['stage']->order.'-'.$row['class']->order)
                ->values();

            $takenBy = SupervisorAttendance::where('session_date', $today)->first()?->supervisor;

            return view('supervisor-attendance.create', [
                'term' => $term,
                'today' => $today,
                'alreadyTaken' => true,
                'summary' => $summary,
                'takenBy' => $takenBy,
                'stages' => collect(),
            ]);
        }

        $stages = Stage::with(['classRooms' => function ($q) {
            $q->orderBy('order')->orderBy('name')->with(['students' => function ($q2) {
                $q2->orderBy('serial_number')->orderBy('name');
            }]);
        }])->orderBy('order')->get();

        return view('supervisor-attendance.create', [
            'term' => $term,
            'today' => $today,
            'alreadyTaken' => false,
            'stages' => $stages,
        ]);
    }

    public function store(Request $request)
    {
        $term = Term::current();

        if (! $term) {
            return back()->withErrors(['general' => 'لا يوجد فصل دراسي مفعّل حاليًا.']);
        }

        $today = now()->toDateString();

        if (SupervisorAttendance::where('session_date', $today)->exists()) {
            return back()->withErrors(['general' => 'تم تسجيل الغياب بالفعل اليوم، ولا يمكن تسجيله مرة أخرى.']);
        }

        $request->validate([
            'status' => 'required|array',
            'status.*' => 'required|in:present,absent,excused',
            'class_id' => 'required|array',
            'reason' => 'array',
            'reason.*' => 'nullable|string|max:50',
        ]);

        // نتحقق من سبب كل غياب بعذر أولًا، قبل أن نبدأ في تسجيل أي شيء
        $reasons = [];
        foreach ($request->status as $studentId => $status) {
            $reason = trim($request->input("reason.$studentId", ''));

            if ($status === 'excused') {
                if ($reason === '') {
                    return back()->withErrors(['reason' => 'يجب كتابة سبب لكل غياب بعذر.'])->withInput();
                }

                if (mb_strlen($reason) > 50) {
                    return back()->withErrors(['reason' => 'سبب الغياب بعذر يجب ألا يتجاوز 50 حرفًا.'])->withInput();
                }

                $reasons[$studentId] = $reason;
            } else {
                $reasons[$studentId] = null;
            }
        }

        DB::transaction(function () use ($request, $term, $today, $reasons) {
            // نتأكد مرة أخرى داخل الـ transaction، احتياطًا من قيام شخص آخر بالإرسال في اللحظة نفسها بالضبط
            if (SupervisorAttendance::where('session_date', $today)->lockForUpdate()->exists()) {
                abort(422, 'تم تسجيل الغياب بالفعل اليوم.');
            }

            foreach ($request->status as $studentId => $status) {
                $reason = $reasons[$studentId];

                SupervisorAttendance::create([
                    'student_id' => $studentId,
                    'class_id' => $request->input("class_id.$studentId"),
                    'term_id' => $term->id,
                    'supervisor_id' => Auth::id(),
                    'session_date' => $today,
                    'academic_year' => Attendance::currentAcademicYear(),
                    'status' => $status,
                    'excuse_reason' => $reason,
                ]);
            }
        });

        return redirect('/supervisor-attendance')->with('success', 'تم تسجيل غياب المدرسة اليوم');
    }
}
