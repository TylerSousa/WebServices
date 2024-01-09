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
    $json_data = file_get_contents("php://input");
    
    $data_arr = json_decode(utf8_encode($json_data), true);
    
    if (is_null($data_arr)) {
        die(json_last_error_msg());
    }

    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    $token = trim($token);

    $token = str_replace("Bearer ", "", $token);

    if (!$token) {
        echo json_encode(["message" => "Token não fornecido"]);
        http_response_code(401);
        exit;
    }

    echo $token;

    try {
        $key = 'segredo';
        $algorithm = 'HS256';
    
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        if ($decoded->user_type !== 'restaurante') {
            echo json_encode(["message" => "Utilizador não tem permissão para adicionar pratos"]);
            http_response_code(403);
            exit;
        }

        if (
            empty($data_arr['nome'])
            || empty($data_arr['descricao'])
            || empty($data_arr['preco'])
            || empty($data_arr['imagem'])
            || empty($data_arr['tipo'])
            || empty($data_arr['disponivel'])
        ) {
            echo json_encode(["message" => "Campos obrigatórios ausentes"]);
            http_response_code(400);
            exit;
        }
            
        $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco, imagem, tipo, disponivel, restaurante_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssii", $data_arr['nome'], $data_arr['descricao'], $data_arr['preco'], $data_arr['imagem'], $data_arr['tipo'], $data_arr['disponivel'], $decoded->user_id); // Use $decoded->user_id como restaurante_id

        if ($stmt->execute()) {
            echo json_encode(["message" => "Prato adicionado com sucesso"]);
            http_response_code(200);
            exit;
        } else {
            echo json_encode(["message" => "Erro ao adicionar prato"]);
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
