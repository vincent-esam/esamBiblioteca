<?php
/*
|--------------------------------------------------------------------------
| API REST: Mostrar información personal de un autor por su ID
|--------------------------------------------------------------------------
| Método: GET
| Parámetro requerido: id_autor
| Ejemplo:
| http://localhost/Proyecto_cocha/api/autor_por_id.php?id_autor=1
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
| Función para responder en formato JSON
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
| Recibir y validar el ID del autor
|--------------------------------------------------------------------------
*/

$id_autor = $_GET["id_autor"] ?? "";

if ($id_autor === "" || !is_numeric($id_autor)) {
    responder(false, "Debe enviar un id_autor válido.");
}

/*
|--------------------------------------------------------------------------
| Consultar únicamente la información personal del autor
|--------------------------------------------------------------------------
| No se consulta libros.
| No se consulta material publicado.
| Solo se consulta la tabla autores.
*/

try {
    $sql = "SELECT 
                id,
                nombre,
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
        responder(false, "El autor no existe.");
    }

    responder(true, "Información personal del autor obtenida correctamente.", $autor);

} catch (PDOException $e) {
    responder(false, "Error al obtener la información personal del autor.", $e->getMessage());
}