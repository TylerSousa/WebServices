<?php
include 'conexao.php'; // Arquivo de conexão com o banco de dados
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Defina o tipo de usuário aqui conforme a lógica do seu sistema
$tipo_usuario = 'restaurante'; 

if ($_SERVER["REQUEST_METHOD"] === "DELETE" && $tipo_usuario == 'restaurante') {
    $data = json_decode(file_get_contents("php://input"));

    // Verifica se o ID do prato a ser excluído foi enviado
    if (empty($data->prato_id)) {
        echo json_encode(["message" => "ID do prato não fornecido"]);
        http_response_code(400);
        exit;
    }

    $prato_id = $data->prato_id;

    // Exclui o prato
    $stmt_delete = $conn->prepare("DELETE FROM pratos WHERE id = ?");
    $stmt_delete->bind_param("i", $prato_id);
    $stmt_delete->execute();

    // Verifica se algum registro foi afetado pela exclusão
    if ($stmt_delete->affected_rows > 0) {
        echo json_encode(["message" => "Prato excluído com sucesso"]);
        http_response_code(200);
        exit;
    } else {
        echo json_encode(["message" => "Prato não encontrado"]);
        http_response_code(404);
        exit;
    }
}
?>
