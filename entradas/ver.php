<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$id = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

$st = $db->prepare(
    "SELECT oc.*, a.nombre AS area_nombre, a.codigo AS area_codigo,
            c.num_clasificador,
            u.nombre_completo AS usuario_nombre
     FROM ordenes_compra oc
     JOIN areas_usuarias a ON a.id=oc.area_id
     LEFT JOIN clasificadores c ON c.id=oc.clasificador_id
     LEFT JOIN usuarios u ON u.id=oc.usuario_id
     WHERE oc.id=?"
);
$st->execute(array($id));
$oc = $st->fetch();
if (!$oc) { setFlash('danger','O.C. no encontrada.'); redirect(BASE_URL.'/entradas/index.php'); }

$det = $db->prepare(
    "SELECT d.*, m.codigo, m.detalle, m.unidad_medida, m.stock_actual
     FROM ordenes_compra_detalle d JOIN materiales m ON m.id=d.material_id
     WHERE d.orden_compra_id=?"
);
$det->execute(array($id));
$items = $det->fetchAll();

$pageTitle = 'Ver Entrada - ' . $oc['nro_orden_compra'];
include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/entradas/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-arrow-circle-down me-2 text-success"></i>Entrada O.C. <?php echo h($oc['nro_orden_compra']); ?></h4>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card table-card">
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><th>N O.C.:</th><td><code><?php echo h($oc['nro_orden_compra']); ?></code></td></tr>
          <tr><th>Area:</th><td><?php echo h($oc['area_codigo'].' - '.$oc['area_nombre']); ?></td></tr>
          <tr><th>Fecha:</th><td><?php echo formatDate($oc['fecha_compra']); ?></td></tr>
          <tr><th>Estado:</th><td><span class="badge bg-success"><?php echo h($oc['estado']); ?></span></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card table-card">
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><th>N NEA:</th><td><?php echo h(isset($oc['nea']) ? $oc['nea'] : '-'); ?></td></tr>
          <tr><th>N PECOSA:</th><td><?php echo h(isset($oc['pecosa']) ? $oc['pecosa'] : '-'); ?></td></tr>
          <tr><th>Clasificador:</th><td><?php echo h(isset($oc['num_clasificador']) ? $oc['num_clasificador'] : '-'); ?></td></tr>
          <tr><th>Registrado por:</th><td><?php echo h(isset($oc['usuario_nombre']) ? $oc['usuario_nombre'] : '-'); ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="card table-card">
  <div class="card-header fw-semibold"><i class="fas fa-list me-2"></i>Items Recibidos</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr><th>N</th><th>Codigo</th><th>Descripcion</th><th>Unidad</th><th class="text-end">Cantidad</th><th class="text-end">Precio Unit.</th><th class="text-end">Total</th><th class="text-end">Stock</th></tr>
      </thead>
      <tbody>
        <?php $total=0; foreach($items as $i=>$it): $sub=(float)$it['cantidad']*(float)$it['precio_unitario']; $total+=$sub; ?>
        <tr>
          <td><?php echo $i+1; ?></td>
          <td><code><?php echo h($it['codigo']); ?></code></td>
          <td><?php echo h($it['detalle']); ?></td>
          <td><?php echo h($it['unidad_medida']); ?></td>
          <td class="text-end"><?php echo number_format((float)$it['cantidad'],2); ?></td>
          <td class="text-end">S/. <?php echo number_format((float)$it['precio_unitario'],4); ?></td>
          <td class="text-end">S/. <?php echo number_format($sub,2); ?></td>
          <td class="text-end"><span class="badge badge-stock-ok"><?php echo number_format((float)$it['stock_actual'],2); ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr><td colspan="6" class="text-end">TOTAL:</td><td class="text-end">S/. <?php echo number_format($total,2); ?></td><td></td></tr>
      </tfoot>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
