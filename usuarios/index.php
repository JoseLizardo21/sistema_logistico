<?php
require_once dirname(__DIR__) . '/config/init.php';
requireRole(['administrador']);
$db = getDB();
$pageTitle = 'Usuarios del Sistema';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((isset($_POST['_action']) ? $_POST['_action'] : '')) === 'toggle') {
    $id  = (int)$_POST['id'];
    $act = (int)$_POST['activo'];
    if ($id !== (int)$_SESSION['usuario_id']) {
        $db->prepare("UPDATE usuarios SET activo=? WHERE id=?")->execute([$act?0:1,$id]);
        setFlash('success','Estado actualizado.');
    }
    redirect(BASE_URL.'/usuarios/index.php');
}

$rows = $db->query("SELECT * FROM usuarios ORDER BY rol, nombre_completo")->fetchAll();
include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-users-cog me-2 text-primary"></i>Usuarios del Sistema</h4>
  <a href="<?= BASE_URL ?>/usuarios/form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nuevo Usuario</a>
</div>
<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover datatable mb-0">
      <thead class="table-dark">
        <tr><th>Usuario</th><th>Nombre Completo</th><th>Email</th><th>Rol</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $u): ?>
        <tr>
          <td><code><?= h($u['username']) ?></code></td>
          <td><?= h($u['nombre_completo']) ?></td>
          <td><?= h((isset($u['email']) ? $u['email'] : '—')) ?></td>
          <td>
            <?php $badge=['administrador'=>'danger','gerente'=>'warning','asistente'=>'info'][$u['rol']]?: 'secondary'; ?>
            <span class="badge bg-<?= $badge ?>"><?= ucfirst($u['rol']) ?></span>
          </td>
          <td class="text-center">
            <span class="badge <?= $u['activo']?'bg-success':'bg-secondary' ?>">
              <?= $u['activo']?'Activo':'Inactivo' ?>
            </span>
          </td>
          <td class="text-center">
            <a href="<?= BASE_URL ?>/usuarios/form.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
            <?php if ($u['id'] !== (int)$_SESSION['usuario_id']): ?>
            <form method="POST" class="d-inline">
              <input type="hidden" name="_action" value="toggle">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <input type="hidden" name="activo" value="<?= $u['activo'] ?>">
              <button class="btn btn-sm <?= $u['activo']?'btn-outline-danger':'btn-outline-success' ?>">
                <i class="fas fa-<?= $u['activo']?'ban':'check' ?>"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
