<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
$estados = ['Disponible', 'Prestado', 'Agotado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'eliminar') {
            $pdo->prepare('DELETE FROM libros WHERE id_libro = ?')->execute([(int) $_POST['id']]);
            volver('libros.php', 'ok', 'Libro eliminado correctamente');
        }

        $datos = [
            trim($_POST['titulo']),
            trim($_POST['isbn']) ?: null,
            (int) $_POST['id_autor'] ?: null,
            (int) $_POST['id_categoria'] ?: null,
            (float) $_POST['precio_venta'],
            (int) $_POST['cantidad_total'],
            (int) $_POST['cantidad_disponible'],
            in_array($_POST['estado'], $estados, true) ? $_POST['estado'] : 'Disponible',
        ];

        if ($datos[6] > $datos[5]) {
            volver('libros.php', 'error', 'Los ejemplares disponibles no pueden superar el total');
        }

        if ($accion === 'crear') {
            $pdo->prepare('INSERT INTO libros (titulo, isbn, id_autor, id_categoria, precio_venta, cantidad_total, cantidad_disponible, estado) VALUES (?,?,?,?,?,?,?,?)')->execute($datos);
            volver('libros.php', 'ok', 'Libro creado correctamente');
        }
        if ($accion === 'actualizar') {
            $datos[] = (int) $_POST['id'];
            $pdo->prepare('UPDATE libros SET titulo=?, isbn=?, id_autor=?, id_categoria=?, precio_venta=?, cantidad_total=?, cantidad_disponible=?, estado=? WHERE id_libro=?')->execute($datos);
            volver('libros.php', 'ok', 'Libro actualizado correctamente');
        }
    } catch (PDOException $ex) {
        volver('libros.php', 'error', mensaje_error($ex));
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM libros WHERE id_libro = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

$q = trim($_GET['q'] ?? '');
$sql = 'SELECT l.*, a.nombre AS autor, c.nombre_categoria AS categoria
        FROM libros l
        LEFT JOIN autores a ON a.id_autor = l.id_autor
        LEFT JOIN categorias c ON c.id_categoria = l.id_categoria';
if ($q !== '') {
    $sql .= ' WHERE l.titulo ILIKE :q OR a.nombre ILIKE :q OR c.nombre_categoria ILIKE :q';
}
$sql .= ' ORDER BY l.id_libro';
$st = $pdo->prepare($sql);
$st->execute($q !== '' ? [':q' => "%$q%"] : []);
$libros = $st->fetchAll();

$autores    = $pdo->query('SELECT id_autor, nombre FROM autores ORDER BY nombre')->fetchAll();
$categorias = $pdo->query('SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria')->fetchAll();

$titulo = 'Libros';
require __DIR__ . '/../includes/header.php';
?>
<h2>Libros</h2>

<form method="post" class="formulario">
    <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
    <?php if ($editar): ?><input type="hidden" name="id" value="<?= e($editar['id_libro']) ?>"><?php endif; ?>

    <label>Título
        <input type="text" name="titulo" required maxlength="150" value="<?= e($editar['titulo'] ?? '') ?>">
    </label>
    <label>ISBN
        <input type="text" name="isbn" maxlength="20" value="<?= e($editar['isbn'] ?? '') ?>">
    </label>
    <label>Autor
        <select name="id_autor">
            <option value="">— Sin autor —</option>
            <?php foreach ($autores as $a): ?>
                <option value="<?= e($a['id_autor']) ?>" <?= ($editar['id_autor'] ?? '') == $a['id_autor'] ? 'selected' : '' ?>><?= e($a['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Categoría
        <select name="id_categoria">
            <option value="">— Sin categoría —</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= e($c['id_categoria']) ?>" <?= ($editar['id_categoria'] ?? '') == $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Precio de venta
        <input type="number" name="precio_venta" required min="0" step="0.01" value="<?= e($editar['precio_venta'] ?? '0') ?>">
    </label>
    <label>Ejemplares totales
        <input type="number" name="cantidad_total" required min="0" value="<?= e($editar['cantidad_total'] ?? '1') ?>">
    </label>
    <label>Disponibles
        <input type="number" name="cantidad_disponible" required min="0" value="<?= e($editar['cantidad_disponible'] ?? '1') ?>">
    </label>
    <label>Estado
        <select name="estado">
            <?php foreach ($estados as $es): ?>
                <option <?= ($editar['estado'] ?? 'Disponible') === $es ? 'selected' : '' ?>><?= e($es) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit"><?= $editar ? 'Guardar cambios' : 'Agregar libro' ?></button>
    <?php if ($editar): ?><a class="boton secundario" href="libros.php">Cancelar</a><?php endif; ?>
</form>

<form method="get" class="buscador">
    <label>Buscar
        <input type="search" name="q" placeholder="Título, autor o categoría" value="<?= e($q) ?>">
    </label>
    <button type="submit">Buscar</button>
    <?php if ($q !== ''): ?><a class="boton secundario" href="libros.php">Limpiar</a><?php endif; ?>
</form>

<table>
    <thead>
        <tr><th>ID</th><th>Título</th><th>ISBN</th><th>Autor</th><th>Categoría</th><th>Precio</th><th>Disp./Total</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($libros as $l): ?>
        <tr>
            <td><?= e($l['id_libro']) ?></td>
            <td><?= e($l['titulo']) ?></td>
            <td><?= e($l['isbn']) ?></td>
            <td><?= e($l['autor']) ?></td>
            <td><?= e($l['categoria']) ?></td>
            <td><?= e(number_format((float) $l['precio_venta'], 2)) ?></td>
            <td><?= e($l['cantidad_disponible']) ?> / <?= e($l['cantidad_total']) ?></td>
            <td><?= e($l['estado']) ?></td>
            <td class="acciones">
                <a class="boton secundario" href="libros.php?editar=<?= e($l['id_libro']) ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar este libro?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= e($l['id_libro']) ?>">
                    <button type="submit" class="peligro">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$libros): ?><tr><td colspan="9">No se encontraron libros.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
