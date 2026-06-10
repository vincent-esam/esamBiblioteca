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
    responder(false, "Error de conexión a la base de datos.", null, $e->getMessage());
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

$entidad = trim($_GET["entidad"] ?? "");
$q = trim($_GET["q"] ?? "");
$limite = $_GET["limite"] ?? 10;

if ($entidad === "") {
    responder(false, "Debe enviar la entidad a buscar.");
}

if (!is_numeric($limite) || $limite < 1) {
    $limite = 10;
}

if ($limite > 50) {
    $limite = 50;
}

$entidades_permitidas = ["libros", "autores", "categorias"];

if (!in_array($entidad, $entidades_permitidas)) {
    responder(false, "La entidad solicitada no está permitida.");
}

$buscar = "%" . $q . "%";

try {


    if ($entidad === "libros") {
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

                    c.categoria,
                    e.nombre AS editorial,
                    i.nombre AS idioma,
                    f.formato,

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
                        GROUP_CONCAT(a.nombre SEPARATOR ', ') AS autores
                    FROM libros_autores la
                    INNER JOIN autores a 
                        ON la.id_autor = a.id
                    GROUP BY la.id_libro
                ) autores_data
                    ON l.id = autores_data.id_libro

                WHERE l.estado = 'activo'
                AND (
                    l.titulo LIKE :q1
                    OR l.subtitulo LIKE :q2
                    OR l.descripcion LIKE :q3
                    OR l.isbn LIKE :q4
                    OR l.autor_corporativo LIKE :q5
                    OR c.categoria LIKE :q6
                    OR e.nombre LIKE :q7
                    OR i.nombre LIKE :q8
                    OR f.formato LIKE :q9
                    OR autores_data.autores LIKE :q10
                )

                ORDER BY l.titulo ASC
                LIMIT :limite";

        $stmt = $pdo->prepare($sql);

        for ($i = 1; $i <= 10; $i++) {
            $stmt->bindValue(":q" . $i, $buscar);
        }

        $stmt->bindValue(":limite", (int)$limite, PDO::PARAM_INT);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        responder(true, "Resultados de libros obtenidos correctamente.", [
            "entidad" => "libros",
            "busqueda" => $q,
            "total" => count($resultados),
            "resultados" => $resultados
        ]);
    }


    if ($entidad === "autores") {
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
                WHERE estado = 'activo'
                AND (
                    nombre LIKE :q1
                    OR apellido_paterno LIKE :q2
                    OR apellido_materno LIKE :q3
                    OR seudonimo LIKE :q4
                    OR tipo_autor LIKE :q5
                    OR correo LIKE :q6
                    OR nacionalidad LIKE :q7
                    OR grado_academico LIKE :q8
                    OR especialidad LIKE :q9
                    OR institucion LIKE :q10
                    OR orcid LIKE :q11
                )
                ORDER BY nombre ASC
                LIMIT :limite";

        $stmt = $pdo->prepare($sql);

        for ($i = 1; $i <= 11; $i++) {
            $stmt->bindValue(":q" . $i, $buscar);
        }

        $stmt->bindValue(":limite", (int)$limite, PDO::PARAM_INT);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        responder(true, "Resultados de autores obtenidos correctamente.", [
            "entidad" => "autores",
            "busqueda" => $q,
            "total" => count($resultados),
            "resultados" => $resultados
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Buscar categorías
    |--------------------------------------------------------------------------
    */

    if ($entidad === "categorias") {
        $sql = "SELECT
                    id,
                    categoria,
                    estado,
                    creado_el,
                    actualizado_el
                FROM categorias
                WHERE estado = 'activo'
                AND categoria LIKE :q
                ORDER BY categoria ASC
                LIMIT :limite";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":q", $buscar);
        $stmt->bindValue(":limite", (int)$limite, PDO::PARAM_INT);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        responder(true, "Resultados de categorías obtenidos correctamente.", [
            "entidad" => "categorias",
            "busqueda" => $q,
            "total" => count($resultados),
            "resultados" => $resultados
        ]);
    }

} catch (PDOException $e) {
    responder(false, "Error al realizar la búsqueda.", null, $e->getMessage());
}