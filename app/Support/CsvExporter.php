<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    /**
     * @param  string  $filename
     * @param  array<int, string>  $headers  عناوين الأعمدة
     * @param  iterable<array<int, string|int|null>>  $rows  كل صف عبارة عن array بنفس ترتيب العناوين
     */
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');

            // BOM حتى يفتح إكسل النص العربي بشكل صحيح دون ظهور حروف مشوهة
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
