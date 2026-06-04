<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

$db = getDB();
$pageTitle = 'Responsables de Área';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((isset($_POST['_action']) ? $_POST['_action'] : '')) === 'delete') {
    requireRole(['administrador']);
    $db->prepare("UPDATE responsables SET activo=0 WHERE id=?")->execute([(int)$_POST['id']]);
    setFlash('success','Responsable eliminado.');
    redirect(BASE_URL.'/responsables/index.php');
}

$rows = $db->query(
    "SELECT r.*, a.nombre AS area_nombre, a.codigo AS area_codigo
     FROM responsables r LEFT JOIN areas_usuarias a ON a.id=r.area_id
     WHERE r.activo=1 ORDER BY r.codigo"
)->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>Responsables de Área</h4>
  <?php if (isAdmin()): ?>
  <a href="<?= BASE_URL ?>/responsables/form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nuevo Responsable</a>
  <?php endif; ?>
</div>
<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>Código</th><th>Nombres y Apellidos</th><th>DNI</th><th>Área Asignada</th><th class="text-center">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><code><?= h($r['codigo']) ?></code></td>
          <td><?= h($r['nombres_apellidos']) ?></td>
          <td><?= h($r['dni']) ?></td>
          <td><?= $r['area_codigo'] ? h($r['area_codigo'].' — '.$r['area_nombre']) : '—' ?></td>
          <td class="text-center">
            <?php if (isAdmin()): ?>
            <a href="<?= BASE_URL ?>/responsables/form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
            <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar responsable?')">
              <input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
            <?php else: ?>—<?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
