<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

include 'conexao.php';
require 'verificarToken.php'; // Inclua o arquivo verificarToken.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Defina o tipo de usuário aqui conforme a lógica do seu sistema
$tipo_usuario = 'restaurante'; 

if ($_SERVER["REQUEST_METHOD"] === "DELETE" && $tipo_usuario == 'restaurante') {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se o ID do prato a ser excluído foi enviado
    if (empty($data->prato_id)) {
        echo json_encode(["message" => "ID do prato não fornecido"]);
        http_response_code(400);
        exit;
    }

    $prato_id = $data->prato_id;

    // Exclui o prato
    $stmt_delete = $conn->prepare("DELETE FROM pratos WHERE id = ?");
    $stmt_delete->bind_param("i", $prato_id);
    $stmt_delete->execute();

    // Verifica se algum registro foi afetado pela exclusão
    if ($stmt_delete->affected_rows > 0) {
        echo json_encode(["message" => "Prato excluído com sucesso"]);
        http_response_code(200);
        exit;
    } else {
        echo json_encode(["message" => "Prato não encontrado"]);
        http_response_code(404);
        exit;
    }
}
?>
