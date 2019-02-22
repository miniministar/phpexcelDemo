<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = 'localhost';
$database = 'nxdb';
$username = 'root';
$password = '2222';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set character set utf8");
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMassage();
}

$stmt = $conn->prepare("select * from nc_user");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    exit("查无记录");
}

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadSheet = new Spreadsheet();
$sheet = $spreadSheet->getActiveSheet();
$sheet->setTitle("明细");

$sheet->mergeCells('A1:C1');

$sheet->setCellValue('A1', '用户情况一览表');

$sheet->setCellValue('A2', '序号');
$sheet->setCellValue('B2', '用户名');
$sheet->setCellValue('C2', '名字');


$i = 3;
foreach ($users as $key => $value) {
    $sheet->setCellValue('A' . ($key+$i), $key+1);
    $sheet->setCellValue('B' . ($key+$i), $value['USERNAME']);
    $sheet->setCellValue('C' . ($key+$i), $value['NAME']);
}

$writer = new Xlsx($spreadSheet);
$fileName = '用户情况一览表.xlsx';
$writer->save($fileName);
$fileName = iconv('utf-8', 'gb2312', $fileName);


ob_end_clean();
header('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Last-Modified: ' . gmdate("D,d M YH:i:s") . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
readfile($fileName);
unlink($fileName);
?>
