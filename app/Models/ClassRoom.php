<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name', 'stage_id', 'order'];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    // ترتيب الفصول: حسب ترتيب المرحلة، بعدين ترتيب الشعبة، بعدين الاسم
    public function scopeOrdered($query)
    {
        return $query->join('stages', 'classes.stage_id', '=', 'stages.id')
            ->orderBy('stages.order')
            ->orderBy('classes.order')
            ->orderBy('classes.name')
            ->select('classes.*');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    // إعادة ترقيم الطلاب تلقائيًا حسب رقم الهوية داخل الفصل، بدلًا من الترقيم اليدوي
    public function resequenceStudents(): void
    {
        $students = $this->students()
            ->orderByRaw('national_id IS NULL, national_id')
            ->orderBy('name')
            ->get(['id']);

        foreach ($students as $i => $student) {
            Student::where('id', $student->id)->update(['serial_number' => $i + 1]);
        }
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }
}
