<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
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
        || empty($data->restaurante_id)
    ) {
        echo json_encode(["message" => "Campos obrigatórios ausentes"]);
        http_response_code(400);
        exit;
    }

    $check_restaurante_stmt = $conn->prepare("SELECT id FROM restaurantes WHERE id = ?");
    $check_restaurante_stmt->bind_param("i", $data->restaurante_id);
    $check_restaurante_stmt->execute();
    $check_restaurante_result = $check_restaurante_stmt->get_result();

    if ($check_restaurante_result->num_rows === 0) {
        echo json_encode(["message" => "Restaurante com ID {$data->restaurante_id} não encontrado"]);
        http_response_code(404);
        exit;
    }

    // Insere os dados na tabela de pratos
    $stmt = $conn->prepare("INSERT INTO pratos (nome, descricao, preco, imagem, tipo, disponivel, restaurante_id ) VALUES (?, ?, ?, ?, ?, ?, ?)");
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
// ...

} elseif ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se o ID do prato foi fornecido
    if (empty($data->id)) {
        echo json_encode(["message" => "ID do prato ausente"]);
        http_response_code(400);
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

    // Verifica se o prato a ser atualizado existe
    $check_prato_stmt = $conn->prepare("SELECT id FROM pratos WHERE id = ? AND restaurante_id = ?");
    $check_prato_stmt->bind_param("ii", $data->id, $data->restaurante_id);
    $check_prato_stmt->execute();
    $check_prato_result = $check_prato_stmt->get_result();

    if ($check_prato_result->num_rows === 0) {
        echo json_encode(["message" => "Prato com ID {$data->id} não encontrado ou não pertence ao restaurante com ID {$data->restaurante_id}"]);
        http_response_code(404);
        exit;
    }

    // Atualiza os dados do prato na tabela de pratos
    $update_stmt = $conn->prepare("UPDATE pratos SET nome = ?, descricao = ?, preco = ?, imagem = ?, tipo = ?, disponivel = ?, restaurante_id = ? WHERE id = ?");
    $update_stmt->bind_param("ssdssiii", $data->nome, $data->descricao, $data->preco, $data->imagem, $data->tipo, $data->disponivel, $data->restaurante_id, $data->id);

    // Verifica se a execução foi bem-sucedida
    if ($update_stmt->execute()) {
        echo json_encode(["message" => "Prato atualizado com sucesso"]);
        http_response_code(200);
        exit;
    } else {
        echo json_encode(["message" => "Erro ao atualizar prato"]);
        http_response_code(500);
        exit;
    }
} else {

    echo json_encode(["message" => "Método não permitido"]);
    http_response_code(405);
    exit;
}
?>
