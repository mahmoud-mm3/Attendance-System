<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::with(['terms' => function ($query) {
            $query->orderBy('term_number');
        }])->orderByDesc('id')->get();

        return view('academic-years.index', ['academicYears' => $academicYears]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name',
        ]);

        AcademicYear::create(['name' => $request->name]);

        return redirect('/academic-years');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_current) {
            return back()->withErrors(['name' => 'لا يمكن حذف العام الدراسي الحالي وهو مفعّل. فعِّل عامًا آخر أولًا إذا أردت حذفه.']);
        }

        $academicYear->delete();

        return redirect('/academic-years');
    }

    public function storeTerm(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'term_number' => 'required|in:1,2',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // لا يجوز تكرار الفصل الدراسي نفسه (الأول أو الثاني) لنفس العام الدراسي
        $exists = $academicYear->terms()->where('term_number', $request->term_number)->exists();
        if ($exists) {
            return back()->withErrors(['term_number' => 'هذا الفصل الدراسي موجود بالفعل لنفس العام.']);
        }

        $academicYear->terms()->create([
            'term_number' => $request->term_number,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect('/academic-years');
    }

    public function destroyTerm(Term $term)
    {
        if ($term->is_current) {
            return back()->withErrors(['name' => 'لا يمكن حذف الفصل الدراسي الحالي وهو مفعّل. فعِّل فصلًا آخر أولًا إذا أردت حذفه.']);
        }

        $term->delete();

        return redirect('/academic-years');
    }

    // تفعيل فصل دراسي معين ليصبح هو الحالي؛ ستُبنى عليه جميع التوزيعات الجديدة والتقارير
    public function activateTerm(Term $term)
    {
        Term::where('is_current', true)->update(['is_current' => false]);
        AcademicYear::where('is_current', true)->update(['is_current' => false]);

        $term->update(['is_current' => true]);
        $term->academicYear()->update(['is_current' => true]);

        return redirect('/academic-years');
    }
}
