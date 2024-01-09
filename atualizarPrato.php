<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"));

    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    $token = trim($token);

    $token = str_replace("Bearer ", "", $token);

    if (!$token) {
        echo json_encode(["message" => "Token não fornecido"]);
        http_response_code(401);
        exit;
    }

    try {
        $key = 'segredo';
        $algorithm = 'HS256';
    
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        if ($decoded->user_type !== 'restaurante') {
            echo json_encode(["message" => "Utilizador não tem permissão para atualizar pratos"]);
            http_response_code(403);
            exit;
        }

        if (
            empty($data->id)
            || empty($data->nome)
            || empty($data->descricao)
            || empty($data->preco)
            || empty($data->imagem)
            || empty($data->tipo)
            || empty($data->disponivel)
        ) {
            echo json_encode(["message" => "Campos obrigatórios ausentes"]);
            http_response_code(400);
            exit;
        }

        $check_prato_stmt = $conn->prepare("SELECT id, restaurante_id FROM pratos WHERE id = ?");
        $check_prato_stmt->bind_param("i", $data->id);
        $check_prato_stmt->execute();
        $check_prato_result = $check_prato_stmt->get_result();

        if ($check_prato_result->num_rows === 0) {
            echo json_encode(["message" => "Prato não encontrado"]);
            http_response_code(404);
            exit;
        }

        $prato_info = $check_prato_result->fetch_assoc();

        if ($prato_info['restaurante_id'] !== $decoded->user_id) {
            echo json_encode(["message" => "Prato não pertence ao seu restaurante"]);
            http_response_code(403);
            exit;
        }

        $update_stmt = $conn->prepare("UPDATE pratos SET nome = ?, descricao = ?, preco = ?, imagem = ?, tipo = ?, disponivel = ? WHERE id = ? AND restaurante_id = ?");
        $update_stmt->bind_param("ssdssiii", $data->nome, $data->descricao, $data->preco, $data->imagem, $data->tipo, $data->disponivel, $data->id, $decoded->user_id);

        if ($update_stmt->execute()) {
            echo json_encode(["message" => "Prato atualizado com sucesso"]);
            http_response_code(200);
            exit;
        } else {
            echo json_encode(["message" => "Erro ao atualizar prato"]);
            http_response_code(500);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(["message" => "Token inválido: " . $e->getMessage()]);
        http_response_code(401);
        exit;
    }
} else {
    echo json_encode(["message" => "Método não permitido"]);
    http_response_code(405);
    exit;
}
?>
