<?php
include 'conexao.php'; // Arquivo de conexão com o banco de dados
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10; // Número padrão de resultados por página
    $page = isset($_GET['page']) ? $_GET['page'] : 1; // Página padrão

    if(isset($_GET['tipo'])) {
        $tipo = $_GET['tipo'];
        $offset = ($page - 1) * $limit; // Calcula o deslocamento para a paginação

        // Consulta para filtrar os pratos por tipo com paginação
        $stmt = $conn->prepare("SELECT * FROM pratos WHERE tipo = ? LIMIT ? OFFSET ?");
        $stmt->bind_param("sii", $tipo, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verifica se existem resultados
        if ($result->num_rows > 0) {
            $pratos = array();
            while ($row = $result->fetch_assoc()) {
                $pratos[] = $row;
            }
            echo json_encode($pratos);
            http_response_code(200);
            exit;
        } else {
            echo json_encode(["message" => "Nenhum prato encontrado para o tipo especificado"]);
            http_response_code(404);
            exit;
        }
    } else {
        echo json_encode(["message" => "Parâmetro 'tipo' ausente na requisição"]);
        http_response_code(400);
        exit;
    }
}
?>
