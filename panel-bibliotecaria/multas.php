
<?php
require_once __DIR__ . '/config/conexion.php';

function h(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$filtroEstado = (string) ($_GET['estado'] ?? '');
$busqueda = trim((string) ($_GET['buscar'] ?? ''));

$estadosValidos = ['', 'pendiente', 'pagada', 'condonada'];

if (!in_array($filtroEstado, $estadosValidos, true)) {
    $filtroEstado = '';
}

$sql = "
    SELECT
        m.id AS multa_id,
        m.id_prestamo,
        m.monto,
        m.estado AS estado_multa,
        m.fecha_generacion,
        m.fecha_pago,
        u.nombre AS usuario,
        u.email,
        l.titulo AS libro,
        p.fecha_prestamo,
        p.fecha_devolucion_esperada,
        p.fecha_devolucion_real
    FROM multas m
    INNER JOIN prestamos p ON p.id = m.id_prestamo
    INNER JOIN usuarios u ON u.id = p.id_usuario
    INNER JOIN libros l ON l.id = p.id_libro
    WHERE 1 = 1
";

$parametros = [];

if ($filtroEstado !== '') {
    $sql .= " AND m.estado = ?";
    $parametros[] = $filtroEstado;
}

if ($busqueda !== '') {
    $sql .= " AND (
        u.nombre LIKE ?
        OR u.email LIKE ?
        OR l.titulo LIKE ?
        OR CAST(m.id AS CHAR) LIKE ?
        OR CAST(m.id_prestamo AS CHAR) LIKE ?
    )";

    $termino = '%' . $busqueda . '%';

    array_push(
        $parametros,
        $termino,
        $termino,
        $termino,
        $termino,
        $termino
    );
}

$sql .= " ORDER BY m.id DESC";

$consulta = $conexion->prepare($sql);
$consulta->execute($parametros);
$multas = $consulta->fetchAll();

$conteos = $conexion->query(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END), 0) AS pendientes,
        COALESCE(SUM(CASE WHEN estado = 'pagada' THEN 1 ELSE 0 END), 0) AS pagadas,
        COALESCE(SUM(CASE WHEN estado = 'condonada' THEN 1 ELSE 0 END), 0) AS condonadas,
        COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END), 0) AS monto_pendiente
     FROM multas"
)->fetch();

function fechaLegible(?string $fecha): string
{
    if (!$fecha) {
        return '—';
    }

    $tiempo = strtotime($fecha);

    return $tiempo ? date('d/m/Y', $tiempo) : $fecha;
}

function estadoLegible(string $estado): string
{
    return match ($estado) {
        'pendiente' => 'Pendiente',
        'pagada' => 'Pagada',
        'condonada' => 'Condonada',
        default => ucfirst($estado),
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multas | BiblioTech</title>
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
        .panel {
            background: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 4px 18px #14213d0b;
        }
        h1 { margin-top: 0; }
        .subtitulo, .nota { color: #64748b; }
        .enlace { color: #2457d6; text-decoration: none; }
        .resumen {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }
        .tarjeta {
            background: #f8fafc;
            padding: 16px;
            border-radius: 10px;
        }
        .tarjeta p { margin: 0 0 8px; color: #64748b; }
        .tarjeta strong { font-size: 23px; }
        .filtros {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
        }
        .campo { flex: 1; min-width: 180px; }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            cursor: pointer;
            color: white;
            background: #2457d6;
            border: 0;
            font-weight: bold;
        }
        .tabla-contenedor { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1050px; }
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
            font-size: 12px;
            font-weight: bold;
        }
        .pendiente { background: #fef3c7; color: #92400e; }
        .pagada { background: #dcfce7; color: #166534; }
        .condonada { background: #e2e8f0; color: #475569; }
        @media (max-width: 850px) {
            body { padding: 12px; }
            .resumen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
</head>
<body>
<div class="contenedor">
    <header class="panel">
        <h1>Multas</h1>
        <p class="subtitulo">BiblioTech · Consulta de sanciones de biblioteca</p>
        <a class="enlace" href="index.php">← Volver al panel principal</a>
    </header>

    <section class="panel">
        <h2>Resumen de multas</h2>
        <div class="resumen">
            <div class="tarjeta">
                <p>Total de multas</p>
                <strong><?= (int) $conteos['total'] ?></strong>
            </div>
            <div class="tarjeta">
                <p>Pendientes</p>
                <strong><?= (int) $conteos['pendientes'] ?></strong>
            </div>
            <div class="tarjeta">
                <p>Pagadas</p>
                <strong><?= (int) $conteos['pagadas'] ?></strong>
            </div>
            <div class="tarjeta">
                <p>Monto pendiente</p>
                <strong>S/ <?= number_format((float) $conteos['monto_pendiente'], 2) ?></strong>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Buscar multas</h2>
        <form method="get" class="filtros">
            <div class="campo">
                <label for="buscar">Buscar</label>
                <input
                    id="buscar"
                    type="search"
                    name="buscar"
                    value="<?= h($busqueda) ?>"
                    placeholder="Usuario, libro o código"
                >
            </div>

            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos los estados</option>
                    <?php foreach (['pendiente', 'pagada', 'condonada'] as $estado): ?>
                        <option
                            value="<?= h($estado) ?>"
                            <?= $filtroEstado === $estado ? 'selected' : '' ?>
                        >
                            <?= h(estadoLegible($estado)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo" style="max-width: 150px">
                <button type="submit">Buscar</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Listado de multas</h2>
        <p class="nota">
            Esta pantalla permite consultar los registros existentes.
            No modifica importes ni estados de pago.
        </p>

        <div class="tabla-contenedor">
            <table>
                <thead>
                <tr>
                    <th>ID multa</th>
                    <th>ID préstamo</th>
                    <th>Usuario</th>
                    <th>Libro</th>
                    <th>Importe</th>
                    <th>Estado</th>
                    <th>Generación</th>
                    <th>Fecha de pago</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$multas): ?>
                    <tr>
                        <td colspan="8">No se encontraron multas para los filtros seleccionados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($multas as $multa): ?>
                        <tr>
                            <td><?= (int) $multa['multa_id'] ?></td>
                            <td><?= (int) $multa['id_prestamo'] ?></td>
                            <td>
                                <?= h($multa['usuario']) ?><br>
                                <span class="nota"><?= h($multa['email']) ?></span>
                            </td>
                            <td><?= h($multa['libro']) ?></td>
                            <td>S/ <?= number_format((float) $multa['monto'], 2) ?></td>
                            <td>
                                <span class="estado <?= h($multa['estado_multa']) ?>">
                                    <?= h(estadoLegible($multa['estado_multa'])) ?>
                                </span>
                            </td>
                            <td><?= h(fechaLegible($multa['fecha_generacion'])) ?></td>
                            <td><?= h(fechaLegible($multa['fecha_pago'])) ?></td>
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