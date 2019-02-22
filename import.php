<?php
/*
 *
CREATE TABLE `DD_CANDIDATE` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `school_year` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `term` tinyint(4) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '姓名',
  `exam_num` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '准考证号',
  `exam_place` int(11) DEFAULT NULL COMMENT '考场',
  `exam_index` int(11) DEFAULT NULL COMMENT '考场顺序号',
  `exam_batch` int(11) DEFAULT NULL COMMENT '批次',
  `type` int(11) DEFAULT NULL COMMENT '类型（1.文科 2.理科）',
  `id_num` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '身份证号',
  `gender` tinyint(4) DEFAULT NULL COMMENT '性别（1.男 2.女）',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '照片地址',
  `birthday` date DEFAULT NULL,
  `height` double DEFAULT NULL COMMENT '身高',
  `weight` double DEFAULT NULL COMMENT '体重',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '插入时间',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考生表';

 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 读取上传文件
$file = $_FILES['file'];
switch ($file['error']) {
    case 1:
        $str = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。';
        break;
    case 2:
        $str = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。';
        break;
    case 3:
        $str = '文件只有部分被上传。';
        break;
    case 4:
        $str = '没有文件被上传。';
        break;
    case 6:
        $str = '找不到临时文件夹。';
        break;
    case 7:
        $str = '文件写入失败。';
        break;
}
if(isset($str))
{
    echo "<a href='import.html'>返回</a><br/>";
    exit($str);
}
$allowMaxSize = pow(1024, 2)*10;//10M
if($file['size'] > $allowMaxSize){
    exit('文件大小超过了准许的大小');
}
$allowSubFix = ['xls', 'xlsx'];

$info = pathinfo($file['name']);
$subFix = $info['extension'];
if (! in_array($subFix, $allowSubFix)) {
    exit('不准许的文件后缀');
}

$path = 'temp/';

if(!file_exists($path))
{
    mkdir($path);
}

$name = uniqid() . '.' . $subFix;
$inputFileName = $path.$name;
$inputFileType = ucfirst(strtolower($subFix));

if (! move_uploaded_file($file['tmp_name'], $inputFileName)) {
    echo "文件移动失败";
}

$servername = 'localhost';
$database = 'pfmis';
$username = 'root';
$password = '2222';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set character set utf8");
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMassage();
}

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
//use PhpOffice\PhpSpreadsheet\Helper\Sample;
//$helper = new Sample();
//
//$helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
$spreadsheet = IOFactory::load($inputFileName);
$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

if(empty($sheetData))
{
    exit('文件无数据');
}

$commonField = [
    'school_year'=>'2020-2021',
    'term'=>1,
];

$fieldMaps = [
    'name'=>            'ksxm',
    'exam_num'=>        'ksh',
    'id_num'=>          'sfzh',
    'exam_place'=>      'classroom',
    'exam_index'=>      'xh',
];

$title = array_shift($sheetData);

foreach($fieldMaps as $field=>$v)
{
    $key = array_search($v, $title);
    if($key)
    {
        $columnMaps[$key] = $field;
        $fieldMaps[$field] = $key;
    }
}

$insertData = [];

$sql = "INSERT INTO DD_CANDIDATE (school_year, term, name, exam_num, exam_place, exam_index, id_num) VALUES ";

for($i = 0 ; $i < count($sheetData); $i++)
{
    $rowData = $sheetData[$i];
    $row = $commonField;
    $sql .= "('" . $commonField['school_year'] . "', " . $commonField['term'] .", '" . $rowData[$fieldMaps['name']] . "',
    '" .$rowData[$fieldMaps['exam_num']] . "', '" .$rowData[$fieldMaps['exam_place']] . "',
    '" .$rowData[$fieldMaps['exam_index']] . "', '" .$rowData[$fieldMaps['id_num']] . "')";
    if($i < count($sheetData)-1)
    {
        $sql .= ",";
    }

    foreach($columnMaps as $key=>$field)
    {

        $row[$field] = $rowData[$key];
    }
    $insertData[] = $row;
}
//echo $sql;

if(!empty($sheetData))
{
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare($sql);
        $res = $stmt->execute();
        if ($res) {
            $conn->commit();
            echo "插入成功";
        } else {
            $conn->rollBack();
            echo "插入失败";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }

}

//$sheet = $spreadsheet->getActiveSheet();
//
//$content = $sheet->getRowIterator();
//$res_arr = array();
//foreach($content as $key => $items) {
//
//    $rows = $items->getRowIndex();              //行
//    $columns = $items->getCellIterator();       //列
//    $row_arr = array();
//    //确定从哪一行开始读取
//    if($rows < 2){
//        continue;
//    }
//    //逐列读取
//    foreach($columns as $head => $cell) {
//        //获取cell中数据
//        $data = $cell->getValue();
//        $row_arr[] = $data;
//    }
//    $res_arr[] = $row_arr;
//}
//
//var_dump($res_arr);

//var_dump($sheet);

//$reader = IOFactory::createReader($inputFileType);
//$worksheetData = $reader->listWorksheetInfo($inputFileName);
//
//var_dump($worksheetData);
unlink($inputFileName);

