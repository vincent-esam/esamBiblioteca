<?php
require_once "../config/database.php";

header("Content-Type: application/json; charset=utf-8");

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
| Obtener método HTTP
|--------------------------------------------------------------------------
*/
$metodo = $_SERVER["REQUEST_METHOD"];

/*
|--------------------------------------------------------------------------
| GET: Mostrar libros o un libro
|--------------------------------------------------------------------------
| Ejemplo:
| http://localhost/Proyecto_cocha/api/libros.php
| http://localhost/Proyecto_cocha/api/libros.php?id=1
*/
if ($metodo === "GET") {
    try {
        $id = $_GET["id"] ?? null;

        if ($id !== null) {
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
                        l.estado,
                        c.categoria,
                        a.nombre AS autor,
                        e.nombre AS editorial,
                        i.nombre AS idioma,
                        f.formato
                    FROM libros l
                    INNER JOIN categorias c ON l.id_categoria = c.id
                    INNER JOIN autores a ON l.id_autor = a.id
                    INNER JOIN editoriales e ON l.id_editorial = e.id
                    INNER JOIN idiomas i ON l.id_idioma = i.id
                    INNER JOIN formatos f ON l.id_formato = f.id
                    WHERE l.id = :id
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $libro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$libro) {
                responder(false, "Libro no encontrado.");
            }

            responder(true, "Libro obtenido correctamente.", $libro);
        }

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
                    l.estado,
                    c.categoria,
                    a.nombre AS autor,
                    e.nombre AS editorial,
                    i.nombre AS idioma,
                    f.formato
                FROM libros l
                INNER JOIN categorias c ON l.id_categoria = c.id
                INNER JOIN autores a ON l.id_autor = a.id
                INNER JOIN editoriales e ON l.id_editorial = e.id
                INNER JOIN idiomas i ON l.id_idioma = i.id
                INNER JOIN formatos f ON l.id_formato = f.id
                ORDER BY l.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        responder(true, "Libros listados correctamente.", $libros);

    } catch (PDOException $e) {
        responder(false, "Error al obtener libros.", $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| POST: Crear libro
|--------------------------------------------------------------------------
| Enviar JSON:
| {
|   "titulo": "Libro de prueba",
|   "subtitulo": "Subtítulo",
|   "descripcion": "Descripción del libro",
|   "isbn": "978-0000000001",
|   "fecha_registro": "2026-05-27",
|   "edicion": 1,
|   "numero_paginas": 250,
|   "id_categoria": 1,
|   "id_autor": 1,
|   "id_editorial": 1,
|   "id_idioma": 1,
|   "id_formato": 1,
|   "ruta_portada": ""
| }
*/
if ($metodo === "POST") {
    try {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            responder(false, "No se recibieron datos válidos.");
        }

        $titulo = trim($input["titulo"] ?? "");
        $subtitulo = trim($input["subtitulo"] ?? "");
        $descripcion = trim($input["descripcion"] ?? "");
        $isbn = trim($input["isbn"] ?? "");
        $fecha_registro = trim($input["fecha_registro"] ?? "");
        $edicion = $input["edicion"] ?? null;
        $numero_paginas = $input["numero_paginas"] ?? null;
        $ruta_portada = trim($input["ruta_portada"] ?? "");

        $id_categoria = $input["id_categoria"] ?? "";
        $id_autor = $input["id_autor"] ?? "";
        $id_editorial = $input["id_editorial"] ?? "";
        $id_idioma = $input["id_idioma"] ?? "";
        $id_formato = $input["id_formato"] ?? "";

        if (
            $titulo === "" ||
            $id_categoria === "" ||
            $id_autor === "" ||
            $id_editorial === "" ||
            $id_idioma === "" ||
            $id_formato === ""
        ) {
            responder(false, "Debe completar los campos obligatorios.");
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO libros (
                    titulo,
                    estado,
                    id_categoria,
                    id_autor,
                    ruta_portada,
                    descripcion,
                    isbn,
                    fecha_registro,
                    subtitulo,
                    id_editorial,
                    edicion,
                    numero_paginas,
                    id_idioma,
                    id_formato
                ) VALUES (
                    :titulo,
                    'activo',
                    :id_categoria,
                    :id_autor,
                    :ruta_portada,
                    :descripcion,
                    :isbn,
                    :fecha_registro,
                    :subtitulo,
                    :id_editorial,
                    :edicion,
                    :numero_paginas,
                    :id_idioma,
                    :id_formato
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(":titulo", $titulo);
        $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt->bindValue(":ruta_portada", $ruta_portada);
        $stmt->bindValue(":descripcion", $descripcion);
        $stmt->bindValue(":isbn", $isbn);
        $stmt->bindValue(":fecha_registro", $fecha_registro !== "" ? $fecha_registro : null);
        $stmt->bindValue(":subtitulo", $subtitulo);
        $stmt->bindValue(":id_editorial", $id_editorial, PDO::PARAM_INT);
        $stmt->bindValue(":edicion", $edicion);
        $stmt->bindValue(":numero_paginas", $numero_paginas);
        $stmt->bindValue(":id_idioma", $id_idioma, PDO::PARAM_INT);
        $stmt->bindValue(":id_formato", $id_formato, PDO::PARAM_INT);

        $stmt->execute();

        $id_libro = $pdo->lastInsertId();

        $sql_autor = "INSERT INTO libros_autores (id_libro, id_autor)
                      VALUES (:id_libro, :id_autor)";

        $stmt_autor = $pdo->prepare($sql_autor);
        $stmt_autor->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
        $stmt_autor->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt_autor->execute();

        $pdo->commit();

        responder(true, "Libro creado correctamente.", [
            "id_libro" => $id_libro
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        responder(false, "Error al crear el libro.", $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| PUT: Actualizar libro
|--------------------------------------------------------------------------
| Enviar JSON con el ID:
| {
|   "id": 1,
|   "titulo": "Libro actualizado",
|   ...
| }
*/
if ($metodo === "PUT") {
    try {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            responder(false, "No se recibieron datos válidos.");
        }

        $id = $input["id"] ?? "";

        if ($id === "" || !is_numeric($id)) {
            responder(false, "ID de libro no válido.");
        }

        $titulo = trim($input["titulo"] ?? "");
        $subtitulo = trim($input["subtitulo"] ?? "");
        $descripcion = trim($input["descripcion"] ?? "");
        $isbn = trim($input["isbn"] ?? "");
        $fecha_registro = trim($input["fecha_registro"] ?? "");
        $edicion = $input["edicion"] ?? null;
        $numero_paginas = $input["numero_paginas"] ?? null;
        $ruta_portada = trim($input["ruta_portada"] ?? "");

        $id_categoria = $input["id_categoria"] ?? "";
        $id_autor = $input["id_autor"] ?? "";
        $id_editorial = $input["id_editorial"] ?? "";
        $id_idioma = $input["id_idioma"] ?? "";
        $id_formato = $input["id_formato"] ?? "";

        if (
            $titulo === "" ||
            $id_categoria === "" ||
            $id_autor === "" ||
            $id_editorial === "" ||
            $id_idioma === "" ||
            $id_formato === ""
        ) {
            responder(false, "Debe completar los campos obligatorios.");
        }

        $pdo->beginTransaction();

        $sql = "UPDATE libros SET
                    titulo = :titulo,
                    id_categoria = :id_categoria,
                    id_autor = :id_autor,
                    ruta_portada = :ruta_portada,
                    descripcion = :descripcion,
                    isbn = :isbn,
                    fecha_registro = :fecha_registro,
                    subtitulo = :subtitulo,
                    id_editorial = :id_editorial,
                    edicion = :edicion,
                    numero_paginas = :numero_paginas,
                    id_idioma = :id_idioma,
                    id_formato = :id_formato
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":titulo", $titulo);
        $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt->bindValue(":ruta_portada", $ruta_portada);
        $stmt->bindValue(":descripcion", $descripcion);
        $stmt->bindValue(":isbn", $isbn);
        $stmt->bindValue(":fecha_registro", $fecha_registro !== "" ? $fecha_registro : null);
        $stmt->bindValue(":subtitulo", $subtitulo);
        $stmt->bindValue(":id_editorial", $id_editorial, PDO::PARAM_INT);
        $stmt->bindValue(":edicion", $edicion);
        $stmt->bindValue(":numero_paginas", $numero_paginas);
        $stmt->bindValue(":id_idioma", $id_idioma, PDO::PARAM_INT);
        $stmt->bindValue(":id_formato", $id_formato, PDO::PARAM_INT);

        $stmt->execute();

        $sql_delete_autores = "DELETE FROM libros_autores WHERE id_libro = :id_libro";
        $stmt_delete_autores = $pdo->prepare($sql_delete_autores);
        $stmt_delete_autores->bindValue(":id_libro", $id, PDO::PARAM_INT);
        $stmt_delete_autores->execute();

        $sql_insert_autor = "INSERT INTO libros_autores (id_libro, id_autor)
                             VALUES (:id_libro, :id_autor)";

        $stmt_insert_autor = $pdo->prepare($sql_insert_autor);
        $stmt_insert_autor->bindValue(":id_libro", $id, PDO::PARAM_INT);
        $stmt_insert_autor->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt_insert_autor->execute();

        $pdo->commit();

        responder(true, "Libro actualizado correctamente.");

    } catch (PDOException $e) {
        $pdo->rollBack();
        responder(false, "Error al actualizar el libro.", $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| DELETE: Eliminar libro
|--------------------------------------------------------------------------
| Ejemplo:
| http://localhost/Proyecto_cocha/api/libros.php?id=1
*/
if ($metodo === "DELETE") {
    try {
        $id = $_GET["id"] ?? "";

        if ($id === "" || !is_numeric($id)) {
            responder(false, "ID de libro no válido.");
        }

        $sql = "DELETE FROM libros WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            responder(true, "Libro eliminado correctamente.");
        } else {
            responder(false, "No se encontró el libro para eliminar.");
        }

    } catch (PDOException $e) {
        responder(false, "Error al eliminar el libro.", $e->getMessage());
    }
}

responder(false, "Método no permitido.");