<?php
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = require __DIR__ . '/../config/config.php';
        $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['dbname']}";
        $pdo = new PDO($dsn, $c['user'], $c['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function volver(string $pagina, string $tipo, string $texto): void
{
    header('Location: ' . $pagina . '?' . http_build_query([$tipo => $texto]));
    exit;
}

function mensaje_error(PDOException $ex): string
{
    return match ($ex->getCode()) {
        '23505' => 'Ya existe un registro con ese valor único (por ejemplo, la cédula).',
        '23503' => 'No se puede eliminar: el registro está siendo usado en otras tablas.',
        '23502' => 'Falta un dato obligatorio.',
        default => 'No se pudo completar la operación.',
    };
}
