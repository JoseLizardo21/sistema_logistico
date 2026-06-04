<?php
/**
 * SCRIPT DE INSTALACION - Solo ejecutar una vez
 * URL: http://localhost/sis_logistico/setup.php
 */
define('ROOT_PATH', __DIR__);
define('BASE_URL', '/sis_logistico');
define('SITE_NAME', 'SIS. LOGISTICO DE ALMACEN');
require_once __DIR__ . '/config/database.php';

$messages = array();
$errors   = array();

$sqlFile = __DIR__ . '/database/sis_logistico.sql';
$sql     = file_get_contents($sqlFile);
$rawStatements = explode(';', $sql);
$statements = array();
foreach ($rawStatements as $s) {
    $s = trim($s);
    if (strlen($s) > 3) $statements[] = $s;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
    );

    foreach ($statements as $stmt) {
        $stmt = preg_replace('/^--.*$/m', '', $stmt);
        if (trim($stmt) === '') continue;
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate') === false) {
                $errors[] = substr($stmt,0,80) . ' - ' . $e->getMessage();
            }
        }
    }

    $pdo->exec("USE sis_logistico");
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE usuarios SET password=?")->execute(array($hash));

    $messages[] = 'Base de datos <b>sis_logistico</b> creada con todas las tablas.';
    $messages[] = 'Datos iniciales insertados (areas, responsables, materiales, clasificadores).';
    $messages[] = 'Contrasenas configuradas: <b>admin123</b> para todos los usuarios.';

} catch (PDOException $e) {
    $errors[] = 'Error de conexion: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Setup - Sistema Logistico</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:700px">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">Instalacion - Sistema Logistico de Almacen</h4>
    </div>
    <div class="card-body">
      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <h5>Errores:</h5>
          <ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
        </div>
      <?php endif; ?>
      <?php if ($messages): ?>
        <div class="alert alert-success">
          <?php foreach($messages as $m) echo '<p class="mb-1">OK: '.$m.'</p>'; ?>
        </div>
      <?php endif; ?>
      <?php if (!$errors): ?>
      <div class="alert alert-info">
        <h5>Credenciales de acceso:</h5>
        <table class="table table-sm mb-0">
          <tr><th>Usuario</th><th>Contrasena</th><th>Rol</th></tr>
          <tr><td>admin</td><td>admin123</td><td>Administrador</td></tr>
          <tr><td>gerente</td><td>admin123</td><td>Gerente</td></tr>
          <tr><td>asistente</td><td>admin123</td><td>Asistente</td></tr>
        </table>
      </div>
      <div class="alert alert-warning">
        Importante: Elimina este archivo setup.php despues de la instalacion.
      </div>
      <a href="/sis_logistico/auth/login.php" class="btn btn-primary btn-lg">Ir al Sistema</a>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
