<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');
$db = getDB();

if (!empty($_GET['area_id'])) {
    $area_id = (int)$_GET['area_id'];
    $st = $db->prepare(
        "SELECT r.id AS responsable_id, r.nombres_apellidos AS responsable_nombre
         FROM responsables r WHERE r.area_id=? AND r.activo=1 LIMIT 1"
    );
    $st->execute(array($area_id));
    $r = $st->fetch();
    if ($r) {
        echo json_encode(array('responsable_id'=>$r['responsable_id'],'responsable_nombre'=>$r['responsable_nombre']));
    } else {
        echo json_encode(array('responsable_id'=>null,'responsable_nombre'=>null));
    }
    exit;
}

$q = trim(isset($_GET['q']) ? $_GET['q'] : '');
if (!$q) { echo '[]'; exit; }

$st = $db->prepare(
    "SELECT id, codigo, nombre FROM areas_usuarias
     WHERE activo=1 AND (codigo LIKE ? OR nombre LIKE ?)
     ORDER BY codigo LIMIT 10"
);
$st->execute(array("%$q%", "%$q%"));
echo json_encode($st->fetchAll());
