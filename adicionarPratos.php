<?php
include 'conexao.php'; // Arquivo de conexão com o banco de dados
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se os campos obrigatórios estão presentes
    if (
        empty($data->nome)
        || empty($data->descricao)
        || empty($data->preco)
        || empty($data->imagem)
        || empty($data->tipo)
        || empty($data->disponivel)
        || empty($data->restaurante_id )
    ) {
        echo json_encode(["message" => "Campos obrigatórios ausentes"]);
        http_response_code(400);
        exit;
    }

    // Insere os dados na tabela de pratos
    $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco, imagem, tipo, disponivel, restaurante_id ) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssii", $data->nome, $data->descricao, $data->preco, $data->imagem, $data->tipo, $data->disponivel, $data->restaurante_id );
    
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
}
?>
