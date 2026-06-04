<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h(isset($pageTitle) ? $pageTitle : 'Inicio'); ?> — <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="wrapper">
<?php include ROOT_PATH . '/includes/sidebar.php'; ?>
<div class="main-content">
  <nav class="navbar navbar-expand-lg topbar shadow-sm">
    <div class="container-fluid">
      <button class="btn btn-link text-white fs-5 me-3" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </button>
      <span class="navbar-brand text-white fw-bold d-none d-md-block">
        <i class="fas fa-warehouse me-2"></i><?php echo SITE_NAME; ?>
      </span>
      <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-white small">
          <i class="fas fa-user-circle me-1"></i>
          <?php echo h(isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : ''); ?>
          <span class="badge bg-light text-dark ms-1"><?php echo h(ucfirst(isset($_SESSION['rol']) ? $_SESSION['rol'] : '')); ?></span>
        </span>
        <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-sm btn-outline-light">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </nav>
  <div class="content-body px-4 py-3">
    <?php echo getFlash(); ?>
