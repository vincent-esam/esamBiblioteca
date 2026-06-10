<?php
/*
|--------------------------------------------------------------------------
| API REST: Servicio de búsqueda avanzada de material de biblioteca
|--------------------------------------------------------------------------
| Método: GET
|
| Ejemplos:
| /api/buscar_material.php?q=mysql
| /api/buscar_material.php?id_categoria=1&id_autor=2
| /api/buscar_material.php?q=base&id_categoria=1&id_idioma=1&id_formato=2
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Función de respuesta JSON
|--------------------------------------------------------------------------
*/

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
| Recibir filtros
|--------------------------------------------------------------------------
*/

$q = trim($_GET["q"] ?? "");

$id_categoria = trim($_GET["id_categoria"] ?? "");
$id_autor = trim($_GET["id_autor"] ?? "");
$id_editorial = trim($_GET["id_editorial"] ?? "");
$id_idioma = trim($_GET["id_idioma"] ?? "");
$id_formato = trim($_GET["id_formato"] ?? "");

$fecha_desde = trim($_GET["fecha_desde"] ?? "");
$fecha_hasta = trim($_GET["fecha_hasta"] ?? "");

$autor_corporativo = trim($_GET["autor_corporativo"] ?? "");

$limite = $_GET["limite"] ?? 10;
$pagina = $_GET["pagina"] ?? 1;
$orden = $_GET["orden"] ?? "fecha_desc";

/*
|--------------------------------------------------------------------------
| Validar paginación
|--------------------------------------------------------------------------
*/

if (!is_numeric($limite) || $limite < 1) {
    $limite = 10;
}

if ($limite > 50) {
    $limite = 50;
}

if (!is_numeric($pagina) || $pagina < 1) {
    $pagina = 1;
}

$offset = ((int)$pagina - 1) * (int)$limite;

/*
|--------------------------------------------------------------------------
| Validar orden permitido
|--------------------------------------------------------------------------
*/

$ordenes_permitidos = [
    "titulo_asc" => "l.titulo ASC",
    "titulo_desc" => "l.titulo DESC",
    "fecha_desc" => "l.fecha_registro DESC",
    "fecha_asc" => "l.fecha_registro ASC",
    "id_desc" => "l.id DESC",
    "id_asc" => "l.id ASC"
];

$order_by = $ordenes_permitidos[$orden] ?? $ordenes_permitidos["fecha_desc"];

