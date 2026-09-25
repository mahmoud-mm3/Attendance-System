<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'الأول الابتدائي', 'level' => 'ابتدائي', 'order' => 1],
            ['name' => 'الثاني الابتدائي', 'level' => 'ابتدائي', 'order' => 2],
            ['name' => 'الثالث الابتدائي', 'level' => 'ابتدائي', 'order' => 3],
            ['name' => 'الرابع الابتدائي', 'level' => 'ابتدائي', 'order' => 4],
            ['name' => 'الخامس الابتدائي', 'level' => 'ابتدائي', 'order' => 5],
            ['name' => 'السادس الابتدائي', 'level' => 'ابتدائي', 'order' => 6],
            ['name' => 'الأول المتوسط', 'level' => 'متوسط', 'order' => 7],
            ['name' => 'الثاني المتوسط', 'level' => 'متوسط', 'order' => 8],
            ['name' => 'الثالث المتوسط', 'level' => 'متوسط', 'order' => 9],
            ['name' => 'الأول الثانوي', 'level' => 'ثانوي', 'order' => 10],
            ['name' => 'الثاني الثانوي', 'level' => 'ثانوي', 'order' => 11],
            ['name' => 'الثالث الثانوي', 'level' => 'ثانوي', 'order' => 12],
        ];

        foreach ($stages as $stage) {
            Stage::firstOrCreate(['name' => $stage['name']], $stage);
        }
    }
}
