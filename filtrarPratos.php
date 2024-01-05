<?php
include 'conexao.php'; // Arquivo de conexão com o banco de dados
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10; // Número padrão de resultados por página
    $page = isset($_GET['page']) ? $_GET['page'] : 1; // Página padrão

    if (isset($_GET['tipo'])) {
        $tipo = $_GET['tipo'];
        $offset = ($page - 1) * $limit; // Calcula o deslocamento para a paginação

        // Consulta para filtrar os pratos por tipo com paginação, retornando apenas o nome do restaurante
        $stmt = $conn->prepare("SELECT pratos.nome, pratos.descricao, pratos.preco, pratos.imagem, pratos.tipo, pratos.disponivel, restaurantes.nome AS restaurante FROM pratos JOIN restaurantes ON pratos.restaurante_id = restaurantes.id WHERE pratos.tipo = ? LIMIT ? OFFSET ?");
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
