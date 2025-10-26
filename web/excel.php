<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
# This gives us the power of many!!
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
// Export an Excel file with all shapes and images

// Example usage
$excelFile = $argv[1];
if (!file_exists($excelFile)) {
    exit("\nFile not found.\n");
}
exportExcelToAsciiAndBinary($excelFile);


function exportExcelToAsciiAndBinary($excelFilePath, $exportDir = __DIR__ . '/export') {
    // Create export directory if it doesn't exist
    if (!file_exists($exportDir)) {
        mkdir($exportDir, 0777, true);
    }

    // Load the Excel file
    $spreadsheet = IOFactory::load($excelFilePath);

    // Loop through each sheet
    foreach ($spreadsheet->getSheetNames() as $sheetIndex => $sheetName) {
        $sheet = $spreadsheet->getSheet($sheetIndex);
        $sheetDir = $exportDir . '/' . $sheetName;

        // Create directory for the sheet
        if (!file_exists($sheetDir)) {
            mkdir($sheetDir, 0777, true);
        }

        // Open a file to write ASCII text data for this sheet
        $textFile = fopen($sheetDir . "/data.txt", 'w');

        // Get sheet data
        $rowData = $sheet->toArray(null, true, true, true);
        foreach ($rowData as $row) {
            // Write each row as a comma-separated line
            fputcsv($textFile, $row);
        }

        fclose($textFile);

        // Check for binary files such as images
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $imageFilePath = $sheetDir . '/' . $drawing->getName();

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
                // Save image files
                $imageContents = file_get_contents($drawing->getPath());
                file_put_contents($imageFilePath, $imageContents);
            }
        }

        // Detect shapes and extract any text they may contain
        detectShapesAndText($sheet, $sheetDir);
    }

    echo "Export completed: Files are saved in the $exportDir folder.\n";
}

function detectShapesAndText($sheet, $sheetDir) {
    // Initialize a file to store shapes' text content
    $shapeTextFile = fopen($sheetDir . '/shapes_text.txt', 'w');

    // Loop through shapes if available
    foreach ($sheet->getDrawingCollection() as $shape) {
        if ($shape instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Shape) {
            // Extract shape text
            $shapeText = $shape->getDescription();
            $shapeName = $shape->getName();

            if (!empty($shapeText)) {
                // Save shape text to file
                fwrite($shapeTextFile, "Shape Name: $shapeName\n");
                fwrite($shapeTextFile, "Shape Text: $shapeText\n\n");
            }
        }
    }

    fclose($shapeTextFile);
}
