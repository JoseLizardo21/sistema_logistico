<?php
require_once dirname(__DIR__) . '/config/init.php';
requireRole(array('administrador'));

$db  = getDB();
$id  = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
$row = null;
if ($id) { $st = $db->prepare("SELECT * FROM areas_usuarias WHERE id=?"); $st->execute(array($id)); $row = $st->fetch(); }
if ($id && !$row) { setFlash('danger','Area no encontrada.'); redirect(BASE_URL.'/areas/index.php'); }

$pageTitle = $id ? 'Editar Area' : 'Nueva Area';
$errors    = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim(isset($_POST['codigo']) ? $_POST['codigo'] : ''));
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');

    if (!$codigo) $errors[] = 'El codigo es requerido.';
    if (!$nombre) $errors[] = 'El nombre es requerido.';

    if (!$errors) {
        if ($id) {
            $db->prepare("UPDATE areas_usuarias SET codigo=?,nombre=? WHERE id=?")->execute(array($codigo,$nombre,$id));
            setFlash('success','Area actualizada.');
        } else {
            try {
                $db->prepare("INSERT INTO areas_usuarias(codigo,nombre) VALUES(?,?)")->execute(array($codigo,$nombre));
                setFlash('success','Area creada.');
            } catch (PDOException $e) { $errors[] = 'El codigo ya existe.'; }
        }
        if (!$errors) redirect(BASE_URL.'/areas/index.php');
    }
}

include ROOT_PATH . '/includes/header.php';
$valCodigo = isset($_POST['codigo']) ? $_POST['codigo'] : (isset($row['codigo']) ? $row['codigo'] : '');
$valNombre = isset($_POST['nombre']) ? $_POST['nombre'] : (isset($row['nombre']) ? $row['nombre'] : '');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/areas/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i><?php echo $pageTitle; ?></h4>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
<div class="card table-card" style="max-width:500px">
  <div class="card-body p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Codigo <span class="text-danger">*</span></label>
        <input type="text" name="codigo" class="form-control" required value="<?php echo h($valCodigo); ?>" placeholder="0016">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Nombre del Area <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control" required value="<?php echo h($valNombre); ?>">
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?php echo $id?'Actualizar':'Guardar'; ?></button>
        <a href="<?php echo BASE_URL; ?>/areas/index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
