<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db = getDB();
$id = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

$st = $db->prepare(
    "SELECT ps.*, a.nombre AS area_nombre, a.codigo AS area_codigo,
            r.nombres_apellidos AS responsable, r.dni
     FROM papeletas_salida ps
     JOIN areas_usuarias a ON a.id=ps.area_id
     LEFT JOIN responsables r ON r.id=ps.responsable_id
     WHERE ps.id=?"
);
$st->execute(array($id));
$ps = $st->fetch();
if (!$ps) { die('Papeleta no encontrada.'); }

$det = $db->prepare(
    "SELECT d.*, m.codigo, m.detalle, m.unidad_medida
     FROM papeletas_salida_detalle d JOIN materiales m ON m.id=d.material_id
     WHERE d.papeleta_id=?"
);
$det->execute(array($id));
$items = $det->fetchAll();
$nroPapeleta = str_pad($ps['nro_papeleta'],5,'0',STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>PAPELETA DE SALIDA PS-<?php echo $nroPapeleta; ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
.page { width: 210mm; padding: 10mm; }
.header { text-align: center; margin-bottom: 8px; border-bottom: 2px solid #000; padding-bottom: 6px; }
.header h2 { font-size: 14px; letter-spacing: 1px; }
.numero-papeleta { font-size: 16px; font-weight: bold; text-align: center; margin: 6px 0; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-bottom: 8px; border: 1px solid #000; padding: 6px; }
.info-row { display: flex; gap: 4px; }
.info-label { font-weight: bold; white-space: nowrap; }
table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
th, td { border: 1px solid #555; padding: 3px 5px; }
th { background: #eee; font-size: 10px; text-align: center; }
td { font-size: 10px; }
.td-right { text-align: right; }
.td-center { text-align: center; }
.firma-section { display: flex; justify-content: space-between; margin-top: 30px; }
.firma-box { text-align: center; width: 45%; }
.firma-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 4px; }
.copia-mark { text-align: center; font-weight: bold; font-size: 14px; border: 2px dashed #000; padding: 4px; margin-bottom: 8px; }
.separator { border-top: 2px dashed #000; margin: 16px 0; }
@media print {
  .no-print { display: none !important; }
  body { -webkit-print-color-adjust: exact; }
}
</style>
</head>
<body>
<div class="no-print" style="padding:10px;background:#f0f0f0;margin-bottom:10px">
  <button onclick="window.print()" style="padding:8px 20px;background:#1976d2;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px">
    Imprimir Original y Copia
  </button>
  <button onclick="window.close()" style="padding:8px 20px;background:#666;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;margin-left:8px">
    Cerrar
  </button>
</div>

<?php for ($copia = 1; $copia <= 2; $copia++): ?>
<div class="page">

  <?php if ($copia === 2): ?>
  <div class="copia-mark">COPIA — AREA USUARIA</div>
  <?php else: ?>
  <div class="copia-mark">ORIGINAL — ALMACEN</div>
  <?php endif; ?>

  <div class="header">
    <h2>MUNICIPALIDAD</h2>
    <h2>PAPELETA DE SALIDA DE ALMACEN</h2>
    <div class="numero-papeleta">N° PS-<?php echo $nroPapeleta; ?></div>
  </div>

  <div class="info-grid">
    <div class="info-row"><span class="info-label">Fecha:</span> <?php echo formatDate($ps['fecha']); ?></div>
    <div class="info-row"><span class="info-label">Unidad Solicitante:</span> <?php echo h($ps['area_codigo'].' — '.$ps['area_nombre']); ?></div>
    <div class="info-row"><span class="info-label">Responsable:</span> <?php echo h(isset($ps['responsable']) ? $ps['responsable'] : '—'); ?></div>
    <div class="info-row"><span class="info-label">DNI:</span> <?php echo h(isset($ps['dni']) ? $ps['dni'] : '—'); ?></div>
    <div class="info-row" style="grid-column:1/-1"><span class="info-label">Con destino a:</span> <?php echo h($ps['area_nombre']); ?></div>
    <?php if (!empty($ps['observacion'])): ?>
    <div class="info-row" style="grid-column:1/-1"><span class="info-label">Observacion:</span> <?php echo h($ps['observacion']); ?></div>
    <?php endif; ?>
  </div>

  <table>
    <thead>
      <tr>
        <th width="30">N°</th>
        <th width="90">Codigo</th>
        <th width="60">Cant.</th>
        <th width="60">U.Medida</th>
        <th>Descripcion del Material</th>
        <th width="60">Marca</th>
        <th width="60">N° Serie</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i => $it): ?>
      <tr>
        <td class="td-center"><?php echo $i+1; ?></td>
        <td><?php echo h($it['codigo']); ?></td>
        <td class="td-right"><?php echo number_format((float)$it['cantidad'],2); ?></td>
        <td class="td-center"><?php echo h($it['unidad_medida']); ?></td>
        <td><?php echo h($it['detalle']); ?></td>
        <td></td>
        <td></td>
      </tr>
      <?php endforeach; ?>
      <?php for ($r = count($items); $r < 10; $r++): ?>
      <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <div class="firma-section">
    <div class="firma-box">
      <div class="firma-line">
        <strong>ALMACEN</strong><br>
        V°B° ENTREGUE CONFORME
      </div>
    </div>
    <div class="firma-box">
      <div class="firma-line">
        <strong><?php echo strtoupper(h($ps['area_nombre'])); ?></strong><br>
        <?php echo h(isset($ps['responsable']) ? $ps['responsable'] : ''); ?><br>
        RECIBI CONFORME
      </div>
    </div>
  </div>

</div>
<?php if ($copia === 1): ?><div class="separator"></div><?php endif; ?>
<?php endfor; ?>

<script>
window.addEventListener('load', function() {
  window.print();
});
</script>
</body>
</html>
