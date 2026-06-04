<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$pageTitle = 'Reportes';

$f_tipo  = trim(isset($_GET['tipo'])  ? $_GET['tipo']  : 'area');
$f_desde = trim(isset($_GET['desde']) ? $_GET['desde'] : date('Y-01-01'));
$f_hasta = trim(isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-d'));

$porArea = $db->prepare(
    "SELECT a.codigo, a.nombre,
            SUM(CASE WHEN k.tipo='salida' THEN k.cantidad ELSE 0 END) AS total_salidas
     FROM kardex k JOIN areas_usuarias a ON a.id=k.area_id
     WHERE k.fecha BETWEEN ? AND ?
     GROUP BY k.area_id ORDER BY total_salidas DESC"
);
$porArea->execute(array($f_desde,$f_hasta));
$dataArea = $porArea->fetchAll();

$porOC = $db->prepare(
    "SELECT oc.nro_orden_compra, oc.nea, oc.pecosa, oc.fecha_compra, a.nombre AS area,
            COUNT(DISTINCT d.material_id) AS items,
            SUM(d.cantidad * d.precio_unitario) AS total
     FROM ordenes_compra oc
     JOIN areas_usuarias a ON a.id=oc.area_id
     JOIN ordenes_compra_detalle d ON d.orden_compra_id=oc.id
     WHERE oc.fecha_compra BETWEEN ? AND ?
     GROUP BY oc.id ORDER BY oc.fecha_compra DESC LIMIT 50"
);
$porOC->execute(array($f_desde,$f_hasta));
$dataOC = $porOC->fetchAll();

$porUsuario = $db->prepare(
    "SELECT u.nombre_completo, u.rol,
            SUM(CASE WHEN k.tipo='entrada' THEN k.cantidad ELSE 0 END) AS entradas,
            SUM(CASE WHEN k.tipo='salida'  THEN k.cantidad ELSE 0 END) AS salidas,
            COUNT(*) AS movimientos
     FROM kardex k JOIN usuarios u ON u.id=k.usuario_id
     WHERE k.fecha BETWEEN ? AND ?
     GROUP BY k.usuario_id ORDER BY movimientos DESC"
);
$porUsuario->execute(array($f_desde,$f_hasta));
$dataUsuarios = $porUsuario->fetchAll();

$chartAreaLabels = array();
$chartAreaData   = array();
foreach ($dataArea as $r) {
    $chartAreaLabels[] = mb_substr($r['nombre'],0,20);
    $chartAreaData[]   = (float)$r['total_salidas'];
}
$chartAreaLabelsJson = json_encode($chartAreaLabels);
$chartAreaDataJson   = json_encode($chartAreaData);

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Reportes y Analisis</h4>
</div>

<div class="card table-card mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-end" method="GET">
      <div class="col-auto">
        <label class="form-label mb-1 small fw-semibold">Desde</label>
        <input type="date" name="desde" class="form-control form-control-sm" value="<?php echo h($f_desde); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-1 small fw-semibold">Hasta</label>
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?php echo h($f_hasta); ?>">
      </div>
      <div class="col-auto">
        <label class="form-label mb-1 small fw-semibold">Ver</label>
        <select name="tipo" class="form-select form-select-sm">
          <option value="area"<?php echo ($f_tipo==='area'?' selected':''); ?>>Por Area</option>
          <option value="oc"<?php echo ($f_tipo==='oc'?' selected':''); ?>>Por O.C.</option>
          <option value="usuario"<?php echo ($f_tipo==='usuario'?' selected':''); ?>>Por Usuario</option>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Filtrar</button>
      </div>
      <div class="col-auto">
        <a href="<?php echo BASE_URL; ?>/reportes/exportar.php?tipo=consolidado&desde=<?php echo urlencode($f_desde); ?>&hasta=<?php echo urlencode($f_hasta); ?>"
           class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i>Exportar Excel Consolidado</a>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card table-card">
      <div class="card-header fw-semibold"><i class="fas fa-chart-bar me-2"></i>Salidas por Area</div>
      <div class="card-body"><canvas id="chartArea" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card table-card">
      <div class="card-header fw-semibold"><i class="fas fa-chart-pie me-2"></i>Distribucion por Area</div>
      <div class="card-body"><canvas id="chartPie" height="200"></canvas></div>
    </div>
  </div>
</div>

<?php if ($f_tipo === 'area'): ?>
<div class="card table-card">
  <div class="card-header fw-semibold"><i class="fas fa-building me-2"></i>Detalle por Area (<?php echo formatDate($f_desde); ?> — <?php echo formatDate($f_hasta); ?>)</div>
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>Codigo</th><th>Area</th><th class="text-end">Cantidad Salida</th></tr>
      </thead>
      <tbody>
        <?php foreach ($dataArea as $r): ?>
        <tr>
          <td><code><?php echo h($r['codigo']); ?></code></td>
          <td><?php echo h($r['nombre']); ?></td>
          <td class="text-end fw-bold"><?php echo number_format((float)$r['total_salidas'],2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php elseif ($f_tipo === 'oc'): ?>
<div class="card table-card">
  <div class="card-header fw-semibold"><i class="fas fa-file-invoice me-2"></i>Ordenes de Compra</div>
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>N° O.C.</th><th>Area</th><th>Fecha</th><th>NEA</th><th>PECOSA</th><th class="text-end">Items</th><th class="text-end">Total S/.</th></tr>
      </thead>
      <tbody>
        <?php foreach ($dataOC as $r): ?>
        <tr>
          <td><code><?php echo h($r['nro_orden_compra']); ?></code></td>
          <td><?php echo h($r['area']); ?></td>
          <td><?php echo formatDate($r['fecha_compra']); ?></td>
          <td><?php echo h(isset($r['nea']) ? $r['nea'] : '—'); ?></td>
          <td><?php echo h(isset($r['pecosa']) ? $r['pecosa'] : '—'); ?></td>
          <td class="text-end"><?php echo $r['items']; ?></td>
          <td class="text-end fw-bold">S/. <?php echo number_format((float)$r['total'],2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="card table-card">
  <div class="card-header fw-semibold"><i class="fas fa-users me-2"></i>Actividad por Usuario</div>
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>Usuario</th><th>Rol</th><th class="text-end">Entradas</th><th class="text-end">Salidas</th><th class="text-end">Total Movimientos</th></tr>
      </thead>
      <tbody>
        <?php foreach ($dataUsuarios as $r): ?>
        <tr>
          <td><?php echo h($r['nombre_completo']); ?></td>
          <td><span class="badge bg-secondary"><?php echo h($r['rol']); ?></span></td>
          <td class="text-end text-success"><?php echo number_format((float)$r['entradas'],2); ?></td>
          <td class="text-end text-danger"><?php echo number_format((float)$r['salidas'],2); ?></td>
          <td class="text-end fw-bold"><?php echo $r['movimientos']; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php
$colors = array('rgba(52,152,219,.8)','rgba(46,204,113,.8)','rgba(231,76,60,.8)','rgba(155,89,182,.8)',
                'rgba(241,196,15,.8)','rgba(26,188,156,.8)','rgba(230,126,34,.8)','rgba(52,73,94,.8)');
$c = json_encode($colors);

$extraJs = '<script>
var aLabels = '.$chartAreaLabelsJson.';
var aData   = '.$chartAreaDataJson.';
var colors  = '.$c.';
new Chart(document.getElementById("chartArea"), {
  type: "bar",
  data: { labels: aLabels, datasets: [{ label:"Salidas", data: aData, backgroundColor: colors }] },
  options: { indexAxis:"y", plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}} }
});
new Chart(document.getElementById("chartPie"), {
  type: "doughnut",
  data: { labels: aLabels, datasets: [{ data: aData, backgroundColor: colors }] },
  options: { plugins:{legend:{position:"right"}} }
});
</script>';

include ROOT_PATH . '/includes/footer.php';
