<?php
/**
 * Excel Template Generator for Gym Members Import
 *
 * Run this script once to generate the EXCEL_TEMPLATE_EXAMPLE.xlsx file
 * Command: php Modules/Software/Imports/generate_excel_template.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('members');

// Define headers
$headers = [
    'member_code',
    'name',
    'phone',
    'email',
    'gender',
    'dob',
    'national_id',
    'address',
    'sale_channel',
    'fp_id',
    'fp_uid',
    'subscription_code',
    'joining_date',
    'expire_date',
    'workouts',
    'visits',
    'amount_paid',
    'amount_remaining',
    'vat_percentage',
    'discount_value',
    'discount_type',
    'payment_type',
    'status'
];

// Set headers in row 1
$sheet->fromArray($headers, NULL, 'A1');

// Style the header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4472C4']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

$sheet->getStyle('A1:W1')->applyFromArray($headerStyle);

// Set column widths
$columnWidths = [
    'A' => 15,  // member_code
    'B' => 20,  // name
    'C' => 15,  // phone
    'D' => 25,  // email
    'E' => 10,  // gender
    'F' => 12,  // dob
    'G' => 15,  // national_id
    'H' => 30,  // address
    'I' => 15,  // sale_channel
    'J' => 12,  // fp_id
    'K' => 12,  // fp_uid
    'L' => 18,  // subscription_code
    'M' => 15,  // joining_date
    'N' => 15,  // expire_date
    'O' => 10,  // workouts
    'P' => 10,  // visits
    'Q' => 15,  // amount_paid
    'R' => 18,  // amount_remaining
    'S' => 15,  // vat_percentage
    'T' => 15,  // discount_value
    'U' => 15,  // discount_type
    'V' => 15,  // payment_type
    'W' => 12   // status
];

foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

// Sample data rows
$sampleData = [
    ['', 'Ahmad Al-Mansour', '0501234567', 'ahmad@example.com', 'male', '1990-05-15', '1234567890', 'Riyadh - King Fahd District', 'Website', '', '', 'GOLD-2024', '2024-01-01', '2024-12-31', 0, 0, 3000, 0, 15, 100, 'fixed', 'cash', 'active'],
    ['', 'Fatima Al-Zahrani', '0507654321', 'fatima@example.com', 'female', '1985-06-20', '0987654321', 'Jeddah - Al-Hamra', 'Direct', 'FP001', 'UID001', 'SILVER-2024', '2024-01-15', '2024-07-15', 12, 5, 1500, 500, 15, 50, 'percentage', 'card', 'active'],
    ['', 'Mohammed Ibrahim', '0503456789', 'mohammed@example.com', 'male', '1988-03-10', '1122334455', 'Dammam - Corniche', 'Phone', '', '', 'PLATINUM-2024', '2024-02-01', '2025-02-01', 0, 0, 5000, 0, 15, 0, 'fixed', 'bank', 'active'],
    ['', 'Sarah Al-Qahtani', '0509876543', 'sarah@example.com', 'female', '1995-11-25', '5544332211', 'Riyadh - Olaya', 'Website', 'FP002', 'UID002', 'BASIC-2024', '2024-01-10', '2024-04-10', 30, 12, 500, 200, 15, 20, 'fixed', 'online', 'active'],
    ['', 'Khalid Hassan', '0502223344', 'khalid@example.com', 'male', '1992-08-30', '6677889900', 'Jeddah - Al-Andalus', 'Direct', '', '', 'GOLD-2024', '2024-03-01', '2025-03-01', 0, 0, 3200, 0, 15, 200, 'percentage', 'cash', 'active'],
    ['', 'Noura Abdullah', '0508887766', 'noura@example.com', 'female', '1987-12-05', '9988776655', 'Riyadh - Diplomatic Quarter', 'Phone', 'FP003', 'UID003', 'VIP-2024', '2024-01-05', '2025-01-05', 0, 0, 8000, 2000, 15, 500, 'fixed', 'card', 'active'],
    ['', 'Faisal Al-Rashid', '0505554444', 'faisal@example.com', 'male', '1993-07-18', '1231231234', 'Dammam - Al-Faisaliyyah', 'Website', '', '', 'SILVER-2024', '2024-02-15', '2024-08-15', 12, 8, 1600, 400, 15, 100, 'percentage', 'bank', 'active'],
    ['', 'Huda Al-Mutairi', '0506665555', 'huda@example.com', 'female', '1991-04-22', '4564564567', 'Jeddah - Al-Zahra', 'Direct', 'FP004', 'UID004', 'FAMILY-2024', '2024-01-20', '2024-07-20', 24, 0, 2500, 500, 15, 150, 'fixed', 'online', 'active'],
    ['', 'Abdullah Al-Otaibi', '0501112233', 'abdullah@example.com', 'male', '1989-09-14', '7897897890', 'Riyadh - Al-Malaz', 'Phone', '', '', 'PLATINUM-2024', '2024-03-10', '2025-03-10', 0, 0, 4800, 200, 15, 0, 'fixed', 'cash', 'active'],
    ['', 'Maha Al-Anazi', '0507778899', 'maha@example.com', 'female', '1994-02-28', '3213213210', 'Dammam - Al-Shati', 'Website', 'FP005', 'UID005', 'BASIC-2024', '2024-02-01', '2024-05-01', 30, 15, 450, 50, 15, 10, 'percentage', 'card', 'active'],
];

// Add sample data starting from row 2
$sheet->fromArray($sampleData, NULL, 'A2');

// Style data rows
$dataStyle = [
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D3D3D3']
        ]
    ]
];

$lastRow = count($sampleData) + 1;
$sheet->getStyle('A2:W' . $lastRow)->applyFromArray($dataStyle);

// Alternate row colors
for ($row = 2; $row <= $lastRow; $row++) {
    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':W' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }
}

// Set row height
$sheet->getDefaultRowDimension()->setRowHeight(20);
$sheet->getRowDimension(1)->setRowHeight(25);

// Freeze first row
$sheet->freezePane('A2');

// Save the file
$writer = new Xlsx($spreadsheet);
$outputPath = __DIR__ . '/EXCEL_TEMPLATE_EXAMPLE.xlsx';
$writer->save($outputPath);

echo "✅ Excel template generated successfully!\n";
echo "📁 File location: {$outputPath}\n";
echo "📊 Template includes:\n";
echo "   - 23 columns with proper headers\n";
echo "   - 10 sample data rows\n";
echo "   - Professional formatting\n";
echo "   - Frozen header row\n";
echo "   - Proper column widths\n";
echo "\n";
echo "🎯 Next steps:\n";
echo "   1. Update the download link in upload_excel.blade.php\n";
echo "   2. Change .csv to .xlsx in the asset path\n";
echo "   3. Test the download\n";
