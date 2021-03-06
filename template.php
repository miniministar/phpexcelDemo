<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = 'localhost';
$username = 'root';
$password = '2222';

try {
    $conn = new PDO("mysql:host=$servername;dbname=nxdb_ty", $username, $password);
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

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Helper\Sample;

$reader = IOFactory::createReader('Xls');
$spreadSheet = $reader->load(__DIR__ . '/files/templates/30template.xls');

$sheet = $spreadSheet->getActiveSheet();

$i = 3;
foreach ($users as $key => $value) {
    $sheet->setCellValue('A' . ($key+$i), $key+1);
    $sheet->setCellValue('B' . ($key+$i), $value['USERNAME']);
    $sheet->setCellValue('C' . ($key+$i), $value['NAME']);
}


$writer = new Xlsx($spreadSheet);

$pathName = 'temp/' . time() . '.xlsx';
$fileName = '用户情况一览表.xlsx';
$writer->save($pathName);
$fileName = iconv('utf-8', 'gb2312', $fileName);

//$fileName = '用户情况一览表.xlsx';
//$helper = new Sample();
//$helper->write($spreadSheet, $fileName, ['Xlsx']);
//
//$fileName = iconv('utf-8', 'gb2312', $fileName);
//
//
ob_end_clean();
header('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Last-Modified: ' . gmdate("D,d M YH:i:s") . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
readfile($pathName);
unlink($pathName);
?>
