<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'is_supervisor', 'is_educational_supervisor'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function supervisorAttendances()
    {
        return $this->hasMany(SupervisorAttendance::class, 'supervisor_id');
    }

    // المعلمون الفعليون فقط: ليسوا أدمن، وليسوا إداريين، وليسوا مشرفين تربويين
    public function scopeTeachersOnly($query)
    {
        return $query->where('is_admin', false)
            ->where('is_supervisor', false)
            ->where('is_educational_supervisor', false);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_supervisor' => 'boolean',
            'is_educational_supervisor' => 'boolean',
        ];
    }
}
