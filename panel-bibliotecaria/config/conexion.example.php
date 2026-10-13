<?php
declare(strict_types=1);

$host = '127.0.0.1';
$puerto = '3306';
$baseDatos = 'bibliotech';
$usuario = 'root';
$contrasena = '';

$dsn = "mysql:host={$host};port={$puerto};dbname={$baseDatos};charset=utf8mb4";

try {
    $conexion = new PDO($dsn, $usuario, $contrasena, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Error de conexión a BiblioTech: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Verifica la configuración local.');
}
