<?php
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

function validar_tipo_autor($tipo_autor)
{
    $tipos_permitidos = ["personal", "corporativo", "institucional"];
    return in_array($tipo_autor, $tipos_permitidos);
}

/*
|--------------------------------------------------------------------------
| CREAR AUTOR
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "guardar") {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido_paterno = trim($_POST["apellido_paterno"] ?? "");
    $apellido_materno = trim($_POST["apellido_materno"] ?? "");
    $seudonimo = trim($_POST["seudonimo"] ?? "");
    $tipo_autor = trim($_POST["tipo_autor"] ?? "personal");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $nacionalidad = trim($_POST["nacionalidad"] ?? "");
    $fecha_nacimiento = trim($_POST["fecha_nacimiento"] ?? "");
    $grado_academico = trim($_POST["grado_academico"] ?? "");
    $especialidad = trim($_POST["especialidad"] ?? "");
    $institucion = trim($_POST["institucion"] ?? "");
    $orcid = trim($_POST["orcid"] ?? "");
    $sitio_web = trim($_POST["sitio_web"] ?? "");
    $biografia = trim($_POST["biografia"] ?? "");

    if ($nombre === "") {
        redirigir("Debe ingresar el nombre del autor.", "error");
    }

    if (!validar_tipo_autor($tipo_autor)) {
        redirigir("El tipo de autor seleccionado no es válido.", "error");
    }

    if ($correo !== "" && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        redirigir("El correo electrónico no tiene un formato válido.", "error");
    }

    if ($sitio_web !== "" && !filter_var($sitio_web, FILTER_VALIDATE_URL)) {
        redirigir("El sitio web no tiene un formato válido. Debe iniciar con http:// o https://", "error");
    }

    try {
        $sql = "INSERT INTO autores (
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
                    estado
                ) VALUES (
                    :nombre,
                    :apellido_paterno,
                    :apellido_materno,
                    :seudonimo,
                    :tipo_autor,
                    :correo,
                    :telefono,
                    :nacionalidad,
                    :fecha_nacimiento,
                    :grado_academico,
                    :especialidad,
                    :institucion,
                    :orcid,
                    :sitio_web,
                    :biografia,
                    'activo'
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido_paterno", $apellido_paterno);
        $stmt->bindParam(":apellido_materno", $apellido_materno);
        $stmt->bindParam(":seudonimo", $seudonimo);
        $stmt->bindParam(":tipo_autor", $tipo_autor);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":nacionalidad", $nacionalidad);

        if ($fecha_nacimiento === "") {
            $stmt->bindValue(":fecha_nacimiento", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        }

        $stmt->bindParam(":grado_academico", $grado_academico);
        $stmt->bindParam(":especialidad", $especialidad);
        $stmt->bindParam(":institucion", $institucion);
        $stmt->bindParam(":orcid", $orcid);
        $stmt->bindParam(":sitio_web", $sitio_web);
        $stmt->bindParam(":biografia", $biografia);

        if ($stmt->execute()) {
            redirigir("Autor registrado correctamente.", "exito");
        } else {
            redirigir("No se pudo registrar el autor.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al registrar el autor: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR AUTOR
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "actualizar") {
    $id_autor = $_POST["id_autor"] ?? "";

    $nombre = trim($_POST["nombre"] ?? "");
    $apellido_paterno = trim($_POST["apellido_paterno"] ?? "");
    $apellido_materno = trim($_POST["apellido_materno"] ?? "");
    $seudonimo = trim($_POST["seudonimo"] ?? "");
    $tipo_autor = trim($_POST["tipo_autor"] ?? "personal");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $nacionalidad = trim($_POST["nacionalidad"] ?? "");
    $fecha_nacimiento = trim($_POST["fecha_nacimiento"] ?? "");
    $grado_academico = trim($_POST["grado_academico"] ?? "");
    $especialidad = trim($_POST["especialidad"] ?? "");
    $institucion = trim($_POST["institucion"] ?? "");
    $orcid = trim($_POST["orcid"] ?? "");
    $sitio_web = trim($_POST["sitio_web"] ?? "");
    $biografia = trim($_POST["biografia"] ?? "");
    $estado = trim($_POST["estado"] ?? "activo");

    if ($id_autor === "" || !is_numeric($id_autor)) {
        redirigir("El ID del autor no es válido.", "error");
    }

    if ($nombre === "") {
        redirigir("Debe ingresar el nombre del autor.", "error");
    }

    if (!validar_tipo_autor($tipo_autor)) {
        redirigir("El tipo de autor seleccionado no es válido.", "error");
    }

    if ($estado !== "activo" && $estado !== "inactivo") {
        redirigir("El estado seleccionado no es válido.", "error");
    }

    if ($correo !== "" && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        redirigir("El correo electrónico no tiene un formato válido.", "error");
    }

    if ($sitio_web !== "" && !filter_var($sitio_web, FILTER_VALIDATE_URL)) {
        redirigir("El sitio web no tiene un formato válido. Debe iniciar con http:// o https://", "error");
    }

    try {
        $sql = "UPDATE autores SET
                    nombre = :nombre,
                    apellido_paterno = :apellido_paterno,
                    apellido_materno = :apellido_materno,
                    seudonimo = :seudonimo,
                    tipo_autor = :tipo_autor,
                    correo = :correo,
                    telefono = :telefono,
                    nacionalidad = :nacionalidad,
                    fecha_nacimiento = :fecha_nacimiento,
                    grado_academico = :grado_academico,
                    especialidad = :especialidad,
                    institucion = :institucion,
                    orcid = :orcid,
                    sitio_web = :sitio_web,
                    biografia = :biografia,
                    estado = :estado
                WHERE id = :id_autor";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido_paterno", $apellido_paterno);
        $stmt->bindParam(":apellido_materno", $apellido_materno);
        $stmt->bindParam(":seudonimo", $seudonimo);
        $stmt->bindParam(":tipo_autor", $tipo_autor);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":nacionalidad", $nacionalidad);

        if ($fecha_nacimiento === "") {
            $stmt->bindValue(":fecha_nacimiento", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        }

        $stmt->bindParam(":grado_academico", $grado_academico);
        $stmt->bindParam(":especialidad", $especialidad);
        $stmt->bindParam(":institucion", $institucion);
        $stmt->bindParam(":orcid", $orcid);
        $stmt->bindParam(":sitio_web", $sitio_web);
        $stmt->bindParam(":biografia", $biografia);
        $stmt->bindParam(":estado", $estado);

        if ($stmt->execute()) {
            redirigir("Autor actualizado correctamente.", "exito");
        } else {
            redirigir("No se pudo actualizar el autor.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al actualizar el autor: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| ELIMINAR AUTOR
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "eliminar") {
    $id_autor = $_POST["id_autor"] ?? "";

    if ($id_autor === "" || !is_numeric($id_autor)) {
        redirigir("El ID del autor no es válido.", "error");
    }

    try {
        $sql = "UPDATE autores
                SET estado = 'inactivo'
                WHERE id = :id_autor";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);

        if ($stmt->execute()) {
            redirigir("Autor eliminado correctamente.", "exito");
        } else {
            redirigir("No se pudo eliminar el autor.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al eliminar el autor: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| OBTENER AUTOR PARA EDITAR
|--------------------------------------------------------------------------
*/

