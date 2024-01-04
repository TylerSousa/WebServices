<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$headers = getallheaders();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

// Verificação do token JWT
if ($token) {
    try {
        $decoded = JWT::decode($token, 'seu_segredo');
        // Continue com o restante da lógica, se necessário, após a verificação do token...
    } catch (Exception $e) {
        echo json_encode(["message" => "Token inválido"]);
        http_response_code(401);
        exit;
    }
}

// Lógica de login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->email) || empty($data->password)) {
        echo json_encode(["message" => "Por favor, forneça email e senha"]);
        http_response_code(400);
        exit;
    }

    $email = $data->email;
    $password = $data->password;

    // Verifica se o usuário é um cliente
    $stmt = $conn->prepare("SELECT id, email, password FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        if (password_verify($password, $hashedPassword)) {
            $payload = [
                "user_id" => $row['id'],
                "user_type" => "cliente"
                // Adicione outras informações necessárias ao payload
            ];
        
            $key = 'seu_segredo'; // Chave secreta para assinar o token
            $algorithm = 'HS256'; // Algoritmo de assinatura
        
            $token = JWT::encode($payload, $key, $algorithm);
            echo json_encode(["token" => $token, "message" => "Login bem-sucedido como cliente"]);
            http_response_code(200);
            exit;
        }
        
    }

    // Verifica se o usuário é um restaurante
    $stmt = $conn->prepare("SELECT id, email, password FROM restaurantes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        if (password_verify($password, $hashedPassword)) {
            $payload = [
                "user_id" => $row['id'],
                "user_type" => "restaurante"
                // Adicione outras informações necessárias ao payload
            ];

            $key = 'seu_segredo'; // Chave secreta para assinar o token
            $algorithm = 'HS256'; // Algoritmo de assinatura

            $token = JWT::encode($payload, $key, $algorithm);
            echo json_encode(["token" => $token, "message" => "Login bem-sucedido como restaurante"]);
            http_response_code(200);
            exit;
        }
    }

    echo json_encode(["message" => "Credenciais inválidas"]);
    http_response_code(401);
    exit;
} else {
    echo json_encode(["message" => "Método não permitido"]);
    http_response_code(405);
    exit;
}
?>
