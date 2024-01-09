<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

        if ($decoded->user_type !== 'cliente') {
            echo json_encode(["message" => "Utilizador não tem permissão para efetuar reservas"]);
            http_response_code(403);
            exit;
        }

        if (
            empty($data->prato_id)
            || empty($data->quantidade)
            || empty($data->data_reserva)
        ) {
            echo json_encode(["message" => "Campos obrigatórios ausentes"]);
            http_response_code(400);
            exit;
        }

        $check_prato_stmt = $conn->prepare("SELECT disponivel FROM pratos WHERE id = ?");
        $check_prato_stmt->bind_param("i", $data->prato_id);
        $check_prato_stmt->execute();
        $check_prato_result = $check_prato_stmt->get_result();

        if ($check_prato_result->num_rows === 0) {
            echo json_encode(["message" => "Prato com ID {$data->prato_id} não encontrado"]);
            http_response_code(404);
            exit;
        }

        $row = $check_prato_result->fetch_assoc();
        $disponivel = $row['disponivel'];

        if ($data->quantidade > $disponivel) {
            echo json_encode(["message" => "Quantidade solicitada maior do que a disponível"]);
            http_response_code(400);
            exit;
        }

        $insert_stmt = $conn->prepare("INSERT INTO reservas (prato_id, quantidade, data_reserva, cliente_id) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("iisi", $data->prato_id, $data->quantidade, $data->data_reserva, $decoded->user_id);

        if ($insert_stmt->execute()) {
            $update_stmt = $conn->prepare("UPDATE pratos SET disponivel = disponivel - ? WHERE id = ?");
            $update_stmt->bind_param("ii", $data->quantidade, $data->prato_id);
            $update_stmt->execute();
        
            echo json_encode(["message" => "Reserva efetuada com sucesso"]);
            http_response_code(200);
            exit;
        } else {
            echo json_encode(["message" => "Erro ao efetuar reserva"]);
            http_response_code(500);
            exit;
        }
        
    } catch (Exception $e) {
        echo json_encode(["message" => "Token inválido: " . $e->getMessage()]);
        http_response_code(401);
        exit;
    }
}
?>
