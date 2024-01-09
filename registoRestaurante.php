<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    $checkStmt = $conn->prepare("SELECT id FROM restaurantes WHERE email = ?");
    $checkStmt->bind_param("s", $data->email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["message" => "Este email já está sendo utilizado por outro restaurante"]);
        http_response_code(400);
        exit;
    }

    if (
        empty($data->nome)
        || empty($data->contribuinte)
        || empty($data->telemovel)
        || empty($data->rua)
        || empty($data->porta)
        || empty($data->localizacao)
        || empty($data->pais)
        || empty($data->codPostal)
        || empty($data->email)
        || empty($data->password)
    ) {
        echo json_encode(["message" => "Campos obrigatórios ausentes"]);
        http_response_code(400);
        exit;
    }

    $password = password_hash($data->password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO restaurantes (nome, contribuinte, telemovel, rua, porta, localizacao, pais, codPostal, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $data->nome, $data->contribuinte, $data->telemovel, $data->rua, $data->porta, $data->localizacao, $data->pais, $data->codPostal, $data->email, $password);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Registo bem-sucedido"]);
        http_response_code(200);
        exit;
    } else {
        echo json_encode(["message" => "Erro ao inserir dados"]);
        http_response_code(500);
        exit;
    }
} else {
    echo json_encode(["message" => "Método não permitido"]);
    http_response_code(405);
    exit;
}
?>
