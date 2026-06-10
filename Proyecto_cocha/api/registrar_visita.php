<?php
/*
|--------------------------------------------------------------------------
| API REST: Registrar visita única cada 24 horas
|--------------------------------------------------------------------------
| Método: POST
|
| Recibe JSON:
| {
|   "id_usuario": 1,
|   "tipo_recurso": "autor",
|   "id_recurso": 1
| }
|
| tipo_recurso permitido:
| - autor
| - libro
*/

header("Content-Type: application/json; charset=utf-8");

function responder($success, $message, $data = null, $error = null)
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data,
        "error" => $error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$host = "localhost";
$dbname = "sistema_cocha";
$user = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    responder(false, "Error de conexión a la base de datos.", null, $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder(false, "Método no permitido. Use POST.");
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    responder(false, "No se recibieron datos válidos en formato JSON.");
}

$id_usuario = $input["id_usuario"] ?? "";
$tipo_recurso = trim($input["tipo_recurso"] ?? "");
$id_recurso = $input["id_recurso"] ?? "";

if ($id_usuario === "" || !is_numeric($id_usuario)) {
    responder(false, "Debe enviar un id_usuario válido.");
}

if ($id_recurso === "" || !is_numeric($id_recurso)) {
    responder(false, "Debe enviar un id_recurso válido.");
}

$tipos_permitidos = ["autor", "libro"];

if (!in_array($tipo_recurso, $tipos_permitidos)) {
    responder(false, "El tipo_recurso no es válido. Use autor o libro.");
}

$id_usuario = (int)$id_usuario;
$id_recurso = (int)$id_recurso;

$ip_usuario = $_SERVER["REMOTE_ADDR"] ?? null;
$navegador = $_SERVER["HTTP_USER_AGENT"] ?? null;

try {
    /*
    |--------------------------------------------------------------------------
    | Verificar que el usuario exista
    |--------------------------------------------------------------------------
    */

    $sql_usuario = "SELECT id 
                    FROM usuarios 
                    WHERE id = :id_usuario
                    AND estado = 'activo'
                    LIMIT 1";

    $stmt_usuario = $pdo->prepare($sql_usuario);
    $stmt_usuario->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt_usuario->execute();

    if (!$stmt_usuario->fetch(PDO::FETCH_ASSOC)) {
        responder(false, "El usuario no existe o está inactivo.");
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar que el recurso exista
    |--------------------------------------------------------------------------
    */

    if ($tipo_recurso === "autor") {
        $sql_recurso = "SELECT id 
                        FROM autores 
                        WHERE id = :id_recurso
                        AND estado = 'activo'
                        LIMIT 1";
    } else {
        $sql_recurso = "SELECT id 
                        FROM libros 
                        WHERE id = :id_recurso
                        AND estado = 'activo'
                        LIMIT 1";
    }

    $stmt_recurso = $pdo->prepare($sql_recurso);
    $stmt_recurso->bindValue(":id_recurso", $id_recurso, PDO::PARAM_INT);
    $stmt_recurso->execute();

    if (!$stmt_recurso->fetch(PDO::FETCH_ASSOC)) {
        responder(false, "El recurso no existe o está inactivo.");
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar si ya existe visita en las últimas 24 horas
    |--------------------------------------------------------------------------
    */

    $sql_visita = "SELECT id, fecha_visita
                   FROM visitas_paginas
                   WHERE id_usuario = :id_usuario
                   AND tipo_recurso = :tipo_recurso
                   AND id_recurso = :id_recurso
                   AND estado = 'activo'
                   AND fecha_visita >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                   ORDER BY fecha_visita DESC
                   LIMIT 1";

    $stmt_visita = $pdo->prepare($sql_visita);
    $stmt_visita->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt_visita->bindValue(":tipo_recurso", $tipo_recurso);
    $stmt_visita->bindValue(":id_recurso", $id_recurso, PDO::PARAM_INT);
    $stmt_visita->execute();

    $visita_reciente = $stmt_visita->fetch(PDO::FETCH_ASSOC);

    if ($visita_reciente) {
        $sql_total = "SELECT COUNT(*) AS total
                      FROM visitas_paginas
                      WHERE tipo_recurso = :tipo_recurso
                      AND id_recurso = :id_recurso
                      AND estado = 'activo'";

        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->bindValue(":tipo_recurso", $tipo_recurso);
        $stmt_total->bindValue(":id_recurso", $id_recurso, PDO::PARAM_INT);
        $stmt_total->execute();

        $total = $stmt_total->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

        responder(true, "La visita ya fue registrada dentro de las últimas 24 horas. No se contará nuevamente.", [
            "visita_contabilizada" => false,
            "ultima_visita" => $visita_reciente["fecha_visita"],
            "total_visitas" => (int)$total
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar nueva visita
    |--------------------------------------------------------------------------
    */

    $sql_insert = "INSERT INTO visitas_paginas (
                        id_usuario,
                        tipo_recurso,
                        id_recurso,
                        fecha_visita,
                        ip_usuario,
                        navegador,
                        estado
                   ) VALUES (
                        :id_usuario,
                        :tipo_recurso,
                        :id_recurso,
                        NOW(),
                        :ip_usuario,
                        :navegador,
                        'activo'
                   )";

    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt_insert->bindValue(":tipo_recurso", $tipo_recurso);
    $stmt_insert->bindValue(":id_recurso", $id_recurso, PDO::PARAM_INT);
    $stmt_insert->bindValue(":ip_usuario", $ip_usuario);
    $stmt_insert->bindValue(":navegador", $navegador);
    $stmt_insert->execute();

    /*
    |--------------------------------------------------------------------------
    | Obtener total actualizado de visitas
    |--------------------------------------------------------------------------
    */

    $sql_total = "SELECT COUNT(*) AS total
                  FROM visitas_paginas
                  WHERE tipo_recurso = :tipo_recurso
                  AND id_recurso = :id_recurso
                  AND estado = 'activo'";

    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->bindValue(":tipo_recurso", $tipo_recurso);
    $stmt_total->bindValue(":id_recurso", $id_recurso, PDO::PARAM_INT);
    $stmt_total->execute();

    $total = $stmt_total->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

    responder(true, "Visita registrada correctamente.", [
        "visita_contabilizada" => true,
        "tipo_recurso" => $tipo_recurso,
        "id_recurso" => $id_recurso,
        "total_visitas" => (int)$total
    ]);

} catch (PDOException $e) {
    responder(false, "Error al registrar la visita.", null, $e->getMessage());
}