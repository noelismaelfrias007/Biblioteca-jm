<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'prestar') {
            $pdo->beginTransaction();
            $st = $pdo->prepare("UPDATE libros SET cantidad_disponible = cantidad_disponible - 1, estado = CASE WHEN cantidad_disponible - 1 = 0 THEN 'Agotado' ELSE estado END WHERE id_libro = ? AND cantidad_disponible > 0");
            $st->execute([(int) $_POST['id_libro']]);
            if ($st->rowCount() === 0) {
                $pdo->rollBack();
                volver('prestamos.php', 'error', 'No hay ejemplares disponibles de ese libro');
            }
            $pdo->prepare('INSERT INTO prestamos (id_libro, id_usuario, id_empleado, fecha_devolucion_esperada) VALUES (?,?,?,?)')
                ->execute([(int) $_POST['id_libro'], (int) $_POST['id_usuario'], (int) $_POST['id_empleado'], $_POST['fecha_devolucion']]);
            $pdo->commit();
            volver('prestamos.php', 'ok', 'Préstamo registrado correctamente');
        }

        if ($accion === 'devolver') {
            $id = (int) $_POST['id'];
            $pdo->beginTransaction();
            $st = $pdo->prepare('SELECT id_libro, estado FROM prestamos WHERE id_prestamo = ? FOR UPDATE');
            $st->execute([$id]);
            $prestamo = $st->fetch();
            if (!$prestamo || $prestamo['estado'] === 'Devuelto') {
                $pdo->rollBack();
                volver('prestamos.php', 'error', 'Ese préstamo ya fue devuelto o no existe');
            }
            $estadoLibro = in_array($_POST['estado_libro'], ['Bueno', 'Dañado', 'Perdido'], true) ? $_POST['estado_libro'] : 'Bueno';
            $pdo->prepare("UPDATE prestamos SET estado = 'Devuelto' WHERE id_prestamo = ?")->execute([$id]);
            $pdo->prepare('INSERT INTO devoluciones (id_prestamo, estado_libro) VALUES (?, ?)')->execute([$id, $estadoLibro]);
            if ($estadoLibro !== 'Perdido') {
                $pdo->prepare("UPDATE libros SET cantidad_disponible = cantidad_disponible + 1, estado = 'Disponible' WHERE id_libro = ?")->execute([$prestamo['id_libro']]);
            }
            $pdo->commit();
            volver('prestamos.php', 'ok', 'Devolución registrada correctamente');
        }
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        volver('prestamos.php', 'error', mensaje_error($ex));
    }
}

$prestamos = $pdo->query("SELECT p.id_prestamo, p.fecha_prestamo, p.fecha_devolucion_esperada, p.estado, l.titulo, u.nombre || ' ' || u.apellido AS usuario
                          FROM prestamos p
                          JOIN libros l ON l.id_libro = p.id_libro
                          JOIN usuarios u ON u.id_usuario = p.id_usuario
                          ORDER BY p.id_prestamo DESC")->fetchAll();
$libros    = $pdo->query('SELECT id_libro, titulo, cantidad_disponible FROM libros WHERE cantidad_disponible > 0 ORDER BY titulo')->fetchAll();
$usuarios  = $pdo->query("SELECT id_usuario, nombre || ' ' || apellido AS nombre FROM usuarios ORDER BY nombre")->fetchAll();
$empleados = $pdo->query("SELECT id_empleado, nombre || ' ' || apellido AS nombre FROM empleados ORDER BY nombre")->fetchAll();
$vence = date('Y-m-d', strtotime('+7 days'));

$titulo = 'Préstamos';
require __DIR__ . '/../includes/header.php';
?>
<h2>Préstamos y devoluciones</h2>

<form method="post" class="formulario">
    <input type="hidden" name="accion" value="prestar">
    <label>Libro
        <select name="id_libro" required>
            <?php foreach ($libros as $l): ?>
                <option value="<?= e($l['id_libro']) ?>"><?= e($l['titulo']) ?> (<?= e($l['cantidad_disponible']) ?> disp.)</option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Usuario
        <select name="id_usuario" required>
            <?php foreach ($usuarios as $u): ?>
                <option value="<?= e($u['id_usuario']) ?>"><?= e($u['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Empleado
        <select name="id_empleado" required>
            <?php foreach ($empleados as $em): ?>
                <option value="<?= e($em['id_empleado']) ?>"><?= e($em['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Devolver antes de
        <input type="date" name="fecha_devolucion" required value="<?= e($vence) ?>">
    </label>
    <button type="submit">Registrar préstamo</button>
</form>

<table>
    <thead>
        <tr><th>ID</th><th>Libro</th><th>Usuario</th><th>Prestado</th><th>Vence</th><th>Estado</th><th>Devolución</th></tr>
    </thead>
    <tbody>
    <?php foreach ($prestamos as $p): ?>
        <tr>
            <td><?= e($p['id_prestamo']) ?></td>
            <td><?= e($p['titulo']) ?></td>
            <td><?= e($p['usuario']) ?></td>
            <td><?= e($p['fecha_prestamo']) ?></td>
            <td><?= e($p['fecha_devolucion_esperada']) ?></td>
            <td><?= e($p['estado']) ?></td>
            <td>
                <?php if ($p['estado'] === 'Activo'): ?>
                    <form method="post" class="en-linea">
                        <input type="hidden" name="accion" value="devolver">
                        <input type="hidden" name="id" value="<?= e($p['id_prestamo']) ?>">
                        <select name="estado_libro"><option>Bueno</option><option>Dañado</option><option>Perdido</option></select>
                        <button type="submit">Devolver</button>
                    </form>
                <?php else: ?>—<?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$prestamos): ?><tr><td colspan="7">No hay préstamos registrados.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
