<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$headers = getallheaders();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verificar o token JWT apenas para requisições POST
    if (!$token) {
        echo json_encode(["message" => "Token não fornecido"]);
        http_response_code(401);
        exit;
    }

    try {
        $decoded = JWT::decode($token, 'seu_segredo', array('HS256'));
        // Verificar o tipo de usuário a partir do payload do token
        if ($decoded->user_type === "restaurante") {
            // O usuário é um restaurante, permitir a exclusão do prato
            // Execute a lógica para exclusão do prato aqui...
            echo json_encode(["message" => "Prato excluído com sucesso"]);
            http_response_code(200);
            exit;
        } else {
            // Se o usuário não for um restaurante, retornar uma mensagem de permissão negada
            echo json_encode(["message" => "Você não tem permissão para excluir pratos"]);
            http_response_code(403); // Código 403 - Forbidden (Proibido)
            exit;
        }
    } catch (Exception $e) {
        // Lidar com erros de token inválido
        echo json_encode(["message" => "Token inválido"]);
        http_response_code(401);
        exit;
    }
} else {
    // Se não for uma requisição POST, permitir a continuação do código
    echo json_encode(["message" => "Apenas requisições POST são permitidas"]);
    http_response_code(405);
    exit;
}
?>
