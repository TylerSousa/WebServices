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
    
    // Decoding JSON with the option to ignore invalid UTF-8 characters manually
    $data_arr = json_decode(utf8_encode($json_data), true);
    
    // Check for JSON decoding errors
    if (is_null($data_arr)) {
        die(json_last_error_msg()); // Handle JSON decoding error
    }

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

    // Echo token for debugging purposes
    echo $token; // Output the token to verify its format

    try {
        $key = 'segredo'; // Chave secreta para assinar o token
        $algorithm = 'HS256'; // Algoritmo de assinatura
    
        // Decodifica o token JWT
        $decoded = JWT::decode($token, new Key($key, 'HS256'));

        // Verifica se o tipo de usuário é restaurante
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
            // Remova a verificação de 'restaurante_id'
        ) {
            echo json_encode(["message" => "Campos obrigatórios ausentes"]);
            http_response_code(400);
            exit;
        }
        
        // Remova a linha que checa a existência de $data->restaurante_id
        
        // Insere os dados na tabela de pratos
        $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco, imagem, tipo, disponivel, restaurante_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssii", $data_arr['nome'], $data_arr['descricao'], $data_arr['preco'], $data_arr['imagem'], $data_arr['tipo'], $data_arr['disponivel'], $decoded->user_id); // Use $decoded->user_id como restaurante_id

        // Verifica se a execução foi bem-sucedida
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
