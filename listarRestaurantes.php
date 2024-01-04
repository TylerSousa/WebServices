<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Seleciona todos os restaurantes da tabela
    $selectStmt = $conn->prepare("SELECT nome FROM restaurantes");
    $selectStmt->execute();
    $result = $selectStmt->get_result();

    // Verifica se há restaurantes
    if ($result->num_rows > 0) {
        // Constrói um array associativo com os dados dos restaurantes
        $restaurantes = [];
        while ($row = $result->fetch_assoc()) {
            $restaurantes[] = $row;
        }

        // Retorna a lista de restaurantes em formato JSON
        echo json_encode($restaurantes);
        http_response_code(200);
        exit;
    } else {
        echo json_encode(["message" => "Nenhum restaurante registrado"]);
        http_response_code(404);
        exit;
    }
} else {
    echo json_encode(["message" => "Método não permitido"]);
    http_response_code(405);
    exit;
}
?>
