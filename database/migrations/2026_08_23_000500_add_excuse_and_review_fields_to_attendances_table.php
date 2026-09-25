<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // غياب بعذر: السبب الذي كتبه المعلم
            $table->string('excuse_reason')->nullable()->after('status');
            // هل يُحتسب هذا العذر غيابًا في النسبة أم لا؟ الأدمن هو من يقرر، لذا تبقى القيمة null إلى أن يقرر
            $table->boolean('excused_counts_as_absence')->nullable()->after('excuse_reason');
            // مراجعة/اعتماد الأدمن على السجل بعد أن يحفظه المعلم
            $table->foreignId('reviewed_by')->nullable()->after('excused_counts_as_absence')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['excuse_reason', 'excused_counts_as_absence', 'reviewed_at']);
        });
    }
};
