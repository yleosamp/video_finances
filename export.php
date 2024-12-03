<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

$conn = new mysqli('localhost', 'root', '', 'videofinances');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão']));
}

$month = intval($_POST['month']);
$year = intval($_POST['year']);
$user_id = $_SESSION['user_id'];

// Criar nova planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar cabeçalhos
$sheet->setCellValue('A1', 'Nome do Vídeo');
$sheet->setCellValue('B1', 'Preço');
$sheet->setCellValue('C1', 'Moeda');
$sheet->setCellValue('D1', 'Nº de Pessoas');
$sheet->setCellValue('E1', 'Status');
$sheet->setCellValue('F1', 'Notas');

// Estilizar cabeçalhos
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4CAF50']
    ],
    'font' => ['color' => ['rgb' => 'FFFFFF']]
];
$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

// Buscar dados
$sql = "SELECT * FROM videos WHERE user_id = $user_id AND month = $month AND year = $year ORDER BY created_at DESC";
$result = $conn->query($sql);

$row = 2;
$totalBRL = 0;
$totalUSD = 0;
$exchangeRate = 5; // Taxa padrão de câmbio

// Tentar obter taxa de câmbio atual
try {
    $exchange = file_get_contents('https://economia.awesomeapi.com.br/last/USD-BRL');
    $exchange = json_decode($exchange, true);
    if (isset($exchange['USDBRL']['bid'])) {
        $exchangeRate = floatval($exchange['USDBRL']['bid']);
    }
} catch (Exception $e) {
    // Mantém a taxa padrão em caso de erro
}

while ($video = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $video['name']);
    $sheet->setCellValue('B' . $row, $video['price']);
    $sheet->setCellValue('C' . $row, $video['currency']);
    $sheet->setCellValue('D' . $row, $video['people_count']);
    $sheet->setCellValue('E' . $row, $video['is_paid'] ? 'Não Pago' : 'Pago');
    $sheet->setCellValue('F' . $row, $video['notes']);
    
    // Calcular totais
    if ($video['currency'] === 'USD') {
        $totalUSD += floatval($video['price']);
        $totalBRL += floatval($video['price']) * $exchangeRate;
    } else {
        $totalBRL += floatval($video['price']);
        $totalUSD += floatval($video['price']) / $exchangeRate;
    }
    
    // Colorir linha baseado no status de pagamento
    $rowStyle = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => $video['is_paid'] ? 'FF696C' : '9FFEBB']
        ]
    ];
    $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray($rowStyle);
    
    $row++;
}

// Adicionar linha de total
$totalRow = $row;
$sheet->setCellValue('A' . $totalRow, 'TOTAL');
$sheet->setCellValue('B' . $totalRow, number_format($totalBRL, 2));
$sheet->setCellValue('C' . $totalRow, 'BRL');
$sheet->setCellValue('D' . $totalRow, '');
$sheet->setCellValue('E' . $totalRow, '');
$sheet->setCellValue('F' . $totalRow, '');

// Adicionar linha de total em USD
$usdRow = $row + 1;
$sheet->setCellValue('A' . $usdRow, 'TOTAL');
$sheet->setCellValue('B' . $usdRow, number_format($totalUSD, 2));
$sheet->setCellValue('C' . $usdRow, 'USD');
$sheet->setCellValue('D' . $usdRow, '');
$sheet->setCellValue('E' . $usdRow, '');
$sheet->setCellValue('F' . $usdRow, '');

// Estilizar linhas de total
$totalStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'B974ED']
    ]
];
$sheet->getStyle('A'.$totalRow.':F'.$totalRow)->applyFromArray($totalStyle);
$sheet->getStyle('A'.$usdRow.':F'.$usdRow)->applyFromArray($totalStyle);

// Ajustar largura das colunas
foreach(range('A','F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Configurar cabeçalhos HTTP
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="videos_' . $month . '_' . $year . '.xlsx"');
header('Cache-Control: max-age=0');

// Criar arquivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
