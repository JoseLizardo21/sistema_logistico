<?php
require_once dirname(__DIR__) . '/config/init.php';
requireRole(array('administrador'));

$db  = getDB();
$id  = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
$row = null;
if ($id) { $st = $db->prepare("SELECT * FROM responsables WHERE id=?"); $st->execute(array($id)); $row = $st->fetch(); }
if ($id && !$row) { setFlash('danger','No encontrado.'); redirect(BASE_URL.'/responsables/index.php'); }

$areas  = $db->query("SELECT id,codigo,nombre FROM areas_usuarias WHERE activo=1 ORDER BY codigo")->fetchAll();
$errors = array();
$pageTitle = $id ? 'Editar Responsable' : 'Nuevo Responsable';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo  = strtoupper(trim(isset($_POST['codigo']) ? $_POST['codigo'] : ''));
    $nombres = trim(isset($_POST['nombres_apellidos']) ? $_POST['nombres_apellidos'] : '');
    $dni     = trim(isset($_POST['dni']) ? $_POST['dni'] : '');
    $area_id = (int)(isset($_POST['area_id']) ? $_POST['area_id'] : 0);
    $area_id = $area_id ?: null;

    if (!$codigo)  $errors[] = 'El codigo es requerido.';
    if (!$nombres) $errors[] = 'Los nombres son requeridos.';
    if (!$dni)     $errors[] = 'El DNI es requerido.';

    if (!$errors) {
        if ($id) {
            $db->prepare("UPDATE responsables SET codigo=?,nombres_apellidos=?,dni=?,area_id=? WHERE id=?")
               ->execute(array($codigo,$nombres,$dni,$area_id,$id));
            setFlash('success','Actualizado.');
        } else {
            try {
                $db->prepare("INSERT INTO responsables(codigo,nombres_apellidos,dni,area_id) VALUES(?,?,?,?)")
                   ->execute(array($codigo,$nombres,$dni,$area_id));
                setFlash('success','Creado.');
            } catch (PDOException $e) { $errors[] = 'El codigo ya existe.'; }
        }
        if (!$errors) redirect(BASE_URL.'/responsables/index.php');
    }
}

include ROOT_PATH . '/includes/header.php';
$valCodigo  = isset($_POST['codigo']) ? $_POST['codigo'] : (isset($row['codigo']) ? $row['codigo'] : '');
$valNombres = isset($_POST['nombres_apellidos']) ? $_POST['nombres_apellidos'] : (isset($row['nombres_apellidos']) ? $row['nombres_apellidos'] : '');
$valDni     = isset($_POST['dni']) ? $_POST['dni'] : (isset($row['dni']) ? $row['dni'] : '');
$selArea    = isset($_POST['area_id']) ? $_POST['area_id'] : (isset($row['area_id']) ? $row['area_id'] : '');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/responsables/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-user-tie me-2 text-primary"></i><?php echo $pageTitle; ?></h4>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
<div class="card table-card" style="max-width:560px">
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Codigo <span class="text-danger">*</span></label>
          <input type="text" name="codigo" class="form-control" required value="<?php echo h($valCodigo); ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label fw-semibold">Nombres y Apellidos <span class="text-danger">*</span></label>
          <input type="text" name="nombres_apellidos" class="form-control" required value="<?php echo h($valNombres); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">N DNI <span class="text-danger">*</span></label>
          <input type="text" name="dni" class="form-control" maxlength="15" required value="<?php echo h($valDni); ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label fw-semibold">Area Usuaria</label>
          <select name="area_id" class="form-select">
            <option value="">-- Sin asignar --</option>
            <?php foreach ($areas as $a): ?>
            <option value="<?php echo $a['id']; ?>"<?php echo ($selArea == $a['id'] ? ' selected' : ''); ?>><?php echo h($a['codigo'].' - '.$a['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?php echo $id?'Actualizar':'Guardar'; ?></button>
        <a href="<?php echo BASE_URL; ?>/responsables/index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
