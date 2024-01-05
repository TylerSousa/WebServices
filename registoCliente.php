<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se o email já existe na tabela de restaurantes
    $checkStmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
    $checkStmt->bind_param("s", $data->email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["message" => "Este email já está sendo utilizado por outro cliente"]);
        http_response_code(400);
        exit;
    }
    if (
        isset($data->nome)
        && isset($data->email)
        && isset($data->password)
    ) {
        $password = password_hash($data->password, PASSWORD_DEFAULT);
        // Insere dados na tabela clientes
        $stmt = $conn->prepare("INSERT INTO clientes (nome, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data->nome, $data->email, $password);
        
        // Verifica se a execução foi bem-sucedida
        if ($stmt->execute()) {
            echo json_encode(["message" => "Registo de cliente bem-sucedido"]);
            http_response_code(200);
            exit;
        } else {
            echo json_encode(["message" => "Erro ao inserir dados do cliente"]);
            http_response_code(500);
            exit;
        }
    } else {
        echo json_encode(["message" => "Campos obrigatórios ausentes para cliente"]);
        http_response_code(400);
        exit;
    }
}
?>