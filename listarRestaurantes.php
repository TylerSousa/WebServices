<?php
include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10; 
    $page = isset($_GET['page']) ? $_GET['page'] : 1; 

    $offset = ($page - 1) * $limit; 

    $selectStmt = $conn->prepare("SELECT nome FROM restaurantes LIMIT ? OFFSET ?");
    $selectStmt->bind_param("ii", $limit, $offset);
    $selectStmt->execute();
    $result = $selectStmt->get_result();

    if ($result->num_rows > 0) {
        $restaurantes = [];
        while ($row = $result->fetch_assoc()) {
            $restaurantes[] = $row;
        }

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
