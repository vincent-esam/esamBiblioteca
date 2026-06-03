<?php
/*
|--------------------------------------------------------------------------
| CONEXIÓN A LA BASE DE DATOS
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
    die("Error de conexión: " . $e->getMessage());
}



$mensaje = $_GET["mensaje"] ?? "";
$tipo = $_GET["tipo"] ?? "";

function redirigir($mensaje, $tipo = "info")
{
    header("Location: index.php?mensaje=" . urlencode($mensaje) . "&tipo=" . urlencode($tipo));
    exit;
}

/*
|--------------------------------------------------------------------------
| CREAR CATEGORÍA
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "guardar") {
    $categoria = trim($_POST["categoria"] ?? "");

    if ($categoria === "") {
        redirigir("Debe ingresar el nombre de la categoría.", "error");
    }

    try {
        $sql_verificar = "SELECT id 
                          FROM categorias 
                          WHERE categoria = :categoria 
                          LIMIT 1";

        $stmt_verificar = $pdo->prepare($sql_verificar);
        $stmt_verificar->bindParam(":categoria", $categoria);
        $stmt_verificar->execute();

        if ($stmt_verificar->rowCount() > 0) {
            redirigir("La categoría ya se encuentra registrada.", "error");
        }

        $sql = "INSERT INTO categorias (categoria, estado)
                VALUES (:categoria, 'activo')";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":categoria", $categoria);

        if ($stmt->execute()) {
            redirigir("Categoría registrada correctamente.", "exito");
        } else {
            redirigir("No se pudo registrar la categoría.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al registrar la categoría: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR CATEGORÍA
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "actualizar") {
    $id_categoria = $_POST["id_categoria"] ?? "";
    $categoria = trim($_POST["categoria"] ?? "");
    $estado = trim($_POST["estado"] ?? "activo");

    if ($id_categoria === "" || !is_numeric($id_categoria)) {
        redirigir("El ID de la categoría no es válido.", "error");
    }

    if ($categoria === "") {
        redirigir("Debe ingresar el nombre de la categoría.", "error");
    }

    if ($estado !== "activo" && $estado !== "inactivo") {
        redirigir("El estado seleccionado no es válido.", "error");
    }

    try {
        $sql_verificar = "SELECT id 
                          FROM categorias 
                          WHERE categoria = :categoria 
                          AND id != :id_categoria
                          LIMIT 1";

        $stmt_verificar = $pdo->prepare($sql_verificar);
        $stmt_verificar->bindParam(":categoria", $categoria);
        $stmt_verificar->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmt_verificar->execute();

        if ($stmt_verificar->rowCount() > 0) {
            redirigir("Ya existe otra categoría con ese nombre.", "error");
        }

        $sql = "UPDATE categorias
                SET categoria = :categoria,
                    estado = :estado
                WHERE id = :id_categoria";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);

        if ($stmt->execute()) {
            redirigir("Categoría actualizada correctamente.", "exito");
        } else {
            redirigir("No se pudo actualizar la categoría.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al actualizar la categoría: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| ELIMINAR CATEGORÍA
|--------------------------------------------------------------------------
| Eliminación lógica: cambia estado a inactivo.
| Esto evita errores si la categoría ya fue asignada a libros.
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "eliminar") {
    $id_categoria = $_POST["id_categoria"] ?? "";

    if ($id_categoria === "" || !is_numeric($id_categoria)) {
        redirigir("El ID de la categoría no es válido.", "error");
    }

    try {
        $sql = "UPDATE categorias
                SET estado = 'inactivo'
                WHERE id = :id_categoria";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);

        if ($stmt->execute()) {
            redirigir("Categoría eliminada correctamente.", "exito");
        } else {
            redirigir("No se pudo eliminar la categoría.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al eliminar la categoría: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| OBTENER CATEGORÍA PARA EDITAR
|--------------------------------------------------------------------------
*/

$categoria_editar = null;

if (isset($_GET["editar"]) && is_numeric($_GET["editar"])) {
    $id_editar = $_GET["editar"];

    try {
        $sql = "SELECT id, categoria, estado, creado_el, actualizado_el
                FROM categorias
                WHERE id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $id_editar, PDO::PARAM_INT);
        $stmt->execute();

        $categoria_editar = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$categoria_editar) {
            redirigir("La categoría seleccionada no existe.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al obtener la categoría: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR CATEGORÍAS ACTIVAS
|--------------------------------------------------------------------------
*/

try {
    $sql = "SELECT id, categoria, estado, creado_el, actualizado_el
            FROM categorias
            WHERE estado = 'activo'
            ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al listar categorías: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías</title>
</head>
<body>

    <h1>Gestión de Categorías</h1>

    <hr>

    <h2>1. Mensajes del sistema</h2>

    <?php if ($mensaje !== ""): ?>
        <p>
            <strong>Tipo:</strong> <?php echo htmlspecialchars($tipo); ?><br>
            <strong>Mensaje:</strong> <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php else: ?>
        <p>No hay mensajes por el momento.</p>
    <?php endif; ?>

    <hr>

    <?php if ($categoria_editar): ?>

        <h2>2. Editar categoría</h2>

        <form action="index.php" method="POST">

            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_categoria" value="<?php echo htmlspecialchars($categoria_editar["id"]); ?>">

            <fieldset>
                <legend>Datos de la categoría</legend>

                <label>Nombre de la categoría:</label><br>
                <input type="text" name="categoria" value="<?php echo htmlspecialchars($categoria_editar["categoria"]); ?>" required><br><br>

                <label>Estado:</label><br>
                <select name="estado" required>
                    <option value="activo" <?php echo ($categoria_editar["estado"] === "activo") ? "selected" : ""; ?>>
                        Activo
                    </option>
                    <option value="inactivo" <?php echo ($categoria_editar["estado"] === "inactivo") ? "selected" : ""; ?>>
                        Inactivo
                    </option>
                </select><br><br>

                <p>
                    <strong>Creado el:</strong>
                    <?php echo htmlspecialchars($categoria_editar["creado_el"]); ?>
                </p>

                <p>
                    <strong>Actualizado el:</strong>
                    <?php echo htmlspecialchars($categoria_editar["actualizado_el"]); ?>
                </p>

            </fieldset>

            <br>

            <button type="submit">Actualizar categoría</button>
            <a href="index.php">Cancelar edición</a>

        </form>

    <?php else: ?>

        <h2>2. Crear categoría</h2>

        <form action="index.php" method="POST">

            <input type="hidden" name="accion" value="guardar">

            <fieldset>
                <legend>Registro estándar de categoría</legend>

                <label>Nombre de la categoría:</label><br>
                <input type="text" name="categoria" required><br><br>

            </fieldset>

            <br>

            <button type="submit">Guardar categoría</button>
            <button type="reset">Limpiar</button>

        </form>

    <?php endif; ?>

    <hr>

    <h2>3. Mostrar categorías registradas</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Creado el</th>
                <th>Actualizado el</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($categorias) > 0): ?>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat["id"]); ?></td>

                        <td><?php echo htmlspecialchars($cat["categoria"]); ?></td>

                        <td><?php echo htmlspecialchars($cat["estado"]); ?></td>

                        <td><?php echo htmlspecialchars($cat["creado_el"]); ?></td>

                        <td><?php echo htmlspecialchars($cat["actualizado_el"]); ?></td>

                        <td>
                            <a href="index.php?editar=<?php echo $cat["id"]; ?>">
                                Editar
                            </a>

                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_categoria" value="<?php echo $cat["id"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No existen categorías registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>