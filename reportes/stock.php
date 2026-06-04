<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$pageTitle = 'Stock de Materiales';

$q = trim((isset($_GET['q']) ? $_GET['q'] : ''));
$sql = "SELECT * FROM materiales WHERE activo=1";
$params = [];
if ($q) { $sql .= " AND (codigo LIKE ? OR detalle LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }
$sql .= " ORDER BY detalle";
$st = $db->prepare($sql); $st->execute($params);
$materiales = $st->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Stock de Materiales</h4>
  <a href="<?= BASE_URL ?>/reportes/exportar.php?tipo=stock&q=<?= urlencode($q) ?>" class="btn btn-success btn-sm">
    <i class="fas fa-file-excel me-1"></i>Exportar Excel
  </a>
</div>

<div class="card table-card mb-3">
  <div class="card-body py-2">
    <form class="d-flex gap-2" method="GET">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar material..." value="<?= h($q) ?>">
      <button class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
      <?php if ($q): ?><a href="?" class="btn btn-outline-secondary btn-sm">Limpiar</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr>
          <th>Código</th>
          <th>Descripción</th>
          <th>Unidad</th>
          <th class="text-end">Total Entradas</th>
          <th class="text-end">Total Salidas</th>
          <th class="text-end">Stock Actual</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($materiales as $m):
          $entradas = $db->prepare("SELECT COALESCE(SUM(cantidad),0) FROM kardex WHERE material_id=? AND tipo='entrada'");
          $entradas->execute([$m['id']]); $totalE = (float)$entradas->fetchColumn();
          $salidas  = $db->prepare("SELECT COALESCE(SUM(cantidad),0) FROM kardex WHERE material_id=? AND tipo='salida'");
          $salidas->execute([$m['id']]); $totalS = (float)$salidas->fetchColumn();
          $stock = (float)$m['stock_actual'];
          $cls   = $stock <= 0 ? 'badge-stock-zero' : ($stock <= 5 ? 'badge-stock-low' : 'badge-stock-ok');
          $label = $stock <= 0 ? 'Sin stock' : ($stock <= 5 ? 'Stock bajo' : 'OK');
        ?>
        <tr>
          <td><code><?= h($m['codigo']) ?></code></td>
          <td><?= h($m['detalle']) ?></td>
          <td><?= h($m['unidad_medida']) ?></td>
          <td class="text-end text-success fw-semibold"><?= number_format($totalE,2) ?></td>
          <td class="text-end text-danger fw-semibold"><?= number_format($totalS,2) ?></td>
          <td class="text-end"><span class="badge <?= $cls ?> fs-6"><?= number_format($stock,2) ?></span></td>
          <td><span class="badge <?= $cls ?>"><?= $label ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
