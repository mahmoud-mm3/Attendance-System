<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // هذا التعديل لا يغيّر بنية الجداول، فقط يوحّد قيم عمود "level" (المرحلة)
    // مع القائمة الرسمية، وينظّف أسماء الفصول (الشعب) من تكرار اسم الصف بداخلها،
    // بحيث يصبح اسم الفصل هو الشعبة فقط (مثال: "أ")، والصف/المرحلة يظهران من علاقتهما لا من النص.
    public function up(): void
    {
        // توحيد قيم المرحلة القديمة ("اعدادي"/"إعدادي") إلى القيمة الرسمية "متوسط"
        DB::table('stages')->whereIn('level', ['اعدادي', 'إعدادي'])->update(['level' => 'متوسط']);

        // تنظيف اسم كل فصل: لو الاسم يحتوي على جزء بين قوسين (الشعبة الفعلية)
        // زي "الأول الابتدائي ( أ )"، نستخرج منه "أ" فقط ونتجاهل تكرار اسم الصف.
        $classes = DB::table('classes')->select('id', 'name')->get();

        foreach ($classes as $class) {
            if (preg_match('/\(\s*(.+?)\s*\)\s*$/u', (string) $class->name, $m)) {
                $section = trim($m[1]);

                if ($section !== '' && $section !== $class->name) {
                    DB::table('classes')->where('id', $class->id)->update(['name' => $section]);
                }
            }
        }
    }

    public function down(): void
    {
        // تنظيف الأسماء وتوحيد المرحلة عمليتان غير قابلتين للعكس بدقة (فقدان النص الأصلي)،
        // فلا يوجد down فعلي هنا.
    }
};
