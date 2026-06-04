<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$pageTitle = 'Entradas - Ordenes de Compra';

$f_area  = trim(isset($_GET['area'])  ? $_GET['area']  : '');
$f_fecha = trim(isset($_GET['fecha']) ? $_GET['fecha'] : '');
$f_nea   = trim(isset($_GET['nea'])   ? $_GET['nea']   : '');

$sql = "SELECT oc.*, a.nombre AS area_nombre, a.codigo AS area_codigo, c.num_clasificador
        FROM ordenes_compra oc
        JOIN areas_usuarias a ON a.id=oc.area_id
        LEFT JOIN clasificadores c ON c.id=oc.clasificador_id
        WHERE 1=1";
$params = array();
if ($f_area)  { $sql .= " AND (a.nombre LIKE ? OR a.codigo LIKE ?)"; $params[] = "%$f_area%"; $params[] = "%$f_area%"; }
if ($f_fecha) { $sql .= " AND oc.fecha_compra = ?"; $params[] = $f_fecha; }
if ($f_nea)   { $sql .= " AND oc.nea LIKE ?"; $params[] = "%$f_nea%"; }
$sql .= " ORDER BY oc.created_at DESC";

$st = $db->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-arrow-circle-down me-2 text-success"></i>Entradas - Ordenes de Compra</h4>
  <a href="<?php echo BASE_URL; ?>/entradas/crear.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Nueva Entrada (O.C.)</a>
</div>

<div class="card table-card mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-end" method="GET">
      <div class="col-md-4">
        <label class="form-label mb-1 small fw-semibold">Area</label>
        <input type="text" name="area" class="form-control form-control-sm" placeholder="Codigo o nombre..." value="<?php echo h($f_area); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1 small fw-semibold">Fecha Compra</label>
        <input type="date" name="fecha" class="form-control form-control-sm" value="<?php echo h($f_fecha); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small fw-semibold">N NEA</label>
        <input type="text" name="nea" class="form-control form-control-sm" value="<?php echo h($f_nea); ?>">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Filtrar</button>
        <a href="?" class="btn btn-outline-secondary btn-sm">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>N O.C.</th><th>Area Solicitante</th><th>Fecha Compra</th><th>N NEA</th><th>N PECOSA</th><th>Clasificador</th><th class="text-center">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><code><?php echo h($r['nro_orden_compra']); ?></code></td>
          <td><?php echo h($r['area_codigo'].' - '.$r['area_nombre']); ?></td>
          <td><?php echo formatDate($r['fecha_compra']); ?></td>
          <td><?php echo h(isset($r['nea']) ? $r['nea'] : '—'); ?></td>
          <td><?php echo h(isset($r['pecosa']) ? $r['pecosa'] : '—'); ?></td>
          <td><?php echo h(isset($r['num_clasificador']) ? $r['num_clasificador'] : '—'); ?></td>
          <td class="text-center">
            <a href="<?php echo BASE_URL; ?>/entradas/ver.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
