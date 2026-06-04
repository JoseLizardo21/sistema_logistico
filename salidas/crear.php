<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

$db        = getDB();
$pageTitle = 'Nueva Papeleta de Salida';
$nextNum   = getNextPapeleta();
$errors    = array();
$areas     = $db->query("SELECT id,codigo,nombre FROM areas_usuarias WHERE activo=1 ORDER BY codigo")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha   = trim(isset($_POST['fecha'])   ? $_POST['fecha']   : date('Y-m-d'));
    $area_id = (int)(isset($_POST['area_id']) ? $_POST['area_id'] : 0);
    $resp_id = (int)(isset($_POST['responsable_id']) ? $_POST['responsable_id'] : 0);
    $resp_id = $resp_id ?: null;
    $obs     = trim(isset($_POST['observacion']) ? $_POST['observacion'] : '');

    $mat_ids    = isset($_POST['material_id']) ? $_POST['material_id'] : array();
    $cantidades = isset($_POST['cantidad'])    ? $_POST['cantidad']    : array();

    if (!$fecha)   $errors[] = 'La fecha es requerida.';
    if (!$area_id) $errors[] = 'Selecciona el area que retira.';
    if (empty($mat_ids)) $errors[] = 'Agrega al menos un material.';

    $items = array();
    foreach ($mat_ids as $i => $mid) {
        $mid  = (int)$mid;
        $cant = (float)str_replace(',', '.', isset($cantidades[$i]) ? $cantidades[$i] : 0);
        if ($mid && $cant > 0) {
            $stock = getStockMaterial($mid);
            if ($cant > $stock) {
                $m = $db->prepare("SELECT detalle FROM materiales WHERE id=?");
                $m->execute(array($mid)); $m = $m->fetch();
                $errors[] = "Stock insuficiente para '" . $m['detalle'] . "'. Disponible: " . $stock;
            } else {
                $items[] = array('material_id'=>$mid,'cantidad'=>$cant);
            }
        }
    }
    if (empty($items) && !$errors) $errors[] = 'Ningun item valido.';

    if (!$errors) {
        $db->beginTransaction();
        try {
            $nro = getNextPapeleta();
            $db->prepare(
                "INSERT INTO papeletas_salida(nro_papeleta,fecha,area_id,responsable_id,observacion,usuario_id)
                 VALUES(?,?,?,?,?,?)"
            )->execute(array($nro,$fecha,$area_id,$resp_id,$obs,$_SESSION['usuario_id']));
            $psId = (int)$db->lastInsertId();

            foreach ($items as $it) {
                $db->prepare(
                    "INSERT INTO papeletas_salida_detalle(papeleta_id,material_id,cantidad) VALUES(?,?,?)"
                )->execute(array($psId,$it['material_id'],$it['cantidad']));

                updateStock($it['material_id'], $it['cantidad'], 'salida');
                $saldo = getStockMaterial($it['material_id']);
                $nroStr = 'PS-'.str_pad($nro,5,'0',STR_PAD_LEFT);
                insertKardex(array(
                    ':material_id'       => $it['material_id'],
                    ':tipo'              => 'salida',
                    ':referencia_tipo'   => 'papeleta_salida',
                    ':referencia_id'     => $psId,
                    ':referencia_numero' => $nroStr,
                    ':area_id'           => $area_id,
                    ':cantidad'          => $it['cantidad'],
                    ':precio_unitario'   => 0,
                    ':saldo_cantidad'    => $saldo,
                    ':fecha'             => $fecha,
                    ':usuario_id'        => $_SESSION['usuario_id'],
                ));
            }
            $db->commit();
            setFlash('success', 'Papeleta PS-'.str_pad($nro,5,'0',STR_PAD_LEFT).' registrada.');
            redirect(BASE_URL.'/salidas/index.php');
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Error al guardar: '.$e->getMessage();
        }
    }
}

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/salidas/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-arrow-circle-up me-2 text-danger"></i>Nueva Papeleta de Salida</h4>
  <span class="badge bg-danger fs-6">N° PS-<?php echo str_pad($nextNum,5,'0',STR_PAD_LEFT); ?></span>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div>
<?php endif; ?>

