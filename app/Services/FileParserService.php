<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class FileParserService
{
    public function parse($url, $content)
    {
        if (str_contains($url, '.pdf')) {
            return base64_encode($content);
        }

        if (str_contains($url, '.xlsx') || str_contains($url, '.xls')) {

            $temp = tempnam(sys_get_temp_dir(), 'excel');
            file_put_contents($temp, $content);

            $spreadsheet = IOFactory::load($temp);

            $text = '';

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                foreach ($sheet->toArray() as $row) {
                    $text .= implode(' | ', $row) . "\n";
                }
            }

            return $text;
        }

        return $content;
    }
}
