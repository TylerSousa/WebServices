<?php
include 'conexao.php'; // Arquivo de conexão com o banco de dados
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");


$sql = "SELECT idusuarios, nome, email, password FROM usuarios";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $usuarios = array();

    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($usuarios);
} else {
    echo "0 resultados";
}

$conn->close();
?>
