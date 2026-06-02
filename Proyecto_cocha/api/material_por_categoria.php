<?php
/*
|--------------------------------------------------------------------------
| API REST: Listar material de biblioteca por categoría
|--------------------------------------------------------------------------
| Método: GET
| Parámetro requerido: id_categoria
| Ejemplo:
| http://localhost/Proyecto_cocha/api/material_por_categoria.php?id_categoria=1
*/

header("Content-Type: application/json; charset=utf-8");

/*
|--------------------------------------------------------------------------
| Conexión a la base de datos
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Función para responder en JSON
|--------------------------------------------------------------------------
*/

function responder($success, $message, $data = null)
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/*
|--------------------------------------------------------------------------
| Validar método HTTP
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responder(false, "Método no permitido. Use GET.");
}

/*
|--------------------------------------------------------------------------
| Recibir y validar parámetro id_categoria
|--------------------------------------------------------------------------
*/

$id_categoria = $_GET["id_categoria"] ?? "";

if ($id_categoria === "" || !is_numeric($id_categoria)) {
    responder(false, "Debe enviar un id_categoria válido.");
}

/*
|--------------------------------------------------------------------------
| Verificar si la categoría existe
|--------------------------------------------------------------------------
*/

try {
    $sql_categoria = "SELECT id, categoria 
                      FROM categorias 
                      WHERE id = :id_categoria 
                      AND estado = 'activo'
                      LIMIT 1";

    $stmt_categoria = $pdo->prepare($sql_categoria);
    $stmt_categoria->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
    $stmt_categoria->execute();

    $categoria = $stmt_categoria->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        responder(false, "La categoría no existe o se encuentra inactiva.");
    }

} catch (PDOException $e) {
    responder(false, "Error al verificar la categoría.", $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| Listar material/libros por categoría
|--------------------------------------------------------------------------
*/

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

                e.nombre AS editorial,
                i.nombre AS idioma,
                f.formato,

                GROUP_CONCAT(a.nombre SEPARATOR ', ') AS autores

            FROM libros l

            INNER JOIN categorias c 
                ON l.id_categoria = c.id

            INNER JOIN editoriales e 
                ON l.id_editorial = e.id

            INNER JOIN idiomas i 
                ON l.id_idioma = i.id

            INNER JOIN formatos f 
                ON l.id_formato = f.id

            LEFT JOIN libros_autores la 
                ON l.id = la.id_libro

            LEFT JOIN autores a 
                ON la.id_autor = a.id

            WHERE l.id_categoria = :id_categoria
            AND l.estado = 'activo'

            GROUP BY l.id
            ORDER BY l.titulo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
    $stmt->execute();

    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responder(true, "Material de biblioteca listado correctamente por categoría.", [
        "categoria" => $categoria,
        "total" => count($materiales),
        "materiales" => $materiales
    ]);

} catch (PDOException $e) {
    responder(false, "Error al listar el material de biblioteca.", $e->getMessage());
}