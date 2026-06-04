<?php
require_once dirname(__DIR__) . '/config/init.php';
requireRole(array('administrador','gerente','asistente'));

$db  = getDB();
$id  = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
$mat = null;
if ($id) { $st = $db->prepare("SELECT * FROM materiales WHERE id=?"); $st->execute(array($id)); $mat = $st->fetch(); }
if ($id && !$mat) { setFlash('danger','Material no encontrado.'); redirect(BASE_URL.'/materiales/index.php'); }

$pageTitle = $id ? 'Editar Material' : 'Nuevo Material';
$errors    = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo  = strtoupper(trim(isset($_POST['codigo'])  ? $_POST['codigo']  : ''));
    $detalle = trim(isset($_POST['detalle'])  ? $_POST['detalle']  : '');
    $unidad  = trim(isset($_POST['unidad_medida']) ? $_POST['unidad_medida'] : '');

    if (!$codigo)  $errors[] = 'El codigo es requerido.';
    if (!$detalle) $errors[] = 'El detalle es requerido.';
    if (!$unidad)  $errors[] = 'La unidad de medida es requerida.';

    if (!$errors) {
        if ($id) {
            $db->prepare("UPDATE materiales SET codigo=?,detalle=?,unidad_medida=? WHERE id=?")
               ->execute(array($codigo,$detalle,$unidad,$id));
            setFlash('success','Material actualizado correctamente.');
        } else {
            try {
                $db->prepare("INSERT INTO materiales(codigo,detalle,unidad_medida) VALUES(?,?,?)")
                   ->execute(array($codigo,$detalle,$unidad));
                setFlash('success','Material creado correctamente.');
            } catch (PDOException $e) {
                $errors[] = 'El codigo ya existe.';
            }
        }
        if (!$errors) redirect(BASE_URL.'/materiales/index.php');
    }
}

include ROOT_PATH . '/includes/header.php';
$valCodigo  = isset($_POST['codigo'])  ? $_POST['codigo']  : (isset($mat['codigo'])  ? $mat['codigo']  : '');
$valDetalle = isset($_POST['detalle']) ? $_POST['detalle'] : (isset($mat['detalle']) ? $mat['detalle'] : '');
$valUnidad  = isset($_POST['unidad_medida']) ? $_POST['unidad_medida'] : (isset($mat['unidad_medida']) ? $mat['unidad_medida'] : '');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/materiales/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-box me-2 text-primary"></i><?php echo $pageTitle; ?></h4>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
<div class="card table-card" style="max-width:600px">
  <div class="card-body p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Codigo de Material <span class="text-danger">*</span></label>
        <input type="text" name="codigo" class="form-control" required value="<?php echo h($valCodigo); ?>" placeholder="Ej: MAT-035">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Nombre / Detalle <span class="text-danger">*</span></label>
        <textarea name="detalle" class="form-control" rows="3" required><?php echo h($valDetalle); ?></textarea>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Unidad de Medida <span class="text-danger">*</span></label>
        <select name="unidad_medida" class="form-select" required>
          <?php
          $uds = array('UND','RESMA','CAJA','PAQUETE','CIENTO','MILLAR','ROLLO','METRO','KG','LITRO','JUEGO','DOCENA');
          foreach ($uds as $u) echo '<option value="'.$u.'"'.($valUnidad===$u?' selected':'').'>'.$u.'</option>';
          ?>
        </select>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?php echo $id?'Actualizar':'Guardar'; ?></button>
        <a href="<?php echo BASE_URL; ?>/materiales/index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
