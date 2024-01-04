<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

include 'conexao.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if (isset($token)) {

    $data = json_decode(file_get_contents("php://input"));

    $chave = "seu_segredo";

    $tokenInfo = JWT::decode($token, new Key($chave, 'HS256'));

    $utilizador = Utilizador::find($tokenInfo->utilizador_id);

    } else {

        header('Content-Type: application/json');
        echo json_encode(["message" => "Sessão Inválida"]);
        http_response_code(401);
        exit;
    }