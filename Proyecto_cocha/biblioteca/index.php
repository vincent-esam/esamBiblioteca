<?php
/*
|--------------------------------------------------------------------------
| CONEXIÓN DIRECTA A LA BASE DE DATOS
|--------------------------------------------------------------------------
| Sin login, sin auth, sin sesiones.
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
| SUBIR PORTADA
|--------------------------------------------------------------------------
*/

function subir_portada($campo)
{
    if (!isset($_FILES[$campo]) || $_FILES[$campo]["error"] !== UPLOAD_ERR_OK) {
        return null;
    }

    $carpeta = "uploads/portadas/";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombre_original = $_FILES[$campo]["name"];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

    $permitidas = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($extension, $permitidas)) {
        return null;
    }

    $nuevo_nombre = "portada_" . time() . "_" . rand(1000, 9999) . "." . $extension;
    $ruta = $carpeta . $nuevo_nombre;

    if (move_uploaded_file($_FILES[$campo]["tmp_name"], $ruta)) {
        return $ruta;
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| OBTENER DATOS PARA LOS SELECT
|--------------------------------------------------------------------------
*/

$categorias = $pdo->query("SELECT id, categoria FROM categorias WHERE estado = 'activo' ORDER BY categoria ASC")->fetchAll(PDO::FETCH_ASSOC);

$autores = $pdo->query("SELECT id, nombre FROM autores WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$editoriales = $pdo->query("SELECT id, nombre FROM editoriales WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$idiomas = $pdo->query("SELECT id, nombre FROM idiomas WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$formatos = $pdo->query("SELECT id, formato FROM formatos WHERE estado = 'activo' ORDER BY formato ASC")->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ELIMINAR LIBRO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "eliminar") {
    $id_libro = $_POST["id_libro"] ?? "";

    if ($id_libro === "" || !is_numeric($id_libro)) {
        redirigir("No se pudo eliminar el libro porque el ID no es válido.", "error");
    }

    $sql = "DELETE FROM libros 
        WHERE id = :id_libro";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);

    if ($stmt->execute()) {
        redirigir("Libro eliminado correctamente.", "exito");
    } else {
        redirigir("No se pudo eliminar el libro.", "error");
    }
}

/*
|--------------------------------------------------------------------------
| GUARDAR LIBRO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "guardar") {
    $titulo = trim($_POST["titulo"] ?? "");
    $subtitulo = trim($_POST["subtitulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $isbn = trim($_POST["isbn"] ?? "");
    $fecha_registro = trim($_POST["fecha_registro"] ?? "");
    $edicion = trim($_POST["edicion"] ?? "");
    $numero_paginas = trim($_POST["numero_paginas"] ?? "");

    $id_categoria = trim($_POST["id_categoria"] ?? "");
    $id_autor = trim($_POST["id_autor"] ?? "");
    $id_editorial = trim($_POST["id_editorial"] ?? "");
    $id_idioma = trim($_POST["id_idioma"] ?? "");
    $id_formato = trim($_POST["id_formato"] ?? "");

    if (
        $titulo === "" ||
        $id_categoria === "" ||
        $id_autor === "" ||
        $id_editorial === "" ||
        $id_idioma === "" ||
        $id_formato === ""
    ) {
        redirigir("Debe completar todos los campos obligatorios.", "error");
    }

    $ruta_portada = subir_portada("ruta_portada");

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

    $stmt->bindParam(":titulo", $titulo);
    $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
    $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
    $stmt->bindParam(":ruta_portada", $ruta_portada);
    $stmt->bindParam(":descripcion", $descripcion);
    $stmt->bindParam(":isbn", $isbn);

    if ($fecha_registro === "") {
        $stmt->bindValue(":fecha_registro", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(":fecha_registro", $fecha_registro);
    }

    $stmt->bindParam(":subtitulo", $subtitulo);
    $stmt->bindValue(":id_editorial", $id_editorial, PDO::PARAM_INT);

    if ($edicion === "") {
        $stmt->bindValue(":edicion", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":edicion", $edicion, PDO::PARAM_INT);
    }

    if ($numero_paginas === "") {
        $stmt->bindValue(":numero_paginas", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":numero_paginas", $numero_paginas, PDO::PARAM_INT);
    }

    $stmt->bindValue(":id_idioma", $id_idioma, PDO::PARAM_INT);
    $stmt->bindValue(":id_formato", $id_formato, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $id_libro = $pdo->lastInsertId();

        $sql_autor = "INSERT INTO libros_autores (id_libro, id_autor)
                      VALUES (:id_libro, :id_autor)";

        $stmt_autor = $pdo->prepare($sql_autor);
        $stmt_autor->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
        $stmt_autor->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt_autor->execute();

        redirigir("Libro guardado correctamente.", "exito");
    } else {
        redirigir("No se pudo guardar el libro.", "error");
    }
}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR LIBRO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "actualizar") {
    $id_libro = $_POST["id_libro"] ?? "";

    $titulo = trim($_POST["titulo"] ?? "");
    $subtitulo = trim($_POST["subtitulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $isbn = trim($_POST["isbn"] ?? "");
    $fecha_registro = trim($_POST["fecha_registro"] ?? "");
    $edicion = trim($_POST["edicion"] ?? "");
    $numero_paginas = trim($_POST["numero_paginas"] ?? "");

    $id_categoria = trim($_POST["id_categoria"] ?? "");
    $id_autor = trim($_POST["id_autor"] ?? "");
    $id_editorial = trim($_POST["id_editorial"] ?? "");
    $id_idioma = trim($_POST["id_idioma"] ?? "");
    $id_formato = trim($_POST["id_formato"] ?? "");

    if (
        $id_libro === "" ||
        !is_numeric($id_libro) ||
        $titulo === "" ||
        $id_categoria === "" ||
        $id_autor === "" ||
        $id_editorial === "" ||
        $id_idioma === "" ||
        $id_formato === ""
    ) {
        redirigir("No se pudo actualizar el libro porque faltan datos obligatorios.", "error");
    }

    $sql_actual = "SELECT ruta_portada FROM libros WHERE id = :id_libro LIMIT 1";
    $stmt_actual = $pdo->prepare($sql_actual);
    $stmt_actual->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
    $stmt_actual->execute();

    $libro_actual = $stmt_actual->fetch(PDO::FETCH_ASSOC);

    $ruta_portada = $libro_actual["ruta_portada"] ?? null;

    $nueva_portada = subir_portada("ruta_portada");

    if ($nueva_portada !== null) {
        $ruta_portada = $nueva_portada;
    }

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
            WHERE id = :id_libro";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
    $stmt->bindParam(":titulo", $titulo);
    $stmt->bindValue(":id_categoria", $id_categoria, PDO::PARAM_INT);
    $stmt->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
    $stmt->bindParam(":ruta_portada", $ruta_portada);
    $stmt->bindParam(":descripcion", $descripcion);
    $stmt->bindParam(":isbn", $isbn);

    if ($fecha_registro === "") {
        $stmt->bindValue(":fecha_registro", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(":fecha_registro", $fecha_registro);
    }

    $stmt->bindParam(":subtitulo", $subtitulo);
    $stmt->bindValue(":id_editorial", $id_editorial, PDO::PARAM_INT);

    if ($edicion === "") {
        $stmt->bindValue(":edicion", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":edicion", $edicion, PDO::PARAM_INT);
    }

    if ($numero_paginas === "") {
        $stmt->bindValue(":numero_paginas", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":numero_paginas", $numero_paginas, PDO::PARAM_INT);
    }

    $stmt->bindValue(":id_idioma", $id_idioma, PDO::PARAM_INT);
    $stmt->bindValue(":id_formato", $id_formato, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $sql_delete_autor = "DELETE FROM libros_autores WHERE id_libro = :id_libro";
        $stmt_delete_autor = $pdo->prepare($sql_delete_autor);
        $stmt_delete_autor->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
        $stmt_delete_autor->execute();

        $sql_insert_autor = "INSERT INTO libros_autores (id_libro, id_autor)
                             VALUES (:id_libro, :id_autor)";

        $stmt_insert_autor = $pdo->prepare($sql_insert_autor);
        $stmt_insert_autor->bindValue(":id_libro", $id_libro, PDO::PARAM_INT);
        $stmt_insert_autor->bindValue(":id_autor", $id_autor, PDO::PARAM_INT);
        $stmt_insert_autor->execute();

        redirigir("Libro actualizado correctamente.", "exito");
    } else {
        redirigir("No se pudo actualizar el libro.", "error");
    }
}

/*
|--------------------------------------------------------------------------
| OBTENER LIBRO PARA EDITAR
|--------------------------------------------------------------------------
*/

$libro_editar = null;

if (isset($_GET["editar"]) && is_numeric($_GET["editar"])) {
    $id_editar = $_GET["editar"];

    $sql = "SELECT * FROM libros WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id", $id_editar, PDO::PARAM_INT);
    $stmt->execute();

    $libro_editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$libro_editar) {
        redirigir("El libro seleccionado no existe.", "error");
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR LIBROS
|--------------------------------------------------------------------------
*/

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
        WHERE l.estado = 'activo'
        ORDER BY l.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Libros</title>
</head>
<body>

    <h1>Gestión de Libros</h1>

    <hr>

    <h2>Mensajes del sistema</h2>

    <?php if ($mensaje !== ""): ?>
        <p>
            <strong>Tipo:</strong> <?php echo htmlspecialchars($tipo); ?><br>
            <strong>Mensaje:</strong> <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php else: ?>
        <p>No hay mensajes por el momento.</p>
    <?php endif; ?>

    <hr>

    <?php if ($libro_editar): ?>

        <h2>Editar libro</h2>

        <form action="index.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_libro" value="<?php echo htmlspecialchars($libro_editar["id"]); ?>">

            <label>Título:</label><br>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($libro_editar["titulo"]); ?>" required><br><br>

            <label>Subtítulo:</label><br>
            <input type="text" name="subtitulo" value="<?php echo htmlspecialchars($libro_editar["subtitulo"] ?? ""); ?>"><br><br>

            <label>Descripción:</label><br>
            <textarea name="descripcion" rows="4" cols="50"><?php echo htmlspecialchars($libro_editar["descripcion"] ?? ""); ?></textarea><br><br>

            <label>ISBN:</label><br>
            <input type="text" name="isbn" value="<?php echo htmlspecialchars($libro_editar["isbn"] ?? ""); ?>"><br><br>

            <label>Fecha de registro:</label><br>
            <input type="date" name="fecha_registro" value="<?php echo htmlspecialchars($libro_editar["fecha_registro"] ?? ""); ?>"><br><br>

            <label>Edición:</label><br>
            <input type="number" name="edicion" value="<?php echo htmlspecialchars($libro_editar["edicion"] ?? ""); ?>"><br><br>

            <label>Número de páginas:</label><br>
            <input type="number" name="numero_paginas" value="<?php echo htmlspecialchars($libro_editar["numero_paginas"] ?? ""); ?>"><br><br>

            <label>Portada actual:</label><br>
            <?php if (!empty($libro_editar["ruta_portada"])): ?>
                <img src="<?php echo htmlspecialchars($libro_editar["ruta_portada"]); ?>" width="80" alt="Portada actual"><br><br>
            <?php else: ?>
                <p>Sin portada.</p>
            <?php endif; ?>

            <label>Cambiar portada:</label><br>
            <input type="file" name="ruta_portada" accept="image/*"><br><br>

            <label>Categoría:</label><br>
            <select name="id_categoria" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria["id"]; ?>" <?php echo ($libro_editar["id_categoria"] == $categoria["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($categoria["categoria"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Autor:</label><br>
            <select name="id_autor" required>
                <option value="">Seleccione un autor</option>
                <?php foreach ($autores as $autor): ?>
                    <option value="<?php echo $autor["id"]; ?>" <?php echo ($libro_editar["id_autor"] == $autor["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($autor["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Editorial:</label><br>
            <select name="id_editorial" required>
                <option value="">Seleccione una editorial</option>
                <?php foreach ($editoriales as $editorial): ?>
                    <option value="<?php echo $editorial["id"]; ?>" <?php echo ($libro_editar["id_editorial"] == $editorial["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($editorial["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Idioma:</label><br>
            <select name="id_idioma" required>
                <option value="">Seleccione un idioma</option>
                <?php foreach ($idiomas as $idioma): ?>
                    <option value="<?php echo $idioma["id"]; ?>" <?php echo ($libro_editar["id_idioma"] == $idioma["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($idioma["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Formato:</label><br>
            <select name="id_formato" required>
                <option value="">Seleccione un formato</option>
                <?php foreach ($formatos as $formato): ?>
                    <option value="<?php echo $formato["id"]; ?>" <?php echo ($libro_editar["id_formato"] == $formato["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($formato["formato"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Actualizar libro</button>
            <a href="index.php">Cancelar edición</a>

        </form>

    <?php else: ?>

        <h2>Agregar libro</h2>

        <form action="index.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="accion" value="guardar">

            <label>Título:</label><br>
            <input type="text" name="titulo" required><br><br>

            <label>Subtítulo:</label><br>
            <input type="text" name="subtitulo"><br><br>

            <label>Descripción:</label><br>
            <textarea name="descripcion" rows="4" cols="50"></textarea><br><br>

            <label>ISBN:</label><br>
            <input type="text" name="isbn"><br><br>

            <label>Fecha de registro:</label><br>
            <input type="date" name="fecha_registro"><br><br>

            <label>Edición:</label><br>
            <input type="number" name="edicion"><br><br>

            <label>Número de páginas:</label><br>
            <input type="number" name="numero_paginas"><br><br>

            <label>Portada:</label><br>
            <input type="file" name="ruta_portada" accept="image/*"><br><br>

            <label>Categoría:</label><br>
            <select name="id_categoria" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria["id"]; ?>">
                        <?php echo htmlspecialchars($categoria["categoria"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Autor:</label><br>
            <select name="id_autor" required>
                <option value="">Seleccione un autor</option>
                <?php foreach ($autores as $autor): ?>
                    <option value="<?php echo $autor["id"]; ?>">
                        <?php echo htmlspecialchars($autor["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Editorial:</label><br>
            <select name="id_editorial" required>
                <option value="">Seleccione una editorial</option>
                <?php foreach ($editoriales as $editorial): ?>
                    <option value="<?php echo $editorial["id"]; ?>">
                        <?php echo htmlspecialchars($editorial["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Idioma:</label><br>
            <select name="id_idioma" required>
                <option value="">Seleccione un idioma</option>
                <?php foreach ($idiomas as $idioma): ?>
                    <option value="<?php echo $idioma["id"]; ?>">
                        <?php echo htmlspecialchars($idioma["nombre"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Formato:</label><br>
            <select name="id_formato" required>
                <option value="">Seleccione un formato</option>
                <?php foreach ($formatos as $formato): ?>
                    <option value="<?php echo $formato["id"]; ?>">
                        <?php echo htmlspecialchars($formato["formato"]); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Guardar libro</button>
            <button type="reset">Limpiar</button>

        </form>

    <?php endif; ?>

    <hr>

    <h2>Listado de libros</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Portada</th>
                <th>Título</th>
                <th>Subtítulo</th>
                <th>Categoría</th>
                <th>Autor</th>
                <th>Editorial</th>
                <th>Idioma</th>
                <th>Formato</th>
                <th>ISBN</th>
                <th>Edición</th>
                <th>Páginas</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($libros) > 0): ?>
                <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($libro["id"]); ?></td>

                        <td>
                            <?php if (!empty($libro["ruta_portada"])): ?>
                                <img src="<?php echo htmlspecialchars($libro["ruta_portada"]); ?>" width="60" alt="Portada">
                            <?php else: ?>
                                Sin portada
                            <?php endif; ?>
                        </td>

                        <td><?php echo htmlspecialchars($libro["titulo"]); ?></td>
                        <td><?php echo htmlspecialchars($libro["subtitulo"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($libro["categoria"]); ?></td>
                        <td><?php echo htmlspecialchars($libro["autor"]); ?></td>
                        <td><?php echo htmlspecialchars($libro["editorial"]); ?></td>
                        <td><?php echo htmlspecialchars($libro["idioma"]); ?></td>
                        <td><?php echo htmlspecialchars($libro["formato"]); ?></td>
                        <td><?php echo htmlspecialchars($libro["isbn"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($libro["edicion"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($libro["numero_paginas"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($libro["fecha_registro"] ?? ""); ?></td>

                        <td>
                            <a href="index.php?editar=<?php echo $libro["id"]; ?>">Editar</a>

                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_libro" value="<?php echo $libro["id"]; ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="14">No existen libros registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>