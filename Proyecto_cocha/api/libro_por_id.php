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
        "error" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}



function responder($success, $message, $data = null)
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

    exit;
}



if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responder(false, "Método no permitido. Use GET.");
}



$id_libro = $_GET["id_libro"] ?? "";

if ($id_libro === "" || !is_numeric($id_libro)) {
    responder(false, "Debe enviar un id_libro válido.");
}


try {
    $sql = "SELECT
                l.id,
                l.titulo,
                l.subtitulo,
                l.descripcion,
                l.isbn,
                l.fecha_registro,
                l.edicion,
                l.numero_paginas,
                l.ruta_portada,
                l.autor_corporativo,
                l.estado,
                l.creado_el,
                l.actualizado_el,

                c.id AS id_categoria,
                c.categoria,

                e.id AS id_editorial,
                e.nombre AS editorial,

                i.id AS id_idioma,
                i.nombre AS idioma,

                f.id AS id_formato,
                f.formato,

                autores_data.id_autores,
                autores_data.autores

            FROM libros l

            INNER JOIN categorias c 
                ON l.id_categoria = c.id

            INNER JOIN editoriales e 
                ON l.id_editorial = e.id

            INNER JOIN idiomas i 
                ON l.id_idioma = i.id

            INNER JOIN formatos f 
                ON l.id_formato = f.id

            LEFT JOIN (
                SELECT 
                    la.id_libro,
                    GROUP_CONCAT(DISTINCT a.id ORDER BY a.nombre SEPARATOR ',') AS id_autores,
                    GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
                FROM libros_autores la
                INNER JOIN autores a 
                    ON la.id_autor = a.id
                GROUP BY la.id_libro
            ) autores_data
                ON l.id = autores_data.id_libro

            WHERE l.id = :id_libro
            AND l.estado = 'activo'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
    $stmt->execute();

    $libro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$libro) {
        responder(false, "El libro no existe o se encuentra inactivo.");
    }

    responder(true, "Información del libro obtenida correctamente.", $libro);

} catch (PDOException $e) {
    responder(false, "Error al obtener la información del libro.", $e->getMessage());
}