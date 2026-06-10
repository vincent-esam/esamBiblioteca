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

/*
|--------------------------------------------------------------------------
| MENSAJES
|--------------------------------------------------------------------------
*/

$mensaje = $_GET["mensaje"] ?? "";
$tipo = $_GET["tipo"] ?? "";

function redirigir($mensaje, $tipo = "info")
{
    header("Location: index.php?mensaje=" . urlencode($mensaje) . "&tipo=" . urlencode($tipo));
    exit;
}

/*
|--------------------------------------------------------------------------
| OBTENER USUARIOS PARA EL SELECT
|--------------------------------------------------------------------------
*/

try {
    $sql_usuarios = "SELECT id, usuario, nombres, apellido_paterno, apellido_materno
                     FROM usuarios
                     WHERE estado = 'activo'
                     ORDER BY nombres ASC";

    $stmt_usuarios = $pdo->prepare($sql_usuarios);
    $stmt_usuarios->execute();

    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener usuarios: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| CREAR PREFERENCIA
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "guardar") {
    $id_usuario = $_POST["id_usuario"] ?? "";
    $profesion = trim($_POST["profesion"] ?? "");
    $area = trim($_POST["area"] ?? "");

    if ($id_usuario === "" || !is_numeric($id_usuario)) {
        redirigir("Debe seleccionar un usuario válido.", "error");
    }

    if ($profesion === "") {
        redirigir("Debe ingresar la profesión.", "error");
    }

    if ($area === "") {
        redirigir("Debe ingresar el área.", "error");
    }

    try {
        $sql_verificar = "SELECT id 
                          FROM preferencias_usuarios 
                          WHERE id_usuario = :id_usuario
                          AND estado = 'activo'
                          LIMIT 1";

        $stmt_verificar = $pdo->prepare($sql_verificar);
        $stmt_verificar->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt_verificar->execute();

        if ($stmt_verificar->rowCount() > 0) {
            redirigir("Este usuario ya tiene preferencias registradas. Puede editarlas.", "error");
        }

        $sql = "INSERT INTO preferencias_usuarios (
                    id_usuario,
                    profesion,
                    area,
                    estado
                ) VALUES (
                    :id_usuario,
                    :profesion,
                    :area,
                    'activo'
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":profesion", $profesion);
        $stmt->bindParam(":area", $area);

        if ($stmt->execute()) {
            redirigir("Preferencias registradas correctamente.", "exito");
        } else {
            redirigir("No se pudieron registrar las preferencias.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al registrar preferencias: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR PREFERENCIA
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "actualizar") {
    $id_preferencia = $_POST["id_preferencia"] ?? "";
    $id_usuario = $_POST["id_usuario"] ?? "";
    $profesion = trim($_POST["profesion"] ?? "");
    $area = trim($_POST["area"] ?? "");
    $estado = trim($_POST["estado"] ?? "activo");

    if ($id_preferencia === "" || !is_numeric($id_preferencia)) {
        redirigir("El ID de la preferencia no es válido.", "error");
    }

    if ($id_usuario === "" || !is_numeric($id_usuario)) {
        redirigir("Debe seleccionar un usuario válido.", "error");
    }

    if ($profesion === "") {
        redirigir("Debe ingresar la profesión.", "error");
    }

    if ($area === "") {
        redirigir("Debe ingresar el área.", "error");
    }

    if ($estado !== "activo" && $estado !== "inactivo") {
        redirigir("El estado seleccionado no es válido.", "error");
    }

    try {
        $sql_verificar = "SELECT id 
                          FROM preferencias_usuarios 
                          WHERE id_usuario = :id_usuario
                          AND id != :id_preferencia
                          AND estado = 'activo'
                          LIMIT 1";

        $stmt_verificar = $pdo->prepare($sql_verificar);
        $stmt_verificar->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt_verificar->bindValue(":id_preferencia", $id_preferencia, PDO::PARAM_INT);
        $stmt_verificar->execute();

        if ($stmt_verificar->rowCount() > 0) {
            redirigir("Ya existe otra preferencia activa para este usuario.", "error");
        }

        $sql = "UPDATE preferencias_usuarios
                SET id_usuario = :id_usuario,
                    profesion = :profesion,
                    area = :area,
                    estado = :estado
                WHERE id = :id_preferencia";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id_preferencia", $id_preferencia, PDO::PARAM_INT);
        $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":profesion", $profesion);
        $stmt->bindParam(":area", $area);
        $stmt->bindParam(":estado", $estado);

        if ($stmt->execute()) {
            redirigir("Preferencias actualizadas correctamente.", "exito");
        } else {
            redirigir("No se pudieron actualizar las preferencias.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al actualizar preferencias: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| ELIMINAR PREFERENCIA
|--------------------------------------------------------------------------
| Eliminación lógica.
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "eliminar") {
    $id_preferencia = $_POST["id_preferencia"] ?? "";

    if ($id_preferencia === "" || !is_numeric($id_preferencia)) {
        redirigir("El ID de la preferencia no es válido.", "error");
    }

    try {
        $sql = "UPDATE preferencias_usuarios
                SET estado = 'inactivo'
                WHERE id = :id_preferencia";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id_preferencia", $id_preferencia, PDO::PARAM_INT);

        if ($stmt->execute()) {
            redirigir("Preferencias eliminadas correctamente.", "exito");
        } else {
            redirigir("No se pudieron eliminar las preferencias.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al eliminar preferencias: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| OBTENER PREFERENCIA PARA EDITAR
|--------------------------------------------------------------------------
*/

$preferencia_editar = null;

if (isset($_GET["editar"]) && is_numeric($_GET["editar"])) {
    $id_editar = $_GET["editar"];

    try {
        $sql = "SELECT *
                FROM preferencias_usuarios
                WHERE id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $id_editar, PDO::PARAM_INT);
        $stmt->execute();

        $preferencia_editar = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$preferencia_editar) {
            redirigir("La preferencia seleccionada no existe.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al obtener la preferencia: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| LEER / LISTAR PREFERENCIAS
|--------------------------------------------------------------------------
*/

try {
    $sql = "SELECT 
                pu.id,
                pu.id_usuario,
                pu.profesion,
                pu.area,
                pu.estado,
                pu.creado_el,
                pu.actualizado_el,
                u.usuario,
                u.nombres,
                u.apellido_paterno,
                u.apellido_materno
            FROM preferencias_usuarios pu
            INNER JOIN usuarios u ON pu.id_usuario = u.id
            WHERE pu.estado = 'activo'
            ORDER BY pu.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al listar preferencias: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preferencias de Usuario</title>
</head>
<body>

    <h1>Gestión de Preferencias de Usuario</h1>

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

    <?php if ($preferencia_editar): ?>

        <h2>2. Editar preferencias</h2>

        <form action="index.php" method="POST">

            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_preferencia" value="<?php echo htmlspecialchars($preferencia_editar["id"]); ?>">

            <fieldset>
                <legend>Datos de preferencia</legend>

                <label>Usuario:</label><br>
                <select name="id_usuario" required>
                    <option value="">Seleccione un usuario</option>

                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario["id"]; ?>" <?php echo ($preferencia_editar["id_usuario"] == $usuario["id"]) ? "selected" : ""; ?>>
                            <?php
                                echo htmlspecialchars(
                                    $usuario["nombres"] . " " .
                                    $usuario["apellido_paterno"] . " " .
                                    $usuario["apellido_materno"] .
                                    " (" . $usuario["usuario"] . ")"
                                );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Profesión:</label><br>
                <input type="text" name="profesion" value="<?php echo htmlspecialchars($preferencia_editar["profesion"]); ?>" required><br><br>

                <label>Área:</label><br>
                <input type="text" name="area" value="<?php echo htmlspecialchars($preferencia_editar["area"]); ?>" required><br><br>

                <label>Estado:</label><br>
                <select name="estado" required>
                    <option value="activo" <?php echo ($preferencia_editar["estado"] === "activo") ? "selected" : ""; ?>>
                        Activo
                    </option>
                    <option value="inactivo" <?php echo ($preferencia_editar["estado"] === "inactivo") ? "selected" : ""; ?>>
                        Inactivo
                    </option>
                </select><br><br>

                <p>
                    <strong>Creado el:</strong>
                    <?php echo htmlspecialchars($preferencia_editar["creado_el"]); ?>
                </p>

                <p>
                    <strong>Actualizado el:</strong>
                    <?php echo htmlspecialchars($preferencia_editar["actualizado_el"]); ?>
                </p>

            </fieldset>

            <br>

            <button type="submit">Actualizar preferencias</button>
            <a href="index.php">Cancelar edición</a>

        </form>

    <?php else: ?>

        <h2>2. Crear preferencias</h2>

        <form action="index.php" method="POST">

            <input type="hidden" name="accion" value="guardar">

            <fieldset>
                <legend>Registro de preferencias del usuario</legend>

                <label>Usuario:</label><br>
                <select name="id_usuario" required>
                    <option value="">Seleccione un usuario</option>

                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario["id"]; ?>">
                            <?php
                                echo htmlspecialchars(
                                    $usuario["nombres"] . " " .
                                    $usuario["apellido_paterno"] . " " .
                                    $usuario["apellido_materno"] .
                                    " (" . $usuario["usuario"] . ")"
                                );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Profesión:</label><br>
                <input type="text" name="profesion" required><br><br>

                <label>Área:</label><br>
                <input type="text" name="area" required><br><br>

            </fieldset>

            <br>

            <button type="submit">Guardar preferencias</button>
            <button type="reset">Limpiar</button>

        </form>

    <?php endif; ?>

    <hr>

    <h2>3. Leer preferencias registradas</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Profesión</th>
                <th>Área</th>
                <th>Estado</th>
                <th>Creado el</th>
                <th>Actualizado el</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($preferencias) > 0): ?>
                <?php foreach ($preferencias as $pref): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pref["id"]); ?></td>

                        <td>
                            <?php
                                echo htmlspecialchars(
                                    $pref["nombres"] . " " .
                                    $pref["apellido_paterno"] . " " .
                                    $pref["apellido_materno"]
                                );
                            ?>
                            <br>
                            <strong>Usuario:</strong>
                            <?php echo htmlspecialchars($pref["usuario"]); ?>
                        </td>

                        <td><?php echo htmlspecialchars($pref["profesion"]); ?></td>

                        <td><?php echo htmlspecialchars($pref["area"]); ?></td>

                        <td><?php echo htmlspecialchars($pref["estado"]); ?></td>

                        <td><?php echo htmlspecialchars($pref["creado_el"]); ?></td>

                        <td><?php echo htmlspecialchars($pref["actualizado_el"]); ?></td>

                        <td>
                            <a href="index.php?editar=<?php echo $pref["id"]; ?>">
                                Editar
                            </a>

                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_preferencia" value="<?php echo $pref["id"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No existen preferencias registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>