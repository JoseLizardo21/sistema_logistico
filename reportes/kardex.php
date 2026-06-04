<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$pageTitle = 'Kardex de Materiales';

$f_mat    = trim(isset($_GET['material']) ? $_GET['material'] : '');
$f_desde  = trim(isset($_GET['desde'])    ? $_GET['desde']    : '');
$f_hasta  = trim(isset($_GET['hasta'])    ? $_GET['hasta']    : '');
$f_tipo   = trim(isset($_GET['tipo'])     ? $_GET['tipo']     : '');
$f_area   = trim(isset($_GET['area'])     ? $_GET['area']     : '');

$sql = "SELECT k.*, m.codigo AS mat_cod, m.detalle AS mat_det, m.unidad_medida,
               a.nombre AS area_nombre, u.nombre_completo AS usuario_nombre
        FROM kardex k
        JOIN materiales m ON m.id=k.material_id
        LEFT JOIN areas_usuarias a ON a.id=k.area_id
        LEFT JOIN usuarios u ON u.id=k.usuario_id
        WHERE 1=1";
$params = array();
if ($f_mat)   { $sql .= " AND (m.codigo LIKE ? OR m.detalle LIKE ?)"; $params[] = "%$f_mat%"; $params[] = "%$f_mat%"; }
if ($f_desde) { $sql .= " AND k.fecha >= ?"; $params[] = $f_desde; }
if ($f_hasta) { $sql .= " AND k.fecha <= ?"; $params[] = $f_hasta; }
if ($f_tipo)  { $sql .= " AND k.tipo = ?";  $params[] = $f_tipo; }
if ($f_area)  { $sql .= " AND (a.nombre LIKE ? OR a.codigo LIKE ?)"; $params[] = "%$f_area%"; $params[] = "%$f_area%"; }
$sql .= " ORDER BY k.fecha DESC, k.id DESC";

$st = $db->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();

include ROOT_PATH . '/includes/header.php';
$exportParams = array();
if ($f_mat)   $exportParams[] = 'material=' . urlencode($f_mat);
if ($f_desde) $exportParams[] = 'desde=' . urlencode($f_desde);
if ($f_hasta) $exportParams[] = 'hasta=' . urlencode($f_hasta);
if ($f_area)  $exportParams[] = 'area=' . urlencode($f_area);
$exportQs = implode('&', $exportParams);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-list-alt me-2 text-primary"></i>Kardex de Movimientos</h4>
  <a href="<?php echo BASE_URL; ?>/reportes/exportar.php?tipo=kardex&<?php echo $exportQs; ?>" class="btn btn-success btn-sm">
    <i class="fas fa-file-excel me-1"></i>Exportar Excel
  </a>
</div>

<div class="card table-card mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-end" method="GET">
      <div class="col-md-3">
        <label class="form-label mb-1 small fw-semibold">Material</label>
        <input type="text" name="material" class="form-control form-control-sm" value="<?php echo h($f_mat); ?>" placeholder="Codigo o nombre...">
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small fw-semibold">Tipo</label>
        <select name="tipo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="entrada"<?php echo ($f_tipo==='entrada'?' selected':''); ?>>Entradas</option>
          <option value="salida"<?php echo ($f_tipo==='salida'?' selected':''); ?>>Salidas</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small fw-semibold">Desde</label>
        <input type="date" name="desde" class="form-control form-control-sm" value="<?php echo h($f_desde); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small fw-semibold">Hasta</label>
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?php echo h($f_hasta); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small fw-semibold">Area</label>
        <input type="text" name="area" class="form-control form-control-sm" value="<?php echo h($f_area); ?>" placeholder="Area...">
      </div>
      <div class="col-md-1 d-flex gap-1">
        <button class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        <a href="?" class="btn btn-outline-secondary btn-sm">x</a>
      </div>
    </form>
  </div>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-sm table-hover datatable mb-0">
      <thead class="table-dark">
        <tr>
          <th>Fecha</th><th>Codigo</th><th>Material</th><th>Unidad</th>
          <th class="text-center">Tipo</th><th>Referencia</th><th>Area</th>
          <th class="text-end">Cantidad</th><th class="text-end">P. Unit.</th><th class="text-end">Saldo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo formatDate($r['fecha']); ?></td>
          <td><code><?php echo h($r['mat_cod']); ?></code></td>
          <td class="small"><?php echo h(mb_substr($r['mat_det'],0,40)); ?></td>
          <td><?php echo h($r['unidad_medida']); ?></td>
          <td class="text-center">
            <?php if ($r['tipo']==='entrada'): ?>
            <span class="badge bg-success">Entrada</span>
            <?php else: ?>
            <span class="badge bg-danger">Salida</span>
            <?php endif; ?>
          </td>
          <td class="small"><?php echo h(isset($r['referencia_numero']) ? $r['referencia_numero'] : '-'); ?></td>
          <td class="small text-truncate" style="max-width:120px"><?php echo h(isset($r['area_nombre']) ? $r['area_nombre'] : '-'); ?></td>
          <td class="text-end <?php echo ($r['tipo']==='entrada'?'text-success':'text-danger'); ?> fw-semibold">
            <?php echo ($r['tipo']==='entrada'?'+':'-').number_format((float)$r['cantidad'],2); ?>
          </td>
          <td class="text-end"><?php echo number_format((float)$r['precio_unitario'],4); ?></td>
          <td class="text-end fw-bold"><?php echo number_format((float)$r['saldo_cantidad'],2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
