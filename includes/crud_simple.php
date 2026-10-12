<?php
function crud_simple(array $c): void
{
    $pdo    = db();
    $tabla  = $c['tabla'];
    $id     = $c['id'];
    $campo  = $c['campo'];
    $pagina = $c['pagina'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO $tabla ($campo) VALUES (?)")->execute([trim($_POST['nombre'])]);
                volver($pagina, 'ok', 'Registro creado correctamente');
            }
            if ($accion === 'actualizar') {
                $pdo->prepare("UPDATE $tabla SET $campo = ? WHERE $id = ?")->execute([trim($_POST['nombre']), (int) $_POST['id']]);
                volver($pagina, 'ok', 'Registro actualizado correctamente');
            }
            if ($accion === 'eliminar') {
                $pdo->prepare("DELETE FROM $tabla WHERE $id = ?")->execute([(int) $_POST['id']]);
                volver($pagina, 'ok', 'Registro eliminado correctamente');
            }
        } catch (PDOException $ex) {
            volver($pagina, 'error', mensaje_error($ex));
        }
    }

    $editar = null;
    if (isset($_GET['editar'])) {
        $st = $pdo->prepare("SELECT * FROM $tabla WHERE $id = ?");
        $st->execute([(int) $_GET['editar']]);
        $editar = $st->fetch() ?: null;
    }
    $filas = $pdo->query("SELECT * FROM $tabla ORDER BY $id")->fetchAll();

    $titulo = $c['plural'];
    require __DIR__ . '/header.php';
    ?>
    <h2><?= e($c['plural']) ?></h2>
    <form method="post" class="formulario">
        <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
        <?php if ($editar): ?><input type="hidden" name="id" value="<?= e($editar[$id]) ?>"><?php endif; ?>
        <label>Nombre
            <input type="text" name="nombre" required maxlength="100" value="<?= e($editar[$campo] ?? '') ?>">
        </label>
        <button type="submit"><?= $editar ? 'Guardar cambios' : 'Agregar' ?></button>
        <?php if ($editar): ?><a class="boton secundario" href="<?= e($pagina) ?>">Cancelar</a><?php endif; ?>
    </form>

    <table>
        <thead><tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><?= e($f[$id]) ?></td>
                <td><?= e($f[$campo]) ?></td>
                <td class="acciones">
                    <a class="boton secundario" href="<?= e($pagina) ?>?editar=<?= e($f[$id]) ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('¿Eliminar este registro?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= e($f[$id]) ?>">
                        <button type="submit" class="peligro">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$filas): ?><tr><td colspan="3">No hay registros todavía.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <?php
    require __DIR__ . '/footer.php';
}
