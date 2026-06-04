<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$pageTitle = 'Salidas - Papeletas';

$f_area  = trim(isset($_GET['area'])  ? $_GET['area']  : '');
$f_fecha = trim(isset($_GET['fecha']) ? $_GET['fecha'] : '');

$sql = "SELECT ps.*, a.nombre AS area_nombre, a.codigo AS area_codigo,
               r.nombres_apellidos AS responsable
        FROM papeletas_salida ps
        JOIN areas_usuarias a ON a.id=ps.area_id
        LEFT JOIN responsables r ON r.id=ps.responsable_id
        WHERE ps.estado='activo'";
$params = array();
if ($f_area)  { $sql .= " AND (a.nombre LIKE ? OR a.codigo LIKE ?)"; $params[] = "%$f_area%"; $params[] = "%$f_area%"; }
if ($f_fecha) { $sql .= " AND ps.fecha=?"; $params[] = $f_fecha; }
$sql .= " ORDER BY ps.nro_papeleta DESC";

$st = $db->prepare($sql); $st->execute($params);
$rows = $st->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-arrow-circle-up me-2 text-danger"></i>Salidas - Papeletas</h4>
  <a href="<?php echo BASE_URL; ?>/salidas/crear.php" class="btn btn-danger"><i class="fas fa-plus me-1"></i>Nueva Papeleta de Salida</a>
</div>

<div class="card table-card mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-end" method="GET">
      <div class="col-md-4">
        <label class="form-label mb-1 small fw-semibold">Area</label>
        <input type="text" name="area" class="form-control form-control-sm" value="<?php echo h($f_area); ?>" placeholder="Codigo o nombre...">
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1 small fw-semibold">Fecha</label>
        <input type="date" name="fecha" class="form-control form-control-sm" value="<?php echo h($f_fecha); ?>">
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
        <tr><th>N Papeleta</th><th>Fecha</th><th>Area que Retira</th><th>Responsable</th><th class="text-center">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><span class="badge bg-danger fs-6">PS-<?php echo str_pad($r['nro_papeleta'],5,'0',STR_PAD_LEFT); ?></span></td>
          <td><?php echo formatDate($r['fecha']); ?></td>
          <td><?php echo h($r['area_codigo'].' - '.$r['area_nombre']); ?></td>
          <td><?php echo h(isset($r['responsable']) ? $r['responsable'] : '-'); ?></td>
          <td class="text-center">
            <a href="<?php echo BASE_URL; ?>/salidas/ver.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
            <a href="<?php echo BASE_URL; ?>/salidas/imprimir.php?id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-print"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
