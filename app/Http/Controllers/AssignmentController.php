<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $currentTerm = Term::current();

        // يخص التوزيع الفصل الدراسي الحالي فقط، وليس جميع الفصول الدراسية السابقة
        $assignments = Assignment::where('term_id', $currentTerm?->id)
            ->with(['teacher', 'classRoom.stage', 'subject', 'schedules'])
            ->get();
        $teachers = User::teachersOnly()->orderBy('name')->get();
        $classes = ClassRoom::ordered()->with('stage')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('assignments.index', [
            'assignments' => $assignments,
            'teachers' => $teachers,
            'classes' => $classes,
            'subjects' => $subjects,
            'days' => Schedule::DAYS,
            'currentTerm' => $currentTerm,
        ]);
    }

    public function store(Request $request)
    {
        $currentTerm = Term::current();

        if (! $currentTerm) {
            return back()->withErrors(['term' => 'يجب تفعيل فصل دراسي أولًا من صفحة الأعوام الدراسية قبل إنشاء توزيع.']);
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        Assignment::create([
            'teacher_id' => $request->teacher_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'term_id' => $currentTerm->id,
        ]);

        return redirect('/assignments');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return redirect('/assignments');
    }
}
