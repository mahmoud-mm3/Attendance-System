<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();

        return view('subjects.index', ['subjects' => $subjects]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Subject::create([
            'name' => $request->name,
        ]);

        return redirect('/subjects');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->assignments()->exists()) {
            return back()->withErrors(['name' => 'لا يمكن حذف هذه المادة لأنها مستخدمة حاليًا في توزيع معلمين. احذف التوزيع أولًا من صفحة "التوزيع".']);
        }

        $subject->delete();

        return redirect('/subjects');
    }
}
