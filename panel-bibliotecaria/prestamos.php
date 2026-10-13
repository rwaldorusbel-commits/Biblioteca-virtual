<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';

$mensaje = '';
$error = '';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['token_prestamos'])) {
    $_SESSION['token_prestamos'] = bin2hex(random_bytes(32));
}

function h(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function fechaLegible(?string $fecha): string
{
    if (!$fecha) {
        return '—';
    }

    $tiempo = strtotime($fecha);

    return $tiempo ? date('d/m/Y', $tiempo) : $fecha;
}

/*
|--------------------------------------------------------------------------
| Registrar un préstamo o procesar una devolución
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['token'] ?? '');

    if (!hash_equals($_SESSION['token_prestamos'], $token)) {
        $error = 'La solicitud no es válida. Recarga la página e inténtalo nuevamente.';
    } else {
        $accion = (string) ($_POST['accion'] ?? '');

        try {
            if ($accion === 'registrar') {
                $idUsuario = filter_var(
                    $_POST['id_usuario'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $idLibro = filter_var(
                    $_POST['id_libro'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $fechaEsperada = (string) ($_POST['fecha_esperada'] ?? '');
                $hoy = date('Y-m-d');

                $fechaValida = DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $fechaEsperada
                );

                if (
                    !$idUsuario || !$idLibro ||
                    !$fechaValida ||
                    $fechaValida->format('Y-m-d') !== $fechaEsperada ||
                    $fechaEsperada < $hoy
                ) {
                    throw new RuntimeException(
                        'Selecciona un usuario, un libro y una fecha de devolución válida, igual o posterior a hoy.'
                    );
                }

                $conexion->beginTransaction();

                $consultaUsuario = $conexion->prepare(
                    "SELECT id, nombre, rol, estado
                     FROM usuarios
                     WHERE id = ?
                     FOR UPDATE"
                );
                $consultaUsuario->execute([$idUsuario]);
                $usuario = $consultaUsuario->fetch();

                if (!$usuario) {
                    throw new RuntimeException('El usuario seleccionado no existe.');
                }

                if ($usuario['estado'] !== 'activo') {
                    throw new RuntimeException('El usuario no está activo.');
                }

                if (!in_array(
                    $usuario['rol'],
                    ['estudiante', 'bibliotecario', 'administrador'],
                    true
                )) {
                    throw new RuntimeException('El rol del usuario no es válido.');
                }

                $consultaLibro = $conexion->prepare(
                    "SELECT id, titulo, stock_total, stock_disponible
                     FROM libros
                     WHERE id = ?
                     FOR UPDATE"
                );
                $consultaLibro->execute([$idLibro]);
                $libro = $consultaLibro->fetch();

                if (!$libro) {
                    throw new RuntimeException('El libro seleccionado no existe.');
                }

                if ((int) $libro['stock_disponible'] < 1) {
                    throw new RuntimeException('No hay ejemplares disponibles de ese libro.');
                }

                $consultaDuplicado = $conexion->prepare(
                    "SELECT COUNT(*)
                     FROM prestamos
                     WHERE id_usuario = ?
                       AND id_libro = ?
                       AND estado IN ('activo', 'vencido')"
                );
                $consultaDuplicado->execute([$idUsuario, $idLibro]);

                if ((int) $consultaDuplicado->fetchColumn() > 0) {
                    throw new RuntimeException(
                        'Este usuario ya tiene un préstamo activo de ese libro.'
                    );
                }

                $insertar = $conexion->prepare(
                    "INSERT INTO prestamos
                        (id_usuario, id_libro, fecha_prestamo,
                         fecha_devolucion_esperada, estado, created_at, updated_at)
                     VALUES (?, ?, ?, ?, 'activo', NOW(), NOW())"
                );

                $insertar->execute([
                    $idUsuario,
                    $idLibro,
                    $hoy,
                    $fechaEsperada
                ]);

                $actualizarStock = $conexion->prepare(
                    "UPDATE libros
                     SET stock_disponible = stock_disponible - 1,
                         updated_at = NOW()
                     WHERE id = ?
                       AND stock_disponible > 0"
                );
                $actualizarStock->execute([$idLibro]);

                if ($actualizarStock->rowCount() !== 1) {
                    throw new RuntimeException(
                        'No se pudo actualizar el stock disponible.'
                    );
                }

                $conexion->commit();

                $mensaje = 'Préstamo registrado correctamente. Se descontó un ejemplar del stock.';
            } elseif ($accion === 'devolver') {
                $idPrestamo = filter_var(
                    $_POST['id_prestamo'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$idPrestamo) {
                    throw new RuntimeException('Selecciona un préstamo válido.');
                }

                $conexion->beginTransaction();

                $consultaPrestamo = $conexion->prepare(
                    "SELECT id, id_libro, estado
                     FROM prestamos
                     WHERE id = ?
                     FOR UPDATE"
                );
                $consultaPrestamo->execute([$idPrestamo]);
                $prestamo = $consultaPrestamo->fetch();

                if (!$prestamo) {
                    throw new RuntimeException('El préstamo no existe.');
                }

                if (!in_array($prestamo['estado'], ['activo', 'vencido'], true)) {
                    throw new RuntimeException(
                        'Este préstamo ya fue devuelto o no admite devoluciones.'
                    );
                }

                $consultaLibro = $conexion->prepare(
                    "SELECT id, stock_total, stock_disponible
                     FROM libros
                     WHERE id = ?
                     FOR UPDATE"
                );
                $consultaLibro->execute([$prestamo['id_libro']]);
                $libro = $consultaLibro->fetch();

                if (!$libro) {
                    throw new RuntimeException(
                        'No se encontró el libro asociado al préstamo.'
                    );
                }

                if (
                    (int) $libro['stock_disponible'] >=
                    (int) $libro['stock_total']
                ) {
                    throw new RuntimeException(
                        'El stock ya está completo. Revisa este registro antes de devolverlo.'
                    );
                }

                $devolver = $conexion->prepare(
                    "UPDATE prestamos
                     SET fecha_devolucion_real = CURDATE(),
                         estado = 'devuelto',
                         updated_at = NOW()
                     WHERE id = ?
                       AND estado IN ('activo', 'vencido')"
                );
                $devolver->execute([$idPrestamo]);

                if ($devolver->rowCount() !== 1) {
                    throw new RuntimeException('No se pudo registrar la devolución.');
                }

                $reponerStock = $conexion->prepare(
                    "UPDATE libros
                     SET stock_disponible = stock_disponible + 1,
                         updated_at = NOW()
                     WHERE id = ?
                       AND stock_disponible < stock_total"
                );
                $reponerStock->execute([$prestamo['id_libro']]);

                if ($reponerStock->rowCount() !== 1) {
                    throw new RuntimeException('No se pudo reponer el stock.');
                }

                $conexion->commit();

                $mensaje = 'Devolución registrada correctamente. Se repuso un ejemplar al stock.';
            } else {
                throw new RuntimeException('La acción solicitada no es válida.');
            }
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            if ($e instanceof RuntimeException) {
                $error = $e->getMessage();
            } else {
                error_log('Error en préstamos BiblioTech: ' . $e->getMessage());
                $error = 'Ocurrió un error al procesar la operación. Revisa los registros del servidor.';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Datos para los formularios y el listado
|--------------------------------------------------------------------------
*/

$usuarios = $conexion->query(
    "SELECT id, nombre, email, rol
     FROM usuarios
     WHERE estado = 'activo'
       AND rol = 'estudiante'
     ORDER BY nombre"
)->fetchAll();

$libros = $conexion->query(
    "SELECT id, titulo, autor, stock_disponible
     FROM libros
     ORDER BY titulo"
)->fetchAll();

$prestamos = $conexion->query(
    "SELECT
        p.id,
        u.nombre AS usuario,
        u.email,
        l.titulo AS libro,
        p.fecha_prestamo,
        p.fecha_devolucion_esperada,
        p.fecha_devolucion_real,
        CASE
            WHEN p.estado = 'activo'
                 AND p.fecha_devolucion_esperada < CURDATE()
                THEN 'vencido'
            ELSE p.estado
        END AS estado_mostrado
     FROM prestamos p
     INNER JOIN usuarios u ON u.id = p.id_usuario
     INNER JOIN libros l ON l.id = p.id_libro
     ORDER BY
        CASE WHEN p.estado IN ('activo', 'vencido') THEN 0 ELSE 1 END,
        p.id DESC"
)->fetchAll();

$hoy = date('Y-m-d');
$fechaPredeterminada = date('Y-m-d', strtotime('+14 days'));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos y devoluciones | BiblioTech</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #f3f6fb;
            color: #172033;
            font-family: Arial, sans-serif;
        }
        .contenedor { max-width: 1250px; margin: auto; }
        .encabezado, .panel {
            background: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 4px 18px #14213d0b;
        }
        h1 { margin: 0 0 8px; }
        h2 { margin-top: 0; }
        .subtitulo { color: #64748b; }
        .rejilla {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin: 14px 0 6px;
        }
        select, input, button {
            width: 100%;
            padding: 11px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }
        button {
            margin-top: 16px;
            border: 0;
            background: #2457d6;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .boton-devolver { background: #16804a; margin: 0; }
        .mensaje, .error {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .mensaje { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .tabla-contenedor { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        th { background: #f8fafc; }
        .estado {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #e2e8f0;
            font-size: 12px;
            font-weight: bold;
        }
        .activo { background: #dbeafe; color: #1d4ed8; }
        .vencido { background: #fee2e2; color: #b91c1c; }
        .devuelto { background: #dcfce7; color: #166534; }
        .cancelado { background: #f1f5f9; color: #475569; }
        .enlace { color: #2457d6; text-decoration: none; }
        .nota { font-size: 13px; color: #64748b; }
        @media (max-width: 800px) {
            body { padding: 12px; }
            .rejilla { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="contenedor">
    <header class="encabezado">
        <h1>Préstamos y devoluciones</h1>
        <p class="subtitulo">BiblioTech · Administración de préstamos</p>
        <a class="enlace" href="index.php">← Volver al panel principal</a>
    </header>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje"><?= h($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="rejilla">
        <section class="panel">
            <h2>Registrar préstamo</h2>
            <p class="nota">
                Se descuenta una unidad del stock al registrar el préstamo.
            </p>

            <form method="post">
                <input type="hidden" name="token"
                       value="<?= h($_SESSION['token_prestamos']) ?>">
                <input type="hidden" name="accion" value="registrar">

                <label for="id_usuario">Usuario</label>
                <select id="id_usuario" name="id_usuario" required>
                    <option value="">Selecciona un usuario activo</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= (int) $usuario['id'] ?>">
                            <?= h($usuario['nombre']) ?>
                            — <?= h($usuario['email']) ?>
                            (<?= h($usuario['rol']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_libro">Libro</label>
                <select id="id_libro" name="id_libro" required>
                    <option value="">Selecciona un libro</option>
                    <?php foreach ($libros as $libro): ?>
                        <option
                            value="<?= (int) $libro['id'] ?>"
                            <?= (int) $libro['stock_disponible'] < 1 ? 'disabled' : '' ?>
                        >
                            <?= h($libro['titulo']) ?>
                            — disponibles: <?= (int) $libro['stock_disponible'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="fecha_esperada">Fecha de devolución esperada</label>
                <input
                    id="fecha_esperada"
                    type="date"
                    name="fecha_esperada"
                    min="<?= h($hoy) ?>"
                    value="<?= h($fechaPredeterminada) ?>"
                    required
                >

                <button type="submit">Registrar préstamo</button>
            </form>
        </section>

        <section class="panel">
            <h2>Resumen</h2>
            <p>Préstamos registrados: <strong><?= count($prestamos) ?></strong></p>
            <p>Usuarios activos disponibles: <strong><?= count($usuarios) ?></strong></p>
            <p>Libros en catálogo: <strong><?= count($libros) ?></strong></p>
            <p class="nota">
                La fecha predeterminada es de 14 días desde hoy.
                Puedes cambiarla antes de registrar el préstamo.
            </p>
            <p class="nota">
                Las devoluciones no generan multas automáticamente.
                La tarifa de multa debe confirmarse según las reglas de la biblioteca.
            </p>
        </section>
    </div>

    <section class="panel">
        <h2>Listado de préstamos</h2>
        <div class="tabla-contenedor">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Libro</th>
                    <th>Fecha de préstamo</th>
                    <th>Devolución esperada</th>
                    <th>Devolución real</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$prestamos): ?>
                    <tr>
                        <td colspan="8">Todavía no hay préstamos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($prestamos as $p): ?>
                        <tr>
                            <td><?= (int) $p['id'] ?></td>
                            <td>
                                <?= h($p['usuario']) ?><br>
                                <span class="nota"><?= h($p['email']) ?></span>
                            </td>
                            <td><?= h($p['libro']) ?></td>
                            <td><?= h(fechaLegible($p['fecha_prestamo'])) ?></td>
                            <td><?= h(fechaLegible($p['fecha_devolucion_esperada'])) ?></td>
                            <td><?= h(fechaLegible($p['fecha_devolucion_real'])) ?></td>
                            <td>
                                <span class="estado <?= h($p['estado_mostrado']) ?>">
                                    <?= h(ucfirst($p['estado_mostrado'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (in_array($p['estado_mostrado'], ['activo', 'vencido'], true)): ?>
                                    <form method="post"
                                          onsubmit="return confirm('¿Confirmas que recibiste el libro y deseas registrar la devolución?');">
                                        <input type="hidden" name="token"
                                               value="<?= h($_SESSION['token_prestamos']) ?>">
                                        <input type="hidden" name="accion" value="devolver">
                                        <input type="hidden" name="id_prestamo"
                                               value="<?= (int) $p['id'] ?>">
                                        <button class="boton-devolver" type="submit">
                                            Registrar devolución
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="nota">Sin acciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</body>
</html>