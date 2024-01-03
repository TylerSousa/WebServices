<?php
$hostname = 'localhost';
$username = 'root';
$password = '2Typernanegra2!';
$dbname = 'autenticador';
$dbport = 3306;

$conn = new mysqli($hostname, $username, $password, $dbname, $dbport);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>
