<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    // أسماء الأيام من الأحد إلى الخميس مرتبطة بقيمة dayOfWeek الخاصة بـ Carbon (0 = الأحد)
    public const DAYS = [
        0 => 'الأحد',
        1 => 'الإثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
    ];

    protected $fillable = ['assignment_id', 'day_of_week', 'period_number'];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function getDayNameAttribute()
    {
        return self::DAYS[$this->day_of_week] ?? '';
    }
}
