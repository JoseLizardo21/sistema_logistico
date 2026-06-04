<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

$db        = getDB();
$pageTitle = 'Materiales';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((isset($_POST['_action']) ? $_POST['_action'] : '')) === 'delete') {
    requireRole(['administrador','gerente']);
    $id = (int)$_POST['id'];
    $db->prepare("UPDATE materiales SET activo=0 WHERE id=?")->execute([$id]);
    setFlash('success', 'Material eliminado.');
    redirect(BASE_URL . '/materiales/index.php');
}

$q    = trim((isset($_GET['q']) ? $_GET['q'] : ''));
$sql  = "SELECT * FROM materiales WHERE activo=1";
$params = [];
if ($q) { $sql .= " AND (codigo LIKE ? OR detalle LIKE ?)"; $params = ["%$q%", "%$q%"]; }
$sql .= " ORDER BY codigo";
$st = $db->prepare($sql);
$st->execute($params);
$materiales = $st->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Materiales</h4>
  <?php if (!isGerente() || isAdmin()): ?>
  <a href="<?= BASE_URL ?>/materiales/form.php" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i>Nuevo Material
  </a>
  <?php endif; ?>
</div>

<div class="card table-card">
  <div class="card-header">
    <form class="d-flex gap-2" method="GET">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por código o nombre..." value="<?= h($q) ?>">
      <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
      <?php if ($q): ?><a href="?" class="btn btn-sm btn-outline-secondary">Limpiar</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle datatable mb-0">
      <thead class="table-dark">
        <tr>
          <th>Código</th>
          <th>Detalle / Descripción</th>
          <th>Unidad</th>
          <th class="text-end">Stock Actual</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($materiales as $m): ?>
        <tr>
          <td><code><?= h($m['codigo']) ?></code></td>
          <td><?= h($m['detalle']) ?></td>
          <td><?= h($m['unidad_medida']) ?></td>
          <td class="text-end">
            <?php
              $s = (float)$m['stock_actual'];
              $cls = $s <= 0 ? 'badge-stock-zero' : ($s <= 5 ? 'badge-stock-low' : 'badge-stock-ok');
            ?>
            <span class="badge <?= $cls ?> fs-6 px-2"><?= number_format($s,2) ?></span>
          </td>
          <td class="text-center">
            <a href="<?= BASE_URL ?>/materiales/form.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
              <i class="fas fa-edit"></i>
            </a>
            <?php if (isAdmin()): ?>
            <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar material?')">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
