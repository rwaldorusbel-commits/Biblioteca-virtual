```php
<?php
require_once __DIR__ . '/config/conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');
$estado = $_GET['estado'] ?? '';

$estadosPermitidos = ['pendiente', 'confirmada', 'expirada', 'cancelada'];

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = '';
}

$sql = "
    SELECT
        r.id,
        r.fecha_reserva,
        r.fecha_expiracion,
        r.estado,
        u.nombre AS usuario,
        u.email,
        l.titulo AS libro
    FROM reservas r
    INNER JOIN usuarios u ON u.id = r.id_usuario
    INNER JOIN libros l ON l.id = r.id_libro
    WHERE 1 = 1
";

$parametros = [];

if ($busqueda !== '') {
    $sql .= "
        AND (
            u.nombre LIKE :nombre
            OR u.email LIKE :email
            OR l.titulo LIKE :titulo
        )
    ";

    $termino = '%' . $busqueda . '%';
    $parametros['nombre'] = $termino;
    $parametros['email'] = $termino;
    $parametros['titulo'] = $termino;
}

if ($estado !== '') {
    $sql .= ' AND r.estado = :estado';
    $parametros['estado'] = $estado;
}

$sql .= ' ORDER BY r.fecha_reserva DESC, r.id DESC';

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$reservas = $stmt->fetchAll();

function h($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function fecha($valor): string
{
    if (!$valor) {
        return '—';
    }

    return date('d/m/Y H:i', strtotime($valor));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservas | BiblioTech</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #1e293b;
        }
        header {
            background: #172554;
            color: white;
            padding: 22px 32px;
        }
        header a { color: #bfdbfe; text-decoration: none; }
        main { max-width: 1250px; margin: 30px auto; padding: 0 22px; }
        .panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px;
        }
        .muted { color: #64748b; }
        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 22px 0;
        }
        input, select {
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }
        input { flex: 1; min-width: 200px; }
        button, .clear {
            padding: 12px 17px;
            border: 0;
            border-radius: 7px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .clear { background: #e2e8f0; color: #172554; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td {
            padding: 13px 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        th { background: #f8fafc; color: #475569; font-size: 13px; }
        .estado {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #e2e8f0;
            color: #334155;
            font-size: 12px;
        }
        .pendiente { background: #fef3c7; color: #92400e; }
        .confirmada { background: #dcfce7; color: #166534; }
        .expirada, .cancelada { background: #fee2e2; color: #991b1b; }
        .empty { padding: 25px; text-align: center; color: #64748b; }
        @media(max-width: 600px) {
            header { padding: 20px; }
            .filters { display: grid; }
            input, select { width: 100%; }
        }
    </style>
</head>
<body>
<header>
    <h1>📚 BiblioTech — Reservas</h1>
    <a href="index.php">← Volver al panel principal</a>
</header>

<main>
    <section class="panel">
        <h2>Consultar reservas</h2>
        <p class="muted">
            Busca por usuario, correo o título del libro. Puedes filtrar por estado.
            Esta pantalla es de consulta.
        </p>

        <form class="filters" method="get" action="reservas.php">
            <input
                type="search"
                name="buscar"
                placeholder="Nombre, correo o título del libro..."
                value="<?= h($busqueda) ?>"
            >

            <select name="estado">
                <option value="">Todos los estados</option>
                <?php foreach ($estadosPermitidos as $opcion): ?>
                    <option
                        value="<?= h($opcion) ?>"
                        <?= $estado === $opcion ? 'selected' : '' ?>
                    >
                        <?= h(ucfirst($opcion)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtrar</button>
            <a class="clear" href="reservas.php">Limpiar</a>
        </form>

        <p class="muted">
            Reservas encontradas: <strong><?= count($reservas) ?></strong>
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Libro</th>
                        <th>Fecha de reserva</th>
                        <th>Fecha de expiración</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$reservas): ?>
                    <tr>
                        <td colspan="7" class="empty">
                            No se encontraron reservas con esos criterios.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td>#<?= h($reserva['id']) ?></td>
                            <td><?= h($reserva['usuario']) ?></td>
                            <td><?= h($reserva['email']) ?></td>
                            <td><?= h($reserva['libro']) ?></td>
                            <td><?= h(fecha($reserva['fecha_reserva'])) ?></td>
                            <td><?= h(fecha($reserva['fecha_expiracion'])) ?></td>
                            <td>
                                <span class="estado <?= h($reserva['estado']) ?>">
                                    <?= h(ucfirst($reserva['estado'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
```
