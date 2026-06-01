<?php
// api_segura.php - Autenticación por Cabeceras (Ejercicio 4)
header('Content-Type: application/json; charset=utf-8');

define('TOKEN_CORRECTO', 'SST-Key-Pro-2026');
$headers = getallheaders();
$apiKeyRecibida = $headers['X-API-KEY'] ?? null;

if (!$apiKeyRecibida) {
    http_response_code(401);
    echo json_encode(["status" => "error", "mensaje" => "Falta la cabecera 'X-API-KEY'."]);
    exit;
}

if ($apiKeyRecibida !== TOKEN_CORRECTO) {
    http_response_code(403);
    echo json_encode(["status" => "error", "mensaje" => "API Key incorrecta. Acceso denegado."]);
    exit;
}

http_response_code(200);
echo json_encode([
    "status" => "success",
    "mensaje" => "Autenticación Exitosa por Cabecera.",
    "datos" => [
        "curso" => "Tecnología Web y Cloud Computing",
        "docente" => "JORGE MARTINEZ BURGOS",
        "ciclo" => "VII"
    ]
]);