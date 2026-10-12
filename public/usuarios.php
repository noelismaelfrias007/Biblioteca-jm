<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();

// campo => [etiqueta, longitud máxima]
$campos = [
    'nombre'    => ['Nombre', 50],
    'apellido'  => ['Apellido', 50],
    'cedula'    => ['Cédula', 15],
    'telefono'  => ['Teléfono', 15],
    'email'     => ['Correo', 100],
    'direccion' => ['Dirección', 150],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'eliminar') {
            $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?')->execute([(int) $_POST['id']]);
            volver('usuarios.php', 'ok', 'Usuario eliminado correctamente');
        }

        $datos = [];
        foreach (array_keys($campos) as $campo) {
            $datos[] = trim($_POST[$campo] ?? '') ?: null;
        }

        if ($accion === 'crear') {
            $pdo->prepare('INSERT INTO usuarios (nombre, apellido, cedula, telefono, email, direccion) VALUES (?,?,?,?,?,?)')->execute($datos);
            volver('usuarios.php', 'ok', 'Usuario creado correctamente');
        }
        if ($accion === 'actualizar') {
            $datos[] = (int) $_POST['id'];
            $pdo->prepare('UPDATE usuarios SET nombre=?, apellido=?, cedula=?, telefono=?, email=?, direccion=? WHERE id_usuario=?')->execute($datos);
            volver('usuarios.php', 'ok', 'Usuario actualizado correctamente');
        }
    } catch (PDOException $ex) {
        volver('usuarios.php', 'error', mensaje_error($ex));
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}
$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY id_usuario')->fetchAll();

$titulo = 'Usuarios';
require __DIR__ . '/../includes/header.php';
?>
<h2>Usuarios</h2>

<form method="post" class="formulario">
    <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
    <?php if ($editar): ?><input type="hidden" name="id" value="<?= e($editar['id_usuario']) ?>"><?php endif; ?>
    <?php foreach ($campos as $campo => [$etiqueta, $max]): ?>
        <label><?= e($etiqueta) ?>
            <input type="<?= $campo === 'email' ? 'email' : 'text' ?>" name="<?= e($campo) ?>" maxlength="<?= $max ?>"
                   <?= in_array($campo, ['nombre', 'apellido'], true) ? 'required' : '' ?>
                   value="<?= e($editar[$campo] ?? '') ?>">
        </label>
    <?php endforeach; ?>
    <button type="submit"><?= $editar ? 'Guardar cambios' : 'Agregar usuario' ?></button>
    <?php if ($editar): ?><a class="boton secundario" href="usuarios.php">Cancelar</a><?php endif; ?>
</form>

<table>
    <thead>
        <tr><th>ID</th><?php foreach ($campos as [$etiqueta]): ?><th><?= e($etiqueta) ?></th><?php endforeach; ?><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= e($u['id_usuario']) ?></td>
            <?php foreach (array_keys($campos) as $campo): ?><td><?= e($u[$campo]) ?></td><?php endforeach; ?>
            <td class="acciones">
                <a class="boton secundario" href="usuarios.php?editar=<?= e($u['id_usuario']) ?>">Editar</a>
                <form method="post" onsubmit="return confirm('¿Eliminar este usuario?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= e($u['id_usuario']) ?>">
                    <button type="submit" class="peligro">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$usuarios): ?><tr><td colspan="8">No hay usuarios todavía.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
