<?php
require_once dirname(__DIR__) . '/config/init.php';
requireRole(array('administrador'));
$db  = getDB();
$id  = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
$row = null;
if ($id) { $st=$db->prepare("SELECT * FROM clasificadores WHERE id=?"); $st->execute(array($id)); $row=$st->fetch(); }
if ($id && !$row) { setFlash('danger','No encontrado.'); redirect(BASE_URL.'/clasificadores/index.php'); }

$pageTitle = $id ? 'Editar Clasificador' : 'Nuevo Clasificador';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num    = trim(isset($_POST['num_clasificador']) ? $_POST['num_clasificador'] : '');
    $detalle= trim(isset($_POST['detalle']) ? $_POST['detalle'] : '');
    if (!$num)     $errors[] = 'El numero es requerido.';
    if (!$detalle) $errors[] = 'El detalle es requerido.';
    if (!$errors) {
        if ($id) {
            $db->prepare("UPDATE clasificadores SET num_clasificador=?,detalle=? WHERE id=?")->execute(array($num,$detalle,$id));
            setFlash('success','Actualizado.');
        } else {
            try { $db->prepare("INSERT INTO clasificadores(num_clasificador,detalle) VALUES(?,?)")->execute(array($num,$detalle)); setFlash('success','Creado.'); }
            catch (PDOException $e) { $errors[]='El numero ya existe.'; }
        }
        if (!$errors) redirect(BASE_URL.'/clasificadores/index.php');
    }
}

include ROOT_PATH . '/includes/header.php';
$valNum    = isset($_POST['num_clasificador']) ? $_POST['num_clasificador'] : (isset($row['num_clasificador']) ? $row['num_clasificador'] : '');
$valDetalle= isset($_POST['detalle']) ? $_POST['detalle'] : (isset($row['detalle']) ? $row['detalle'] : '');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/clasificadores/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-tags me-2 text-primary"></i><?php echo $pageTitle; ?></h4>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
<div class="card table-card" style="max-width:520px">
  <div class="card-body p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">N Clasificador <span class="text-danger">*</span></label>
        <input type="text" name="num_clasificador" class="form-control" required value="<?php echo h($valNum); ?>" placeholder="2.3.1.5.1.2">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Detalle <span class="text-danger">*</span></label>
        <input type="text" name="detalle" class="form-control" required value="<?php echo h($valDetalle); ?>">
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?php echo $id?'Actualizar':'Guardar'; ?></button>
        <a href="<?php echo BASE_URL; ?>/clasificadores/index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
