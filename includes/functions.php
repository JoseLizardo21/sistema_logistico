<?php
function requireLogin() {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireRole(array $roles) {
    requireLogin();
    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
    if (!in_array($rol, $roles)) {
        setFlash('danger', 'No tienes permisos para acceder a esta seccion.');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function isAdmin() {
    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
    return $rol === 'administrador';
}

function isGerente() {
    $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
    return in_array($rol, array('administrador', 'gerente'));
}

function setFlash($type, $msg) {
    $_SESSION['flash'] = array('type' => $type, 'msg' => $msg);
}

function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icons = array('success'=>'check-circle','danger'=>'times-circle','warning'=>'exclamation-triangle','info'=>'info-circle');
        $icon  = isset($icons[$f['type']]) ? $icons[$f['type']] : 'info-circle';
        return '<div class="alert alert-' . $f['type'] . ' alert-dismissible fade show" role="alert">'
             . '<i class="fas fa-' . $icon . ' me-2"></i>' . htmlspecialchars($f['msg'])
             . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    return '';
}

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function getNextPapeleta() {
    $db  = getDB();
    $row = $db->query("SELECT COALESCE(MAX(nro_papeleta),0)+1 AS next FROM papeletas_salida")->fetch();
    return (int)$row['next'];
}

function updateStock($materialId, $cantidad, $tipo) {
    $db  = getDB();
    $op  = ($tipo === 'entrada') ? '+' : '-';
    $sql = "UPDATE materiales SET stock_actual = stock_actual " . $op . " :cantidad WHERE id = :id";
    $st  = $db->prepare($sql);
    $st->execute(array(':cantidad' => $cantidad, ':id' => $materialId));
}

function insertKardex(array $data) {
    $db  = getDB();
    $sql = "INSERT INTO kardex
              (material_id, tipo, referencia_tipo, referencia_id, referencia_numero,
               area_id, cantidad, precio_unitario, saldo_cantidad, fecha, usuario_id)
            VALUES
              (:material_id, :tipo, :referencia_tipo, :referencia_id, :referencia_numero,
               :area_id, :cantidad, :precio_unitario, :saldo_cantidad, :fecha, :usuario_id)";
    $db->prepare($sql)->execute($data);
}

function getStockMaterial($materialId) {
    $db  = getDB();
    $row = $db->prepare("SELECT stock_actual FROM materiales WHERE id = ?");
    $row->execute(array($materialId));
    $r = $row->fetch();
    return $r ? (float)$r['stock_actual'] : 0.0;
}

function formatDate($date) {
    if (!$date) return '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d ? $d->format('d/m/Y') : $date;
}

function formatMoney($amount) {
    return 'S/. ' . number_format((float)$amount, 2);
}

function gp($arr, $key, $default = '') {
    return isset($arr[$key]) ? $arr[$key] : $default;
}
