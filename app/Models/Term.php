<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = ['academic_year_id', 'term_number', 'start_date', 'end_date', 'is_current'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function getNameAttribute(): string
    {
        return $this->term_number == 1 ? 'الفصل الدراسي الأول' : 'الفصل الدراسي الثاني';
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }

    // هل يقع هذا التاريخ ضمن فترة الفصل الدراسي، وهو يوم دراسة (من الأحد إلى الخميس)؟
    public function isSchoolDay(\DateTimeInterface $date): bool
    {
        $carbon = \Illuminate\Support\Carbon::instance(\Illuminate\Support\Carbon::parse($date));

        return $carbon->betweenIncluded($this->start_date, $this->end_date)
            && $carbon->dayOfWeek <= 4; // 0=الأحد ... 4=الخميس
    }
}
