<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorAttendance extends Model
{
    protected $fillable = [
        'student_id', 'class_id', 'term_id', 'supervisor_id', 'session_date', 'academic_year',
        'status', 'excuse_reason', 'excused_counts_as_absence', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'excused_counts_as_absence' => 'boolean',
            'reviewed_at' => 'datetime',
            'session_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // نفس منطق Attendance::countsAsAbsence بالضبط
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
}
