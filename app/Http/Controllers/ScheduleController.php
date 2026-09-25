<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // إضافة عدة مواعيد للحصة الواحدة مرة واحدة، ويتم تجاهل أي موعد مكرر موجود بالفعل
    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'day_of_week.*' => 'nullable|integer|between:0,4',
            'period_number.*' => 'nullable|integer|min:1|max:10',
        ]);

        $days = $request->input('day_of_week', []);
        $periods = $request->input('period_number', []);

        $created = 0;
        foreach ($days as $i => $day) {
            $period = $periods[$i] ?? null;

            if ($day === '' || $day === null || $period === '' || $period === null) {
                continue;
            }

            $schedule = Schedule::firstOrCreate([
                'assignment_id' => $assignment->id,
                'day_of_week' => $day,
                'period_number' => $period,
            ]);

            if ($schedule->wasRecentlyCreated) {
                $created++;
            }
        }

        return redirect('/assignments')->with(
            'success',
            $created > 0 ? "تم إضافة $created ميعاد بنجاح" : 'هذه المواعيد موجودة بالفعل'
        );
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect('/assignments');
    }
}
