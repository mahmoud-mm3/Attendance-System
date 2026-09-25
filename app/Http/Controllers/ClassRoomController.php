<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Stage;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    public function index()
    {
        $stages = Stage::orderBy('order')->with(['classRooms' => function ($query) {
            $query->orderBy('order')->orderBy('name');
        }])->get();

        return view('classes.index', ['stages' => $stages]);
    }

    // إضافة عدة فصول مرة واحدة: كل صف يحتوي على مرحلة + اسم فصل + ترتيب، ويتم تجاهل أي صف فارغ
    public function store(Request $request)
    {
        $request->validate([
            'stage_id.*' => 'nullable|exists:stages,id',
            'order.*' => 'nullable|integer|min:0|max:255',
        ]);

        $names = $request->input('name', []);
        $stageIds = $request->input('stage_id', []);
        $orders = $request->input('order', []);

        $rows = [];
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $stageId = $stageIds[$i] ?? null;

            if ($name === '' || ! $stageId) {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'stage_id' => $stageId,
                'order' => $orders[$i] !== '' && isset($orders[$i]) ? (int) $orders[$i] : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return back()->withErrors(['name' => 'يجب إدخال اسم الفصل والصف على الأقل لسطر واحد.'])->withInput();
        }

        ClassRoom::insert($rows);

        return redirect('/classes')->with('success', 'تم إضافة '.count($rows).' فصل بنجاح');
    }

    public function show(ClassRoom $classRoom)
    {
        $classRoom->load('stage');
        $students = $classRoom->students()->orderBy('serial_number')->orderBy('name')->get();

        return view('classes.show', [
            'classRoom' => $classRoom,
            'students' => $students,
        ]);
    }

    public function destroy(ClassRoom $classRoom)
    {
        if ($classRoom->students()->exists()) {
            return back()->withErrors(['name' => 'لا يمكن حذف هذا الفصل لوجود طلاب مسجلين فيه. انقل الطلاب إلى فصل آخر أولًا.']);
        }

        if ($classRoom->assignments()->exists()) {
            return back()->withErrors(['name' => 'لا يمكن حذف هذا الفصل لوجود توزيع معلمين مرتبط به. احذف التوزيع أولًا من صفحة "التوزيع".']);
        }

        $classRoom->delete();

        return redirect('/classes');
    }
}
