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



$id_autor = $_GET["id_autor"] ?? "";

if ($id_autor === "" || !is_numeric($id_autor)) {
    responder(false, "Debe enviar un id_autor válido.");
}



try {
    $sql_autor = "SELECT 
                    id, 
                    nombre,
                    estado
                  FROM autores
                  WHERE id = :id_autor
                  AND estado = 'activo'
                  LIMIT 1";

    $stmt_autor = $pdo->prepare($sql_autor);
    $stmt_autor->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
    $stmt_autor->execute();

    $autor = $stmt_autor->fetch(PDO::FETCH_ASSOC);

    if (!$autor) {
        responder(false, "El autor no existe o se encuentra inactivo.");
    }

} catch (PDOException $e) {
    responder(false, "Error al verificar el autor.", $e->getMessage());
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

                c.id AS id_categoria,
                c.categoria,

                e.id AS id_editorial,
                e.nombre AS editorial,

                i.id AS id_idioma,
                i.nombre AS idioma,

                f.id AS id_formato,
                f.formato,

                GROUP_CONCAT(DISTINCT a_todos.nombre SEPARATOR ', ') AS autores

            FROM libros l

            INNER JOIN categorias c 
                ON l.id_categoria = c.id

            INNER JOIN editoriales e 
                ON l.id_editorial = e.id

            INNER JOIN idiomas i 
                ON l.id_idioma = i.id

            INNER JOIN formatos f 
                ON l.id_formato = f.id

            LEFT JOIN libros_autores la_filtro
                ON l.id = la_filtro.id_libro
                AND la_filtro.id_autor = :id_autor

            LEFT JOIN libros_autores la_todos
                ON l.id = la_todos.id_libro

            LEFT JOIN autores a_todos
                ON la_todos.id_autor = a_todos.id

            WHERE l.estado = 'activo'
            AND (
                la_filtro.id_autor = :id_autor
                OR l.id_autor = :id_autor
            )

            GROUP BY l.id
            ORDER BY l.titulo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
    $stmt->execute();

    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responder(true, "Material de biblioteca listado correctamente por autor.", [
        "autor" => $autor,
        "total" => count($materiales),
        "materiales" => $materiales
    ]);

} catch (PDOException $e) {
    responder(false, "Error al listar el material del autor.", $e->getMessage());
}