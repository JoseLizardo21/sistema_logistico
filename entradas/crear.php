<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

$db         = getDB();
$pageTitle  = 'Nueva Entrada - Orden de Compra';
$errors     = array();
$clasificadores = $db->query("SELECT id,num_clasificador,detalle FROM clasificadores WHERE activo=1 ORDER BY num_clasificador")->fetchAll();
$areas          = $db->query("SELECT id,codigo,nombre FROM areas_usuarias WHERE activo=1 ORDER BY codigo")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nro    = strtoupper(trim(isset($_POST['nro_orden_compra']) ? $_POST['nro_orden_compra'] : ''));
    $areaid = (int)(isset($_POST['area_id']) ? $_POST['area_id'] : 0);
    $clasid = (int)(isset($_POST['clasificador_id']) ? $_POST['clasificador_id'] : 0);
    $clasid = $clasid ?: null;
    $nea    = trim(isset($_POST['nea'])    ? $_POST['nea']    : '');
    $pecosa = trim(isset($_POST['pecosa']) ? $_POST['pecosa'] : '');
    $fecha  = trim(isset($_POST['fecha_compra']) ? $_POST['fecha_compra'] : date('Y-m-d'));
    $obs    = trim(isset($_POST['observacion']) ? $_POST['observacion'] : '');

    $mat_ids    = isset($_POST['material_id'])    ? $_POST['material_id']    : array();
    $cantidades = isset($_POST['cantidad'])        ? $_POST['cantidad']       : array();
    $precios    = isset($_POST['precio_unitario']) ? $_POST['precio_unitario']: array();

    if (!$nro)    $errors[] = 'El N° de Orden de Compra es requerido.';
    if (!$areaid) $errors[] = 'Selecciona el area solicitante.';
    if (empty($mat_ids)) $errors[] = 'Agrega al menos un material.';

    $items = array();
    foreach ($mat_ids as $i => $mid) {
        $mid  = (int)$mid;
        $cant = (float)str_replace(',', '.', isset($cantidades[$i]) ? $cantidades[$i] : 0);
        $prec = (float)str_replace(',', '.', isset($precios[$i])    ? $precios[$i]    : 0);
        if ($mid && $cant > 0) $items[] = array('material_id'=>$mid,'cantidad'=>$cant,'precio'=>$prec);
    }
    if (empty($items)) $errors[] = 'Ningun item valido ingresado.';

    if (!$errors) {
        $db->beginTransaction();
        try {
            $db->prepare(
                "INSERT INTO ordenes_compra(nro_orden_compra,area_id,clasificador_id,nea,pecosa,fecha_compra,observacion,usuario_id)
                 VALUES(?,?,?,?,?,?,?,?)"
            )->execute(array($nro,$areaid,$clasid,$nea,$pecosa,$fecha,$obs,$_SESSION['usuario_id']));
            $ocId = (int)$db->lastInsertId();

            foreach ($items as $it) {
                $db->prepare(
                    "INSERT INTO ordenes_compra_detalle(orden_compra_id,material_id,cantidad,precio_unitario)
                     VALUES(?,?,?,?)"
                )->execute(array($ocId,$it['material_id'],$it['cantidad'],$it['precio']));

                updateStock($it['material_id'], $it['cantidad'], 'entrada');
                $saldo = getStockMaterial($it['material_id']);
                insertKardex(array(
                    ':material_id'       => $it['material_id'],
                    ':tipo'              => 'entrada',
                    ':referencia_tipo'   => 'orden_compra',
                    ':referencia_id'     => $ocId,
                    ':referencia_numero' => $nro,
                    ':area_id'           => $areaid,
                    ':cantidad'          => $it['cantidad'],
                    ':precio_unitario'   => $it['precio'],
                    ':saldo_cantidad'    => $saldo,
                    ':fecha'             => $fecha,
                    ':usuario_id'        => $_SESSION['usuario_id'],
                ));
            }
            $db->commit();
            setFlash('success', "Entrada registrada. O.C.: " . $nro);
            redirect(BASE_URL . '/entradas/index.php');
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/entradas/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-arrow-circle-down me-2 text-success"></i>Nueva Entrada - Orden de Compra</h4>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div>
<?php endif; ?>

<form method="POST" id="formEntrada">
<div class="card table-card mb-3">
  <div class="card-header fw-semibold"><i class="fas fa-file-invoice me-2"></i>Datos de la Orden de Compra</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">N° Orden de Compra <span class="text-danger">*</span></label>
        <input type="text" name="nro_orden_compra" class="form-control" required
               value="<?php echo h(isset($_POST['nro_orden_compra']) ? $_POST['nro_orden_compra'] : ''); ?>" placeholder="Ej: OC-2026-0006">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Area Solicitante <span class="text-danger">*</span></label>
        <select name="area_id" class="form-select" required>
          <option value="">— Seleccionar —</option>
          <?php $selA = isset($_POST['area_id']) ? $_POST['area_id'] : ''; foreach ($areas as $a): ?>
          <option value="<?php echo $a['id']; ?>"<?php echo ($selA==$a['id']?' selected':''); ?>><?php echo h($a['codigo'].' — '.$a['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Clasificador</label>
        <select name="clasificador_id" class="form-select">
          <option value="">— Ninguno —</option>
          <?php $selC = isset($_POST['clasificador_id']) ? $_POST['clasificador_id'] : ''; foreach ($clasificadores as $c): ?>
          <option value="<?php echo $c['id']; ?>"<?php echo ($selC==$c['id']?' selected':''); ?>><?php echo h($c['num_clasificador'].' — '.$c['detalle']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">N° NEA</label>
        <input type="text" name="nea" class="form-control" value="<?php echo h(isset($_POST['nea']) ? $_POST['nea'] : ''); ?>" placeholder="2026-00100">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">N° PECOSA</label>
        <input type="text" name="pecosa" class="form-control" value="<?php echo h(isset($_POST['pecosa']) ? $_POST['pecosa'] : ''); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Fecha de Compra</label>
        <input type="date" name="fecha_compra" class="form-control" value="<?php echo h(isset($_POST['fecha_compra']) ? $_POST['fecha_compra'] : date('Y-m-d')); ?>">
      </div>
      <div class="col-md-9">
        <label class="form-label fw-semibold">Observacion</label>
        <input type="text" name="observacion" class="form-control" value="<?php echo h(isset($_POST['observacion']) ? $_POST['observacion'] : ''); ?>">
      </div>
    </div>
  </div>
</div>

<div class="card table-card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
    <span><i class="fas fa-list me-2"></i>Items de Material</span>
    <button type="button" class="btn btn-sm btn-success" id="btnAddRow">
      <i class="fas fa-plus me-1"></i>Agregar Material
    </button>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0" id="tablaItems">
      <thead class="table-light">
        <tr>
          <th width="40">N°</th>
          <th>Codigo / Material</th>
          <th>Descripcion</th>
          <th width="100">Unidad</th>
          <th width="110">Cantidad</th>
          <th width="120">Precio Unit.</th>
          <th width="120">Total</th>
          <th width="50"></th>
        </tr>
      </thead>
      <tbody id="itemsBody"></tbody>
      <tfoot>
        <tr class="table-light fw-bold">
          <td colspan="6" class="text-end">TOTAL S/.</td>
          <td id="totalGeneral">0.00</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="d-flex gap-2">
  <button type="submit" class="btn btn-success px-4">
    <i class="fas fa-save me-2"></i>Registrar Entrada
  </button>
  <a href="<?php echo BASE_URL; ?>/entradas/index.php" class="btn btn-outline-secondary">Cancelar</a>
</div>
</form>

<?php
$extraJs = <<<'JS'
<script>
var rowCount = 0;
function addRow() {
  rowCount++;
  var n = rowCount;
  var row = '<tr id="row'+n+'">'
    + '<td>'+n+'</td>'
    + '<td style="min-width:200px;position:relative">'
    + '<input type="text" class="form-control form-control-sm mat-search" placeholder="Codigo o nombre..." autocomplete="off" data-row="'+n+'">'
    + '<input type="hidden" name="material_id[]" class="mat-id" value="">'
    + '</td>'
    + '<td><input type="text" class="form-control form-control-sm mat-detalle" readonly></td>'
    + '<td><input type="text" class="form-control form-control-sm mat-unidad" readonly></td>'
    + '<td><input type="number" name="cantidad[]" class="form-control form-control-sm cant" min="0.01" step="0.01" value="1" required></td>'
    + '<td><input type="number" name="precio_unitario[]" class="form-control form-control-sm prec" min="0" step="0.01" value="0.00"></td>'
    + '<td><input type="text" class="form-control form-control-sm total-row" readonly value="0.00"></td>'
    + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow('+n+')"><i class="fas fa-times"></i></button></td>'
    + '</tr>';
  $('#itemsBody').append(row);
  setupSearch(n);
}

function removeRow(n) { $('#row'+n).remove(); updateTotal(); }

function setupSearch(n) {
  var $row = $('#row'+n);
  var $inp = $row.find('.mat-search');
  var $drop = $('<ul class="list-group position-absolute z-3" style="min-width:320px;max-height:220px;overflow-y:auto"></ul>');
  $inp.after($drop);

  $inp.on('input', function(){
    var q = $(this).val().trim();
    if(q.length < 1){ $drop.hide(); return; }
    $.getJSON(BASE_URL+'/ajax/buscar_material.php', {q:q}, function(data){
      $drop.empty().show();
      if(!data.length){ $drop.append('<li class="list-group-item small py-1">Sin resultados</li>'); return; }
      $.each(data, function(i, m){
        $('<li class="list-group-item list-group-item-action small py-1 px-2" style="cursor:pointer"><b>'+m.codigo+'</b> — '+m.detalle+' <span class="badge bg-secondary">'+m.unidad_medida+'</span> <span class="text-success">Stock: '+m.stock_actual+'</span></li>')
          .on('click', function(){
            $inp.val(m.codigo);
            $row.find('.mat-id').val(m.id);
            $row.find('.mat-detalle').val(m.detalle);
            $row.find('.mat-unidad').val(m.unidad_medida);
            $drop.hide();
          }).appendTo($drop);
      });
    });
  });
  $(document).on('click', function(e){ if(!$(e.target).closest($inp).length) $drop.hide(); });
}

$(document).on('input', '.cant,.prec', function(){
  var $row = $(this).closest('tr');
  var c = parseFloat($row.find('.cant').val())||0;
  var p = parseFloat($row.find('.prec').val())||0;
  $row.find('.total-row').val((c*p).toFixed(2));
  updateTotal();
});

function updateTotal(){
  var t=0; $('.total-row').each(function(){ t+=parseFloat($(this).val())||0; });
  $('#totalGeneral').text(t.toFixed(2));
}

$('#btnAddRow').on('click', function(){ addRow(); });
$(function(){ addRow(); });
</script>
JS;

include ROOT_PATH . '/includes/footer.php';
