<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // فهرس مركب يسرّع فلترة التقارير حسب الحالة والتاريخ مع بعض
            $table->index(['status', 'session_date']);
            $table->index('session_date');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['status', 'session_date']);
            $table->dropIndex(['session_date']);
        });
    }
};
