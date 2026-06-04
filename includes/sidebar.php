<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <i class="fas fa-warehouse me-2"></i>
    <span>ALMACÉN</span>
  </div>
  <ul class="sidebar-menu">

    <li class="sidebar-item<?= (basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['REQUEST_URI'], '/sis_logistico/index') !== false) || $_SERVER['REQUEST_URI'] === BASE_URL . '/' ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/index.php">
        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
      </a>
    </li>

    <li class="sidebar-heading">MANTENIMIENTO</li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/materiales/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/materiales/index.php">
        <i class="fas fa-boxes"></i><span>Materiales</span>
      </a>
    </li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/areas/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/areas/index.php">
        <i class="fas fa-building"></i><span>Áreas Usuarias</span>
      </a>
    </li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/responsables/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/responsables/index.php">
        <i class="fas fa-user-tie"></i><span>Responsables</span>
      </a>
    </li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/clasificadores/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/clasificadores/index.php">
        <i class="fas fa-tags"></i><span>Clasificadores</span>
      </a>
    </li>

    <li class="sidebar-heading">ALMACÉN</li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/entradas/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/entradas/index.php">
        <i class="fas fa-arrow-circle-down text-success"></i><span>Entradas (O.C.)</span>
      </a>
    </li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/salidas/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/salidas/index.php">
        <i class="fas fa-arrow-circle-up text-danger"></i><span>Salidas (Papeleta)</span>
      </a>
    </li>

    <li class="sidebar-heading">REPORTES</li>

    <li class="sidebar-item<?= basename($_SERVER['PHP_SELF']) === 'stock.php' ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/reportes/stock.php">
        <i class="fas fa-chart-bar"></i><span>Stock de Materiales</span>
      </a>
    </li>

    <li class="sidebar-item<?= basename($_SERVER['PHP_SELF']) === 'kardex.php' ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/reportes/kardex.php">
        <i class="fas fa-list-alt"></i><span>Kardex</span>
      </a>
    </li>

    <li class="sidebar-item<?= basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['REQUEST_URI'], '/reportes/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/reportes/index.php">
        <i class="fas fa-chart-pie"></i><span>Reportes</span>
      </a>
    </li>

    <?php if (isAdmin()): ?>
    <li class="sidebar-heading">ADMINISTRACIÓN</li>

    <li class="sidebar-item<?= strpos($_SERVER['REQUEST_URI'], '/usuarios/') !== false ? ' active' : '' ?>">
      <a href="<?= BASE_URL ?>/usuarios/index.php">
        <i class="fas fa-users-cog"></i><span>Usuarios</span>
      </a>
    </li>
    <?php endif; ?>

  </ul>
</nav>
