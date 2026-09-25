<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassRoom;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $students = Student::with('classRoom.stage')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->join('stages', 'classes.stage_id', '=', 'stages.id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('students.name', 'like', "%$search%")
                        ->orWhere('students.national_id', 'like', "%$search%");
                });
            })
            ->orderBy('stages.order')
            ->orderBy('classes.order')
            ->orderByRaw('students.national_id IS NULL, students.national_id')
            ->select('students.*')
            ->paginate(20)
            ->withQueryString();

        $classes = ClassRoom::ordered()->with('stage')->get();

        return view('students.index', [
            'students' => $students,
            'classes' => $classes,
            'search' => $search,
        ]);
    }

    // إضافة عدة طلاب مرة واحدة: كل صف يحتوي على اسم + رقم هوية + فصل، ويتم تجاهل أي صف فارغ
    // أصبح الرقم التسلسلي تلقائيًا بالكامل حسب ترتيب رقم الهوية داخل كل فصل
    public function store(Request $request)
    {
        $request->validate([
            'class_id.*' => 'nullable|exists:classes,id',
        ]);

        $names = $request->input('name', []);
        $nationalIds = $request->input('national_id', []);
        $classIds = $request->input('class_id', []);

        $rows = [];
        $affectedClassIds = [];
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $classId = $classIds[$i] ?? null;

            if ($name === '' || ! $classId) {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'national_id' => $nationalIds[$i] ?: null,
                'class_id' => $classId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $affectedClassIds[$classId] = true;
        }

        if (empty($rows)) {
            return back()->withErrors(['name' => 'يجب إدخال اسم الطالب والفصل على الأقل لسطر واحد.'])->withInput();
        }

        Student::insert($rows);

        foreach (array_keys($affectedClassIds) as $classId) {
            ClassRoom::find($classId)?->resequenceStudents();
        }

        return redirect('/students')->with('success', 'تم إضافة '.count($rows).' طالب بنجاح، وترقيمهم تلقائيًا حسب رقم الهوية');
    }

    public function destroy(Student $student)
    {
        $classId = $student->class_id;
        $student->delete();

        ClassRoom::find($classId)?->resequenceStudents();

        return redirect('/students');
    }
}
