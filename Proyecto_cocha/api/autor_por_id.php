<?php

header("Content-Type: application/json; charset=utf-8");



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
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos.",
        "error" => $e->getMessage(),
        "data" => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


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


if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responder(false, "Método no permitido. Use GET.");
}


$id_autor = $_GET["id_autor"] ?? "";

if ($id_autor === "" || !is_numeric($id_autor)) {
    responder(false, "Debe enviar un id_autor válido.");
}



try {
    $sql = "SELECT
                id,
                nombre,
                apellido_paterno,
                apellido_materno,
                seudonimo,
                tipo_autor,
                correo,
                telefono,
                nacionalidad,
                fecha_nacimiento,
                grado_academico,
                especialidad,
                institucion,
                orcid,
                sitio_web,
                biografia,
                estado,
                creado_el,
                actualizado_el
            FROM autores
            WHERE id = :id_autor
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
    $stmt->execute();

    $autor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$autor) {
        responder(false, "El autor no existe.", null);
    }

    responder(true, "Información personal del autor obtenida correctamente.", $autor);

} catch (PDOException $e) {
    responder(
        false,
        "Error al obtener la información personal del autor.",
        null,
        $e->getMessage()
    );
}