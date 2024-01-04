<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se o token está presente nos headers
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$token) {
        echo json_encode(["message" => "Token não fornecido"]);
        http_response_code(401);
        exit;
    }

    try {
        $key = 'seu_segredo'; // Chave secreta para verificar o token
        
        // Decodifica o token JWT usando a função decode() sem a referência
        $decoded = JWT::decode($token, $key, array('HS256'));

        // Verifica se o tipo de usuário é restaurante
        if ($decoded->user_type !== 'restaurante') {
            echo json_encode(["message" => "Usuário não tem permissão para adicionar pratos"]);
            http_response_code(403);
            exit;
        }

        // Verifica se os campos obrigatórios estão presentes
        if (
            empty($data->nome)
            || empty($data->descricao)
            || empty($data->preco)
            || empty($data->imagem)
            || empty($data->tipo)
            || empty($data->disponivel)
            || empty($data->restaurante_id)
        ) {
            echo json_encode(["message" => "Campos obrigatórios ausentes"]);
            http_response_code(400);
            exit;
        }

        $check_prato_stmt = $conn->prepare("SELECT id FROM restaurantes WHERE id = ?");
        $check_prato_stmt->bind_param("i", $data->restaurante_id);
        $check_prato_stmt->execute();
        $check_prato_result = $check_prato_stmt->get_result();

        if ($check_prato_result->num_rows === 0) {
            echo json_encode(["message" => "Restaurante com ID {$data->restaurante_id} não encontrado"]);
            http_response_code(404);
            exit;
        }

        // Insere os dados na tabela de pratos
        $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco, imagem, tipo, disponivel, restaurante_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssii", $data->nome, $data->descricao, $data->preco, $data->imagem, $data->tipo, $data->disponivel, $data->restaurante_id);

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
