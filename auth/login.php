<?php
require_once dirname(__DIR__) . '/config/init.php';

if (!empty($_SESSION['usuario_id'])) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');

    if ($username && $password) {
        $db  = getDB();
        $st  = $db->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = 1");
        $st->execute(array($username));
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id']      = $user['id'];
            $_SESSION['username']        = $user['username'];
            $_SESSION['nombre_completo'] = $user['nombre_completo'];
            $_SESSION['rol']             = $user['rol'];
            redirect(BASE_URL . '/index.php');
        } else {
            $error = 'Usuario o contrasena incorrectos.';
        }
    } else {
        $error = 'Ingresa usuario y contrasena.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesion — <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #1e2a3a 0%, #2c4a6e 100%); min-height: 100vh; display: flex; align-items: center; }
.login-card { border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
.login-header { background: linear-gradient(135deg, #1e2a3a, #3498db); border-radius: 16px 16px 0 0; padding: 32px; text-align: center; }
</style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="card login-card border-0">
        <div class="login-header">
          <i class="fas fa-warehouse text-white" style="font-size:2.5rem"></i>
          <h5 class="text-white mt-3 mb-0 fw-bold">SISTEMA LOGISTICO</h5>
          <small class="text-white-50">Almacen Municipal</small>
        </div>
        <div class="card-body p-4">
          <?php if ($error): ?>
            <div class="alert alert-danger py-2"><i class="fas fa-times-circle me-2"></i><?php echo h($error); ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Usuario</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control" required autofocus
                       value="<?php echo h(isset($_POST['username']) ? $_POST['username'] : ''); ?>">
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Contrasena</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
              <i class="fas fa-sign-in-alt me-2"></i>Ingresar
            </button>
          </form>
          <hr>
          <small class="text-muted d-block text-center">
            Cuentas: <b>admin</b> / <b>gerente</b> / <b>asistente</b><br>Contrasena: <b>admin123</b>
          </small>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
