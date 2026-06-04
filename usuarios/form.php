<?php
require_once dirname(__DIR__) . '/config/init.php';
requireRole(array('administrador'));
$db  = getDB();
$id  = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
$row = null;
if ($id) { $st=$db->prepare("SELECT * FROM usuarios WHERE id=?"); $st->execute(array($id)); $row=$st->fetch(); }
if ($id && !$row) { setFlash('danger','No encontrado.'); redirect(BASE_URL.'/usuarios/index.php'); }

$pageTitle = $id ? 'Editar Usuario' : 'Nuevo Usuario';
$errors    = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $nombre    = trim(isset($_POST['nombre_completo']) ? $_POST['nombre_completo'] : '');
    $email     = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $rol       = trim(isset($_POST['rol']) ? $_POST['rol'] : 'asistente');
    $password  = trim(isset($_POST['password']) ? $_POST['password'] : '');
    $password2 = trim(isset($_POST['password2']) ? $_POST['password2'] : '');

    if (!$username) $errors[]='Usuario requerido.';
    if (!$nombre)   $errors[]='Nombre requerido.';
    if (!$id && !$password) $errors[]='Contrasena requerida.';
    if ($password && $password !== $password2) $errors[]='Las contrasenas no coinciden.';

    if (!$errors) {
        if ($id) {
            $params = array($username,$nombre,$email,$rol);
            $sql = "UPDATE usuarios SET username=?,nombre_completo=?,email=?,rol=?";
            if ($password) { $sql .= ",password=?"; $params[]=password_hash($password,PASSWORD_BCRYPT); }
            $sql .= " WHERE id=?"; $params[]=$id;
            try { $db->prepare($sql)->execute($params); setFlash('success','Actualizado.'); }
            catch (PDOException $e) { $errors[]='El usuario ya existe.'; }
        } else {
            try {
                $db->prepare("INSERT INTO usuarios(username,password,nombre_completo,email,rol) VALUES(?,?,?,?,?)")
                   ->execute(array($username,password_hash($password,PASSWORD_BCRYPT),$nombre,$email,$rol));
                setFlash('success','Usuario creado.');
            } catch (PDOException $e) { $errors[]='El usuario ya existe.'; }
        }
        if (!$errors) redirect(BASE_URL.'/usuarios/index.php');
    }
}

include ROOT_PATH . '/includes/header.php';
$vUser  = isset($_POST['username']) ? $_POST['username'] : (isset($row['username']) ? $row['username'] : '');
$vNom   = isset($_POST['nombre_completo']) ? $_POST['nombre_completo'] : (isset($row['nombre_completo']) ? $row['nombre_completo'] : '');
$vEmail = isset($_POST['email']) ? $_POST['email'] : (isset($row['email']) ? $row['email'] : '');
$vRol   = isset($_POST['rol']) ? $_POST['rol'] : (isset($row['rol']) ? $row['rol'] : 'asistente');
$rols   = array('administrador','gerente','asistente');
?>
<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?php echo BASE_URL; ?>/usuarios/index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
  <h4 class="fw-bold mb-0"><i class="fas fa-user-cog me-2 text-primary"></i><?php echo $pageTitle; ?></h4>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
<div class="card table-card" style="max-width:560px">
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" required value="<?php echo h($vUser); ?>">
        </div>
        <div class="col-md-7">
          <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
          <input type="text" name="nombre_completo" class="form-control" required value="<?php echo h($vNom); ?>">
        </div>
        <div class="col-md-7">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" value="<?php echo h($vEmail); ?>">
        </div>
        <div class="col-md-5">
          <label class="form-label fw-semibold">Rol</label>
          <select name="rol" class="form-select">
            <?php foreach($rols as $r) echo '<option value="'.$r.'"'.($vRol===$r?' selected':'').'>'.ucfirst($r).'</option>'; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Contrasena <?php echo $id ? '<small class="text-muted">(dejar vacio para no cambiar)</small>' : '<span class="text-danger">*</span>'; ?></label>
          <input type="password" name="password" class="form-control" <?php echo !$id?'required':''; ?>>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Confirmar Contrasena</label>
          <input type="password" name="password2" class="form-control">
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?php echo $id?'Actualizar':'Crear Usuario'; ?></button>
        <a href="<?php echo BASE_URL; ?>/usuarios/index.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