/*
|--------------------------------------------------------------------------
| Construcción dinámica de filtros
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];

$where[] = "l.estado = 'activo'";

if ($q !== "") {
    $where[] = "(
        l.titulo LIKE :q
        OR l.subtitulo LIKE :q
        OR l.descripcion LIKE :q
        OR l.isbn LIKE :q
        OR l.autor_corporativo LIKE :q
        OR c.categoria LIKE :q
        OR e.nombre LIKE :q
        OR i.nombre LIKE :q
        OR f.formato LIKE :q
        OR autores_data.autores LIKE :q
    )";

    $params[":q"] = [
        "value" => "%" . $q . "%",
        "type" => PDO::PARAM_STR
    ];
}

if ($id_categoria !== "") {
    if (!is_numeric($id_categoria)) {
        responder(false, "El filtro id_categoria no es válido.");
    }

    $where[] = "l.id_categoria = :id_categoria";

    $params[":id_categoria"] = [
        "value" => (int)$id_categoria,
        "type" => PDO::PARAM_INT
    ];
}

if ($id_autor !== "") {
    if (!is_numeric($id_autor)) {
        responder(false, "El filtro id_autor no es válido.");
    }

    $where[] = "EXISTS (
        SELECT 1
        FROM libros_autores la_filtro
        WHERE la_filtro.id_libro = l.id
        AND la_filtro.id_autor = :id_autor
    )";

    $params[":id_autor"] = [
        "value" => (int)$id_autor,
        "type" => PDO::PARAM_INT
    ];
}

if ($id_editorial !== "") {
    if (!is_numeric($id_editorial)) {
        responder(false, "El filtro id_editorial no es válido.");
    }

    $where[] = "l.id_editorial = :id_editorial";

    $params[":id_editorial"] = [
        "value" => (int)$id_editorial,
        "type" => PDO::PARAM_INT
    ];
}

if ($id_idioma !== "") {
    if (!is_numeric($id_idioma)) {
        responder(false, "El filtro id_idioma no es válido.");
    }

    $where[] = "l.id_idioma = :id_idioma";

    $params[":id_idioma"] = [
        "value" => (int)$id_idioma,
        "type" => PDO::PARAM_INT
    ];
}

if ($id_formato !== "") {
    if (!is_numeric($id_formato)) {
        responder(false, "El filtro id_formato no es válido.");
    }

    $where[] = "l.id_formato = :id_formato";

    $params[":id_formato"] = [
        "value" => (int)$id_formato,
        "type" => PDO::PARAM_INT
    ];
}

if ($fecha_desde !== "") {
    $where[] = "l.fecha_registro >= :fecha_desde";

    $params[":fecha_desde"] = [
        "value" => $fecha_desde,
        "type" => PDO::PARAM_STR
    ];
}

if ($fecha_hasta !== "") {
    $where[] = "l.fecha_registro <= :fecha_hasta";

    $params[":fecha_hasta"] = [
        "value" => $fecha_hasta,
        "type" => PDO::PARAM_STR
    ];
}

if ($autor_corporativo !== "") {
    $where[] = "l.autor_corporativo LIKE :autor_corporativo";

    $params[":autor_corporativo"] = [
        "value" => "%" . $autor_corporativo . "%",
        "type" => PDO::PARAM_STR
    ];
}

$where_sql = implode(" AND ", $where);

/*
|--------------------------------------------------------------------------
| Consulta principal
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

            WHERE $where_sql

            ORDER BY $order_by

            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $param) {
        $stmt->bindValue($key, $param["value"], $param["type"]);
    }

    $stmt->bindValue(":limite", (int)$limite, PDO::PARAM_INT);
    $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Consulta para contar total de resultados sin paginación
    |--------------------------------------------------------------------------
    */

    $sql_total = "SELECT COUNT(*) AS total
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
                          GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS autores
                      FROM libros_autores la
                      INNER JOIN autores a
                          ON la.id_autor = a.id
                      GROUP BY la.id_libro
                  ) autores_data
                      ON l.id = autores_data.id_libro

                  WHERE $where_sql";

    $stmt_total = $pdo->prepare($sql_total);

    foreach ($params as $key => $param) {
        $stmt_total->bindValue($key, $param["value"], $param["type"]);
    }

    $stmt_total->execute();

    $total = $stmt_total->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

    responder(true, "Resultados obtenidos correctamente según los filtros aplicados.", [
        "filtros_aplicados" => [
            "q" => $q,
            "id_categoria" => $id_categoria,
            "id_autor" => $id_autor,
            "id_editorial" => $id_editorial,
            "id_idioma" => $id_idioma,
            "id_formato" => $id_formato,
            "fecha_desde" => $fecha_desde,
            "fecha_hasta" => $fecha_hasta,
            "autor_corporativo" => $autor_corporativo,
            "orden" => $orden,
            "limite" => (int)$limite,
            "pagina" => (int)$pagina
        ],
        "paginacion" => [
            "total_registros" => (int)$total,
            "registros_en_pagina" => count($resultados),
            "pagina_actual" => (int)$pagina,
            "limite" => (int)$limite,
            "total_paginas" => ceil($total / $limite)
        ],
        "resultados" => $resultados
    ]);

} catch (PDOException $e) {
    responder(false, "Error al realizar la búsqueda con filtros.", null, $e->getMessage());
}