$autor_editar = null;

if (isset($_GET["editar"]) && is_numeric($_GET["editar"])) {
    $id_editar = $_GET["editar"];

    try {
        $sql = "SELECT *
                FROM autores
                WHERE id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $id_editar, PDO::PARAM_INT);
        $stmt->execute();

        $autor_editar = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$autor_editar) {
            redirigir("El autor seleccionado no existe.", "error");
        }

    } catch (PDOException $e) {
        redirigir("Error al obtener el autor: " . $e->getMessage(), "error");
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR AUTORES ACTIVOS
|--------------------------------------------------------------------------
*/

try {
    $sql = "SELECT *
            FROM autores
            WHERE estado = 'activo'
            ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $autores = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al listar autores: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Autores</title>
</head>
<body>

    <h1>Gestión de Autores</h1>

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

    <?php if ($autor_editar): ?>

        <h2>2. Editar autor</h2>

        <form action="index.php" method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_autor" value="<?php echo htmlspecialchars($autor_editar["id"]); ?>">

            <fieldset>
                <legend>2.1 Identificación del autor</legend>

                <label>Nombre:</label><br>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($autor_editar["nombre"] ?? ""); ?>" required><br><br>

                <label>Apellido paterno:</label><br>
                <input type="text" name="apellido_paterno" value="<?php echo htmlspecialchars($autor_editar["apellido_paterno"] ?? ""); ?>"><br><br>

                <label>Apellido materno:</label><br>
                <input type="text" name="apellido_materno" value="<?php echo htmlspecialchars($autor_editar["apellido_materno"] ?? ""); ?>"><br><br>

                <label>Seudónimo:</label><br>
                <input type="text" name="seudonimo" value="<?php echo htmlspecialchars($autor_editar["seudonimo"] ?? ""); ?>"><br><br>

                <label>Tipo de autor:</label><br>
                <select name="tipo_autor">
                    <option value="personal" <?php echo ($autor_editar["tipo_autor"] === "personal") ? "selected" : ""; ?>>Personal</option>
                    <option value="corporativo" <?php echo ($autor_editar["tipo_autor"] === "corporativo") ? "selected" : ""; ?>>Corporativo</option>
                    <option value="institucional" <?php echo ($autor_editar["tipo_autor"] === "institucional") ? "selected" : ""; ?>>Institucional</option>
                </select><br><br>
            </fieldset>

            <br>

            <fieldset>
                <legend>2.2 Contacto y datos personales</legend>

                <label>Correo:</label><br>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($autor_editar["correo"] ?? ""); ?>"><br><br>

                <label>Teléfono:</label><br>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($autor_editar["telefono"] ?? ""); ?>"><br><br>

                <label>Nacionalidad:</label><br>
                <input type="text" name="nacionalidad" value="<?php echo htmlspecialchars($autor_editar["nacionalidad"] ?? ""); ?>"><br><br>

                <label>Fecha de nacimiento:</label><br>
                <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($autor_editar["fecha_nacimiento"] ?? ""); ?>"><br><br>
            </fieldset>

            <br>

            <fieldset>
                <legend>2.3 Información académica o institucional</legend>

                <label>Grado académico:</label><br>
                <input type="text" name="grado_academico" value="<?php echo htmlspecialchars($autor_editar["grado_academico"] ?? ""); ?>"><br><br>

                <label>Especialidad:</label><br>
                <input type="text" name="especialidad" value="<?php echo htmlspecialchars($autor_editar["especialidad"] ?? ""); ?>"><br><br>

                <label>Institución:</label><br>
                <input type="text" name="institucion" value="<?php echo htmlspecialchars($autor_editar["institucion"] ?? ""); ?>"><br><br>

                <label>ORCID:</label><br>
                <input type="text" name="orcid" value="<?php echo htmlspecialchars($autor_editar["orcid"] ?? ""); ?>"><br><br>

                <label>Sitio web:</label><br>
                <input type="url" name="sitio_web" value="<?php echo htmlspecialchars($autor_editar["sitio_web"] ?? ""); ?>"><br><br>
            </fieldset>

            <br>

            <fieldset>
                <legend>2.4 Biografía y estado</legend>

                <label>Biografía:</label><br>
                <textarea name="biografia" rows="5" cols="70"><?php echo htmlspecialchars($autor_editar["biografia"] ?? ""); ?></textarea><br><br>

                <label>Estado:</label><br>
                <select name="estado">
                    <option value="activo" <?php echo ($autor_editar["estado"] === "activo") ? "selected" : ""; ?>>Activo</option>
                    <option value="inactivo" <?php echo ($autor_editar["estado"] === "inactivo") ? "selected" : ""; ?>>Inactivo</option>
                </select><br><br>

                <p>
                    <strong>Creado el:</strong>
                    <?php echo htmlspecialchars($autor_editar["creado_el"]); ?>
                </p>

                <p>
                    <strong>Actualizado el:</strong>
                    <?php echo htmlspecialchars($autor_editar["actualizado_el"]); ?>
                </p>
            </fieldset>

            <br>

            <button type="submit">Actualizar autor</button>
            <a href="index.php">Cancelar edición</a>
        </form>

    <?php else: ?>

        <h2>2. Crear autor</h2>

        <form action="index.php" method="POST">
            <input type="hidden" name="accion" value="guardar">

            <fieldset>
                <legend>2.1 Identificación del autor</legend>

                <label>Nombre:</label><br>
                <input type="text" name="nombre" required><br><br>

                <label>Apellido paterno:</label><br>
                <input type="text" name="apellido_paterno"><br><br>

                <label>Apellido materno:</label><br>
                <input type="text" name="apellido_materno"><br><br>

                <label>Seudónimo:</label><br>
                <input type="text" name="seudonimo"><br><br>

                <label>Tipo de autor:</label><br>
                <select name="tipo_autor">
                    <option value="personal">Personal</option>
                    <option value="corporativo">Corporativo</option>
                    <option value="institucional">Institucional</option>
                </select><br><br>
            </fieldset>

            <br>

            <fieldset>
                <legend>2.2 Contacto y datos personales</legend>

                <label>Correo:</label><br>
                <input type="email" name="correo"><br><br>

                <label>Teléfono:</label><br>
                <input type="text" name="telefono"><br><br>

                <label>Nacionalidad:</label><br>
                <input type="text" name="nacionalidad"><br><br>

                <label>Fecha de nacimiento:</label><br>
                <input type="date" name="fecha_nacimiento"><br><br>
            </fieldset>

            <br>

            <fieldset>
                <legend>2.3 Información académica o institucional</legend>

                <label>Grado académico:</label><br>
                <input type="text" name="grado_academico"><br><br>

                <label>Especialidad:</label><br>
                <input type="text" name="especialidad"><br><br>

                <label>Institución:</label><br>
                <input type="text" name="institucion"><br><br>

                <label>ORCID:</label><br>
                <input type="text" name="orcid"><br><br>

                <label>Sitio web:</label><br>
                <input type="url" name="sitio_web"><br><br>
            </fieldset>

            <br>

            <fieldset>
                <legend>2.4 Biografía</legend>

                <label>Biografía:</label><br>
                <textarea name="biografia" rows="5" cols="70"></textarea><br><br>
            </fieldset>

            <br>

            <button type="submit">Guardar autor</button>
            <button type="reset">Limpiar</button>
        </form>

    <?php endif; ?>

    <hr>

    <h2>3. Mostrar autores registrados</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Identificación</th>
                <th>Contacto</th>
                <th>Información académica</th>
                <th>Biografía</th>
                <th>Fechas</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($autores) > 0): ?>
                <?php foreach ($autores as $autor): ?>
                    <tr>
                        <td>
                            <strong>ID:</strong> <?php echo htmlspecialchars($autor["id"]); ?><br>
                            <strong>Nombre:</strong> <?php echo htmlspecialchars($autor["nombre"]); ?><br>
                            <strong>Apellido paterno:</strong> <?php echo htmlspecialchars($autor["apellido_paterno"] ?? ""); ?><br>
                            <strong>Apellido materno:</strong> <?php echo htmlspecialchars($autor["apellido_materno"] ?? ""); ?><br>
                            <strong>Seudónimo:</strong> <?php echo htmlspecialchars($autor["seudonimo"] ?? ""); ?><br>
                            <strong>Tipo autor:</strong> <?php echo htmlspecialchars($autor["tipo_autor"] ?? ""); ?><br>
                            <strong>Estado:</strong> <?php echo htmlspecialchars($autor["estado"]); ?>
                        </td>

                        <td>
                            <strong>Correo:</strong> <?php echo htmlspecialchars($autor["correo"] ?? ""); ?><br>
                            <strong>Teléfono:</strong> <?php echo htmlspecialchars($autor["telefono"] ?? ""); ?><br>
                            <strong>Nacionalidad:</strong> <?php echo htmlspecialchars($autor["nacionalidad"] ?? ""); ?><br>
                            <strong>Fecha nacimiento:</strong> <?php echo htmlspecialchars($autor["fecha_nacimiento"] ?? ""); ?>
                        </td>

                        <td>
                            <strong>Grado académico:</strong> <?php echo htmlspecialchars($autor["grado_academico"] ?? ""); ?><br>
                            <strong>Especialidad:</strong> <?php echo htmlspecialchars($autor["especialidad"] ?? ""); ?><br>
                            <strong>Institución:</strong> <?php echo htmlspecialchars($autor["institucion"] ?? ""); ?><br>
                            <strong>ORCID:</strong> <?php echo htmlspecialchars($autor["orcid"] ?? ""); ?><br>
                            <strong>Sitio web:</strong> <?php echo htmlspecialchars($autor["sitio_web"] ?? ""); ?>
                        </td>

                        <td>
                            <?php echo nl2br(htmlspecialchars($autor["biografia"] ?? "")); ?>
                        </td>

                        <td>
                            <strong>Creado:</strong><br>
                            <?php echo htmlspecialchars($autor["creado_el"]); ?><br><br>

                            <strong>Actualizado:</strong><br>
                            <?php echo htmlspecialchars($autor["actualizado_el"]); ?>
                        </td>

                        <td>
                            <a href="index.php?editar=<?php echo $autor["id"]; ?>">Editar</a>

                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_autor" value="<?php echo $autor["id"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No existen autores registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>