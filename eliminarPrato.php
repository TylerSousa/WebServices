<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    // Obtém os dados enviados na requisição DELETE
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data);

    // Verifica se o token está presente nos headers
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    // Trim the token to remove whitespace or unexpected characters
    $token = trim($token);

    // Remove "Bearer " prefix if it's included
    $token = str_replace("Bearer ", "", $token);

    if (!$token) {
        echo json_encode(["message" => "Token não fornecido"]);
        http_response_code(401);
        exit;
    }

    try {
        $key = 'segredo'; // Chave secreta para assinar o token
        $algorithm = 'HS256'; // Algoritmo de assinatura
    
        // Decodifica o token JWT
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // Verifica se o tipo de usuário é restaurante
        if ($decoded->user_type !== 'restaurante') {
            echo json_encode(["message" => "Utilizador não tem permissão para excluir pratos"]);
            http_response_code(403);
            exit;
        }

        // Verifica se o ID do prato a ser excluído foi enviado
        if (empty($data->prato_id)) {
            echo json_encode(["message" => "ID do prato não fornecido"]);
            http_response_code(400);
            exit;
        }

        $prato_id = $data->prato_id;

        // Verifica se o restaurante associado ao prato é o mesmo do token
        $check_prato_stmt = $conn->prepare("SELECT restaurante_id FROM pratos WHERE id = ?");
        $check_prato_stmt->bind_param("i", $prato_id);
        $check_prato_stmt->execute();
        $check_prato_result = $check_prato_stmt->get_result();

        if ($check_prato_result->num_rows === 0) {
            echo json_encode(["message" => "Prato não encontrado"]);
            http_response_code(404);
            exit;
        }

        $row = $check_prato_result->fetch_assoc();
        $restaurante_id_do_prato = $row['restaurante_id'];

        if ($restaurante_id_do_prato != $decoded->user_id) {
            echo json_encode(["message" => "Não autorizado a excluir este prato"]);
            http_response_code(403);
            exit;
        }

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
    } catch (Exception $e) {
        echo json_encode(["message" => "Token inválido: " . $e->getMessage()]);
        http_response_code(401);
        exit;
    }
}
?>
