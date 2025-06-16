<?php
var_dump($_POST);

$data = [
    "status"=> $_POST["status"],
];

$connection = new PDO("mysql:host=localhost;dbname=demka_db", "root", password: '');

$sql = 'UPDATE statements SET status=:status WHERE 1';
$statment = $connection->prepare($sql);
$result = $statment->execute($data);
var_dump($result);

?>
