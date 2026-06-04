<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$id = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

$st = $db->prepare(
    "SELECT ps.*, a.nombre AS area_nombre, a.codigo AS area_codigo,
            r.nombres_apellidos AS responsable, r.dni,
            u.nombre_completo AS usuario_nombre
     FROM papeletas_salida ps
     JOIN areas_usuarias a ON a.id=ps.area_id
     LEFT JOIN responsables r ON r.id=ps.responsable_id
     LEFT JOIN usuarios u ON u.id=ps.usuario_id
     WHERE ps.id=?"
);
$st->execute(array($id));
$ps = $st->fetch();
if (!$ps) { setFlash('danger','Papeleta no encontrada.'); redirect(BASE_URL.'/salidas/index.php'); }

$det = $db->prepare(
    "SELECT d.*, m.codigo, m.detalle, m.unidad_medida
     FROM papeletas_salida_detalle d JOIN materiales m ON m.id=d.material_id
     WHERE d.papeleta_id=?"
);
$det->execute(array($id));
$items = $det->fetchAll();

$pageTitle = 'Ver Papeleta PS-'.str_pad($ps['nro_papeleta'],5,'0',STR_PAD_LEFT);
include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/salidas/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-arrow-circle-up me-2 text-danger"></i>Papeleta PS-<?php echo str_pad($ps['nro_papeleta'],5,'0',STR_PAD_LEFT); ?></h4>
  <a href="<?php echo BASE_URL; ?>/salidas/imprimir.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">
    <i class="fas fa-print me-1"></i>Imprimir
  </a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card table-card h-100">
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><th>N Papeleta:</th><td><span class="badge bg-danger fs-6">PS-<?php echo str_pad($ps['nro_papeleta'],5,'0',STR_PAD_LEFT); ?></span></td></tr>
          <tr><th>Fecha:</th><td><?php echo formatDate($ps['fecha']); ?></td></tr>
          <tr><th>Area:</th><td><?php echo h($ps['area_codigo'].' - '.$ps['area_nombre']); ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card table-card h-100">
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><th>Responsable:</th><td><?php echo h(isset($ps['responsable']) ? $ps['responsable'] : '-'); ?></td></tr>
          <tr><th>DNI:</th><td><?php echo h(isset($ps['dni']) ? $ps['dni'] : '-'); ?></td></tr>
          <tr><th>Registrado por:</th><td><?php echo h(isset($ps['usuario_nombre']) ? $ps['usuario_nombre'] : '-'); ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="card table-card">
  <div class="card-header fw-semibold"><i class="fas fa-list me-2"></i>Materiales Despachados</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr><th>N</th><th>Codigo</th><th>Descripcion</th><th>Unidad</th><th class="text-end">Cantidad</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i=>$it): ?>
        <tr>
          <td><?php echo $i+1; ?></td>
          <td><code><?php echo h($it['codigo']); ?></code></td>
          <td><?php echo h($it['detalle']); ?></td>
          <td><?php echo h($it['unidad_medida']); ?></td>
          <td class="text-end"><?php echo number_format((float)$it['cantidad'],2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
