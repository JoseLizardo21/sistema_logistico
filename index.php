<?php
require_once __DIR__ . '/config/init.php';
requireLogin();

$db = getDB();
$pageTitle = 'Dashboard';

$totalMateriales = $db->query("SELECT COUNT(*) FROM materiales WHERE activo=1")->fetchColumn();
$totalAreas      = $db->query("SELECT COUNT(*) FROM areas_usuarias WHERE activo=1")->fetchColumn();
$totalEntradas   = $db->query("SELECT COUNT(*) FROM ordenes_compra WHERE MONTH(fecha_compra)=MONTH(NOW()) AND YEAR(fecha_compra)=YEAR(NOW())")->fetchColumn();
$totalSalidas    = $db->query("SELECT COUNT(*) FROM papeletas_salida WHERE estado='activo' AND MONTH(fecha)=MONTH(NOW()) AND YEAR(fecha)=YEAR(NOW())")->fetchColumn();

$movMeses = $db->query(
    "SELECT DATE_FORMAT(fecha,'%b %Y') AS mes,
            SUM(CASE WHEN tipo='entrada' THEN cantidad ELSE 0 END) AS entradas,
            SUM(CASE WHEN tipo='salida'  THEN cantidad ELSE 0 END) AS salidas
     FROM kardex
     WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(fecha,'%Y-%m')
     ORDER BY MIN(fecha)"
)->fetchAll();

$topMat = $db->query(
    "SELECT m.detalle, SUM(k.cantidad) AS total
     FROM kardex k JOIN materiales m ON m.id=k.material_id
     WHERE k.tipo='salida'
     GROUP BY k.material_id ORDER BY total DESC LIMIT 8"
)->fetchAll();

$ultimasEntradas = $db->query(
    "SELECT oc.id, oc.nro_orden_compra, oc.fecha_compra, a.nombre AS area, oc.nea
     FROM ordenes_compra oc JOIN areas_usuarias a ON a.id=oc.area_id
     ORDER BY oc.created_at DESC LIMIT 5"
)->fetchAll();

$ultimasSalidas = $db->query(
    "SELECT ps.id, ps.nro_papeleta, ps.fecha, a.nombre AS area, r.nombres_apellidos AS responsable
     FROM papeletas_salida ps JOIN areas_usuarias a ON a.id=ps.area_id
     LEFT JOIN responsables r ON r.id=ps.responsable_id
     WHERE ps.estado='activo' ORDER BY ps.created_at DESC LIMIT 5"
)->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</h4>
  <small class="text-muted"><?php echo date('d/m/Y H:i'); ?></small>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary bg-opacity-10">
          <i class="fas fa-boxes text-primary"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold"><?php echo $totalMateriales; ?></div>
          <div class="text-muted small">Materiales</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success bg-opacity-10">
          <i class="fas fa-arrow-circle-down text-success"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold"><?php echo $totalEntradas; ?></div>
          <div class="text-muted small">Entradas este mes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-danger bg-opacity-10">
          <i class="fas fa-arrow-circle-up text-danger"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold"><?php echo $totalSalidas; ?></div>
          <div class="text-muted small">Salidas este mes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning bg-opacity-10">
          <i class="fas fa-building text-warning"></i>
        </div>
        <div>
          <div class="fs-4 fw-bold"><?php echo $totalAreas; ?></div>
          <div class="text-muted small">Areas Usuarias</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
  <div class="col-md-7">
    <div class="card table-card">
      <div class="card-header">
        <i class="fas fa-chart-line me-2 text-primary"></i>Movimientos Ultimos 6 Meses
      </div>
      <div class="card-body">
        <canvas id="chartMovimientos" height="120"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card table-card">
      <div class="card-header">
        <i class="fas fa-chart-bar me-2 text-success"></i>Top Materiales con mas Salidas
      </div>
      <div class="card-body">
        <canvas id="chartTopMat" height="160"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Recent tables -->
<div class="row g-3">
  <div class="col-md-6">
    <div class="card table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-arrow-down text-success me-2"></i>Ultimas Entradas</span>
        <a href="<?php echo BASE_URL; ?>/entradas/index.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="table-light"><tr><th>O.C.</th><th>Area</th><th>Fecha</th><th>NEA</th></tr></thead>
          <tbody>
            <?php foreach ($ultimasEntradas as $e): ?>
            <tr>
              <td><a href="<?php echo BASE_URL; ?>/entradas/ver.php?id=<?php echo $e['id']; ?>"><?php echo h($e['nro_orden_compra']); ?></a></td>
              <td class="text-truncate" style="max-width:140px"><?php echo h($e['area']); ?></td>
              <td><?php echo formatDate($e['fecha_compra']); ?></td>
              <td><?php echo h(isset($e['nea']) ? $e['nea'] : '—'); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$ultimasEntradas): ?>
              <tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-arrow-up text-danger me-2"></i>Ultimas Salidas</span>
        <a href="<?php echo BASE_URL; ?>/salidas/index.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="table-light"><tr><th>N° Papeleta</th><th>Area</th><th>Responsable</th><th>Fecha</th></tr></thead>
          <tbody>
            <?php foreach ($ultimasSalidas as $s): ?>
            <tr>
              <td><a href="<?php echo BASE_URL; ?>/salidas/ver.php?id=<?php echo $s['id']; ?>">PS-<?php echo str_pad($s['nro_papeleta'],5,'0',STR_PAD_LEFT); ?></a></td>
              <td class="text-truncate" style="max-width:140px"><?php echo h($s['area']); ?></td>
              <td><?php echo h(isset($s['responsable']) ? $s['responsable'] : '—'); ?></td>
              <td><?php echo formatDate($s['fecha']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$ultimasSalidas): ?>
              <tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
$mesesLabels   = array();
$mesesEntradas = array();
$mesesSalidas  = array();
foreach ($movMeses as $r) {
    $mesesLabels[]   = $r['mes'];
    $mesesEntradas[] = (float)$r['entradas'];
    $mesesSalidas[]  = (float)$r['salidas'];
}
$topLabels = array();
$topData   = array();
foreach ($topMat as $r) {
    $topLabels[] = mb_substr($r['detalle'], 0, 25);
    $topData[]   = (float)$r['total'];
}
$mesesLabelsJson   = json_encode($mesesLabels);
$mesesEntradasJson = json_encode($mesesEntradas);
$mesesSalidasJson  = json_encode($mesesSalidas);
$topLabelsJson     = json_encode($topLabels);
$topDataJson       = json_encode($topData);

$extraJs = '<script>
new Chart(document.getElementById("chartMovimientos"), {
  type: "line",
  data: {
    labels: ' . $mesesLabelsJson . ',
    datasets: [
      { label:"Entradas", data:' . $mesesEntradasJson . ', borderColor:"#27ae60", backgroundColor:"rgba(39,174,96,.1)", tension:.4, fill:true },
      { label:"Salidas",  data:' . $mesesSalidasJson  . ', borderColor:"#e74c3c", backgroundColor:"rgba(231,76,60,.1)", tension:.4, fill:true }
    ]
  },
  options:{ plugins:{legend:{position:"top"}}, scales:{y:{beginAtZero:true}} }
});
new Chart(document.getElementById("chartTopMat"), {
  type: "bar",
  data: {
    labels: ' . $topLabelsJson . ',
    datasets: [{ label:"Cant. salida", data:' . $topDataJson . ', backgroundColor:"rgba(52,152,219,.7)" }]
  },
  options:{ indexAxis:"y", plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}} }
});
</script>';

include ROOT_PATH . '/includes/footer.php';
