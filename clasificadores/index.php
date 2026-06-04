<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$pageTitle = 'Clasificadores';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((isset($_POST['_action']) ? $_POST['_action'] : '')) === 'delete') {
    requireRole(['administrador']);
    $db->prepare("UPDATE clasificadores SET activo=0 WHERE id=?")->execute([(int)$_POST['id']]);
    setFlash('success','Clasificador eliminado.');
    redirect(BASE_URL.'/clasificadores/index.php');
}

$rows = $db->query("SELECT * FROM clasificadores WHERE activo=1 ORDER BY num_clasificador")->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-tags me-2 text-primary"></i>Clasificadores</h4>
  <?php if (isAdmin()): ?>
  <a href="<?= BASE_URL ?>/clasificadores/form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nuevo Clasificador</a>
  <?php endif; ?>
</div>
<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>N° Clasificador</th><th>Detalle</th><th class="text-center">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $c): ?>
        <tr>
          <td><code><?= h($c['num_clasificador']) ?></code></td>
          <td><?= h($c['detalle']) ?></td>
          <td class="text-center">
            <?php if (isAdmin()): ?>
            <a href="<?= BASE_URL ?>/clasificadores/form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
            <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
              <input type="hidden" name="_action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
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
