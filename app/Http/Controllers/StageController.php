<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public const LEVELS = ['ابتدائي', 'متوسط', 'ثانوي'];

    public function index()
    {
        $stages = Stage::orderBy('order')->withCount('classRooms')->get()->groupBy('level');

        return view('stages.index', [
            'stagesByLevel' => $stages,
            'levels' => self::LEVELS,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:'.implode(',', self::LEVELS),
            'order' => 'nullable|integer|min:0|max:255',
        ]);

        Stage::create([
            'name' => $request->name,
            'level' => $request->level,
            'order' => $request->order ?? 0,
        ]);

        return redirect('/stages');
    }

    public function destroy(Stage $stage)
    {
        if ($stage->classRooms()->exists()) {
            return back()->withErrors(['name' => 'لا يمكن حذف هذه المرحلة لوجود فصول مسجلة فيها. احذف الفصول أولًا.']);
        }

        $stage->delete();

        return redirect('/stages');
    }
}
