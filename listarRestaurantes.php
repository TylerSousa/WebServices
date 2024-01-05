<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10; // Número padrão de resultados por página
    $page = isset($_GET['page']) ? $_GET['page'] : 1; // Página padrão

    $offset = ($page - 1) * $limit; // Calcula o deslocamento para a paginação

    // Seleciona os restaurantes com paginação
    $selectStmt = $conn->prepare("SELECT nome FROM restaurantes LIMIT ? OFFSET ?");
    $selectStmt->bind_param("ii", $limit, $offset);
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
        echo json_encode(["message" => "Nenhum restaurante encontrado"]);
        http_response_code(404);
        exit;
    }
} else {
    echo json_encode(["message" => "Método não permitido"]);
    http_response_code(405);
    exit;
}
?>