<form method="POST" id="formSalida">
<div class="card table-card mb-3">
  <div class="card-header fw-semibold"><i class="fas fa-file-alt me-2"></i>Datos de la Papeleta</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-2">
        <label class="form-label fw-semibold">N° Correlativo</label>
        <input type="text" class="form-control" value="PS-<?php echo str_pad($nextNum,5,'0',STR_PAD_LEFT); ?>" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
        <input type="date" name="fecha" class="form-control" required value="<?php echo h(isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d')); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Area que Retira <span class="text-danger">*</span></label>
        <select name="area_id" class="form-select" id="areaSelect" required>
          <option value="">— Seleccionar —</option>
          <?php $selA = isset($_POST['area_id']) ? $_POST['area_id'] : ''; foreach ($areas as $a): ?>
          <option value="<?php echo $a['id']; ?>"<?php echo ($selA==$a['id']?' selected':''); ?>><?php echo h($a['codigo'].' — '.$a['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Responsable del Area</label>
        <input type="text" id="responsableNombre" class="form-control" readonly placeholder="Auto al seleccionar area">
        <input type="hidden" name="responsable_id" id="responsableId">
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Observacion</label>
        <input type="text" name="observacion" class="form-control" value="<?php echo h(isset($_POST['observacion']) ? $_POST['observacion'] : ''); ?>">
      </div>
    </div>
  </div>
</div>

<div class="card table-card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
    <span><i class="fas fa-list me-2"></i>Items a Despachar</span>
    <button type="button" class="btn btn-sm btn-danger" id="btnAddRow">
      <i class="fas fa-plus me-1"></i>Agregar Material
    </button>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="40">N°</th>
          <th>Codigo / Material</th>
          <th>Descripcion</th>
          <th width="90">Unidad</th>
          <th width="100">Stock Disp.</th>
          <th width="110">Cantidad</th>
          <th width="50"></th>
        </tr>
      </thead>
      <tbody id="itemsBody"></tbody>
    </table>
  </div>
</div>

<div class="d-flex gap-2">
  <button type="submit" class="btn btn-danger px-4">
    <i class="fas fa-save me-2"></i>Registrar Salida
  </button>
  <a href="<?php echo BASE_URL; ?>/salidas/index.php" class="btn btn-outline-secondary">Cancelar</a>
</div>
</form>

<?php
$extraJs = <<<'JS'
<script>
var rowCount = 0;

$('#areaSelect').on('change', function(){
  var aid = $(this).val();
  if(!aid){ $('#responsableNombre').val(''); $('#responsableId').val(''); return; }
  $.getJSON(BASE_URL+'/ajax/get_area_info.php', {area_id: aid}, function(data){
    if(data.responsable_nombre){
      $('#responsableNombre').val(data.responsable_nombre);
      $('#responsableId').val(data.responsable_id);
    } else {
      $('#responsableNombre').val('Sin responsable asignado');
      $('#responsableId').val('');
    }
  });
});

function addRow(){
  rowCount++;
  var n = rowCount;
  var row = '<tr id="row'+n+'">'
    + '<td>'+n+'</td>'
    + '<td style="min-width:220px;position:relative">'
    + '<input type="text" class="form-control form-control-sm mat-search" placeholder="Codigo o nombre..." autocomplete="off" data-row="'+n+'">'
    + '<input type="hidden" name="material_id[]" class="mat-id" value="">'
    + '</td>'
    + '<td><input type="text" class="form-control form-control-sm" readonly id="det'+n+'"></td>'
    + '<td><input type="text" class="form-control form-control-sm" readonly id="und'+n+'"></td>'
    + '<td><input type="text" class="form-control form-control-sm text-end fw-bold" readonly id="stk'+n+'"></td>'
    + '<td><input type="number" name="cantidad[]" class="form-control form-control-sm" min="0.01" step="0.01" value="1" required></td>'
    + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="$(\'#row'+n+'\').remove()"><i class="fas fa-times"></i></button></td>'
    + '</tr>';
  $('#itemsBody').append(row);
  setupSearch(n);
}

function setupSearch(n){
  var $row = $('#row'+n);
  var $inp = $row.find('.mat-search');
  var $drop = $('<ul class="list-group position-absolute z-3" style="min-width:340px;max-height:220px;overflow-y:auto"></ul>');
  $inp.after($drop);

  $inp.on('input', function(){
    var q=$(this).val().trim();
    if(q.length<1){$drop.hide();return;}
    $.getJSON(BASE_URL+'/ajax/buscar_material.php',{q:q},function(data){
      $drop.empty().show();
      if(!data.length){$drop.append('<li class="list-group-item small py-1">Sin resultados</li>');return;}
      $.each(data, function(i, m){
        var stk = parseFloat(m.stock_actual);
        var stkClass = stk<=0?'text-danger':(stk<=5?'text-warning':'text-success');
        $('<li class="list-group-item list-group-item-action small py-1 px-2" style="cursor:pointer"><b>'+m.codigo+'</b> — '+m.detalle+' <span class="badge bg-secondary">'+m.unidad_medida+'</span> <span class="'+stkClass+'">Stock: '+stk+'</span></li>')
          .on('click',function(){
            $inp.val(m.codigo);
            $row.find('.mat-id').val(m.id);
            $('#det'+n).val(m.detalle);
            $('#und'+n).val(m.unidad_medida);
            $('#stk'+n).val(stk);
            $drop.hide();
          }).appendTo($drop);
      });
    });
  });
  $(document).on('click',function(e){if(!$(e.target).closest($inp).length)$drop.hide();});
}

$('#btnAddRow').on('click', addRow);
$(function(){ addRow(); });
</script>
JS;

include ROOT_PATH . '/includes/footer.php';
