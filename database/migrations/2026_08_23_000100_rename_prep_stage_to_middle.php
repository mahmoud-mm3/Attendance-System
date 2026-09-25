<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stages')->where('level', 'اعدادي')->update(['level' => 'متوسط']);

        DB::table('stages')->where('name', 'الأول الإعدادي')->update(['name' => 'الأول المتوسط']);
        DB::table('stages')->where('name', 'الثاني الإعدادي')->update(['name' => 'الثاني المتوسط']);
        DB::table('stages')->where('name', 'الثالث الإعدادي')->update(['name' => 'الثالث المتوسط']);
    }

    public function down(): void
    {
        DB::table('stages')->where('level', 'متوسط')->update(['level' => 'اعدادي']);

        DB::table('stages')->where('name', 'الأول المتوسط')->update(['name' => 'الأول الإعدادي']);
        DB::table('stages')->where('name', 'الثاني المتوسط')->update(['name' => 'الثاني الإعدادي']);
        DB::table('stages')->where('name', 'الثالث المتوسط')->update(['name' => 'الثالث الإعدادي']);
    }
};
