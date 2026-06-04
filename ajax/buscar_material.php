<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');
$q  = trim(isset($_GET['q']) ? $_GET['q'] : '');
$db = getDB();

if (!$q) { echo '[]'; exit; }

$st = $db->prepare(
    "SELECT id, codigo, detalle, unidad_medida, stock_actual
     FROM materiales
     WHERE activo=1 AND (codigo LIKE ? OR detalle LIKE ?)
     ORDER BY codigo LIMIT 15"
);
$st->execute(array("%$q%", "%$q%"));
$rows = $st->fetchAll();

echo json_encode($rows);
