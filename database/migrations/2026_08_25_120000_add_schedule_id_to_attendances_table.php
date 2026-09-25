<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // قبل ذلك كان الغياب مرتبطًا بالتوزيع (معلم+فصل+مادة) فقط، فإذا كان التوزيع نفسه
    // له أكثر من حصة في اليوم (حصة 3 وحصة 5 مثلًا) كان تسجيل الحصة الثانية
    // يطغى على تسجيل الأولى. أصبح الآن لكل حصة (schedule) غياب منفصل تمامًا.
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('assignment_id')->constrained()->nullOnDelete();
            $table->index('schedule_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
        });
    }
};
