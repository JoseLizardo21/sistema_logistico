<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

$db = getDB();
$pageTitle = 'Áreas Usuarias';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((isset($_POST['_action']) ? $_POST['_action'] : '')) === 'delete') {
    requireRole(['administrador']);
    $db->prepare("UPDATE areas_usuarias SET activo=0 WHERE id=?")->execute([(int)$_POST['id']]);
    setFlash('success','Área eliminada.');
    redirect(BASE_URL.'/areas/index.php');
}

$areas = $db->query(
    "SELECT a.*, r.nombres_apellidos AS responsable
     FROM areas_usuarias a
     LEFT JOIN responsables r ON r.area_id=a.id AND r.activo=1
     WHERE a.activo=1 ORDER BY a.codigo"
)->fetchAll();

include ROOT_PATH . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i>Áreas Usuarias</h4>
  <?php if (isAdmin()): ?>
  <a href="<?= BASE_URL ?>/areas/form.php" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i>Nueva Área
  </a>
  <?php endif; ?>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle datatable mb-0">
      <thead class="table-dark">
        <tr><th>Código</th><th>Área Usuaria</th><th>Responsable</th><th class="text-center">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($areas as $a): ?>
        <tr>
          <td><code><?= h($a['codigo']) ?></code></td>
          <td><?= h($a['nombre']) ?></td>
          <td><?= h((isset($a['responsable']) ? $a['responsable'] : '—')) ?></td>
          <td class="text-center">
            <?php if (isAdmin()): ?>
            <a href="<?= BASE_URL ?>/areas/form.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-warning">
              <i class="fas fa-edit"></i>
            </a>
            <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar área?')">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
            <?php else: ?>—<?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
