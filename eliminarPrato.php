<?php
require 'vendor/autoload.php'; // Caminho para o autoload do Composer
use \Firebase\JWT\JWT;
include 'conexao.php'; // Arquivo de conexão com o banco de dados
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$token = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTHORIZATION'])) : null;

if (!$token) {
    echo json_encode(["message" => "Token não fornecido"]);
    http_response_code(401);
    exit;
}

try {
    $decoded = JWT::decode($token, 'seu_segredo', array('HS256'));
    $tipo_usuario = $decoded->user_type;

    if ($_SERVER["REQUEST_METHOD"] === "DELETE" && $tipo_usuario === 'restaurante') {
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
    } else {
        echo json_encode(["message" => "Ação não permitida para este tipo de usuário"]);
        http_response_code(403); // Código 403 - Ação proibida
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["message" => "Token inválido"]);
    http_response_code(401);
    exit;
}
?>
