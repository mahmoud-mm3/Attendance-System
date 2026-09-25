<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'is_current'];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
        ];
    }

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }
}
