<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se os campos obrigatórios estão presentes
    if (
        empty($data->prato_id)
        || empty($data->quantidade)
        || empty($data->data_reserva)
        || empty($data->cliente_id)
    ) {
        echo json_encode(["message" => "Campos obrigatórios ausentes"]);
        http_response_code(400);
        exit;
    }

    // Verifica se o prato_id existe na tabela de pratos
    $check_prato_stmt = $conn->prepare("SELECT id FROM pratos WHERE id = ?");
    $check_prato_stmt->bind_param("i", $data->prato_id);
    $check_prato_stmt->execute();
    $check_prato_result = $check_prato_stmt->get_result();

    if ($check_prato_result->num_rows === 0) {
        echo json_encode(["message" => "Prato com ID {$data->prato_id} não encontrado"]);
        http_response_code(404);
        exit;
    }

    // Verifica se o cliente_id existe na tabela de clientes
    $check_cliente_stmt = $conn->prepare("SELECT id FROM clientes WHERE id = ?");
    $check_cliente_stmt->bind_param("i", $data->cliente_id);
    $check_cliente_stmt->execute();
    $check_cliente_result = $check_cliente_stmt->get_result();

    if ($check_cliente_result->num_rows === 0) {
        echo json_encode(["message" => "Cliente com ID {$data->cliente_id} não encontrado"]);
        http_response_code(404);
        exit;
    }

    // Insere os dados na tabela de reservas
    $insert_stmt = $conn->prepare("INSERT INTO reservas (prato_id, quantidade, data_reserva, cliente_id) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("iisi", $data->prato_id, $data->quantidade, $data->data_reserva, $data->cliente_id);

    // Verifica se a execução foi bem-sucedida
    if ($insert_stmt->execute()) {
        echo json_encode(["message" => "Reserva efetuada com sucesso"]);
        http_response_code(200);
        exit;
    } else {
        echo json_encode(["message" => "Erro ao efetuar reserva"]);
        http_response_code(500);
        exit;
    }
}
?>
