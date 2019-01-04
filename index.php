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

// var_dump($users);
if (empty($users)) {
    exit("查无记录");
}

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
var_dump($users);

?>