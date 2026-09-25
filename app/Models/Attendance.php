<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id', 'assignment_id', 'schedule_id', 'session_date', 'academic_year', 'status',
        'excuse_reason', 'excused_counts_as_absence', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'excused_counts_as_absence' => 'boolean',
            'reviewed_at' => 'datetime',
            'session_date' => 'date',
        ];
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // هل يُحتسب هذا السجل "غيابًا" في النسب؟ الغياب العادي يُحتسب دائمًا،
    // أما الغياب بعذر فيُحتسب فقط إذا حدد الأدمن ذلك صراحةً
    public function countsAsAbsence(): bool
    {
        if ($this->status === 'absent') {
            return true;
        }

        if ($this->status === 'excused') {
            return (bool) $this->excused_counts_as_absence;
        }

        return false;
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // العام الدراسي بيبدأ من شهر سبتمبر
    public static function currentAcademicYear(): string
    {
        $now = now();
        $startYear = $now->month >= 9 ? $now->year : $now->year - 1;

        return $startYear . '/' . ($startYear + 1);
    }
}
