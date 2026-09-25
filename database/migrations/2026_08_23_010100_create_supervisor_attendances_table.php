<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // غياب المشرف التربوي منفصل تمامًا عن غياب المعلمين (مفيهوش حصة/مادة، مجرد غياب طالب في يوم معين)
    // لذلك هو جدول مستقل بدلًا من ربطه بجدول attendances المبني على assignment
    public function up(): void
    {
        Schema::create('supervisor_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->date('session_date');
            $table->string('academic_year');
            $table->enum('status', ['present', 'absent', 'excused']);
            $table->string('excuse_reason')->nullable();
            $table->boolean('excused_counts_as_absence')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_attendances');
    }
};
