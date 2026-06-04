<?php
require_once dirname(__DIR__) . '/config/init.php';
requireLogin();
$db   = getDB();
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'kardex';

function csvRow(array $row) {
    return implode(';', array_map(function($v) {
        $v = str_replace('"','""',$v);
        return '"'.$v.'"';
    }, $row)) . "\r\n";
}

$filename = 'sis_logistico_' . $tipo . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
echo "\xEF\xBB\xBF"; // UTF-8 BOM para Excel

if ($tipo === 'stock') {
    $q = trim(isset($_GET['q']) ? $_GET['q'] : '');
    $sql = "SELECT m.codigo, m.detalle, m.unidad_medida, m.stock_actual,
                   COALESCE(SUM(CASE WHEN k.tipo='entrada' THEN k.cantidad ELSE 0 END),0) AS total_entradas,
                   COALESCE(SUM(CASE WHEN k.tipo='salida'  THEN k.cantidad ELSE 0 END),0) AS total_salidas
            FROM materiales m LEFT JOIN kardex k ON k.material_id=m.id
            WHERE m.activo=1";
    $params = array();
    if ($q) { $sql .= " AND (m.codigo LIKE ? OR m.detalle LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
    $sql .= " GROUP BY m.id ORDER BY m.detalle";
    $st = $db->prepare($sql); $st->execute($params);
    $rows = $st->fetchAll();

    echo csvRow(array('CODIGO','DESCRIPCION','UNIDAD MEDIDA','TOTAL ENTRADAS','TOTAL SALIDAS','STOCK ACTUAL'));
    foreach ($rows as $r) {
        echo csvRow(array($r['codigo'],$r['detalle'],$r['unidad_medida'],
                     number_format((float)$r['total_entradas'],2,'.',''),
                     number_format((float)$r['total_salidas'],2,'.',''),
                     number_format((float)$r['stock_actual'],2,'.','')));
    }

} elseif ($tipo === 'kardex') {
    $f_mat   = trim(isset($_GET['material']) ? $_GET['material'] : '');
    $f_desde = trim(isset($_GET['desde'])    ? $_GET['desde']    : '');
    $f_hasta = trim(isset($_GET['hasta'])    ? $_GET['hasta']    : '');
    $f_area  = trim(isset($_GET['area'])     ? $_GET['area']     : '');

    $sql = "SELECT k.fecha, m.codigo, m.detalle, m.unidad_medida, k.tipo,
                   k.referencia_numero, a.nombre AS area,
                   k.cantidad, k.precio_unitario, k.saldo_cantidad,
                   u.nombre_completo AS usuario
            FROM kardex k
            JOIN materiales m ON m.id=k.material_id
            LEFT JOIN areas_usuarias a ON a.id=k.area_id
            LEFT JOIN usuarios u ON u.id=k.usuario_id
            WHERE 1=1";
    $params = array();
    if ($f_mat)   { $sql .= " AND (m.codigo LIKE ? OR m.detalle LIKE ?)"; $params[] = "%$f_mat%"; $params[] = "%$f_mat%"; }
    if ($f_desde) { $sql .= " AND k.fecha >= ?"; $params[] = $f_desde; }
    if ($f_hasta) { $sql .= " AND k.fecha <= ?"; $params[] = $f_hasta; }
    if ($f_area)  { $sql .= " AND (a.nombre LIKE ? OR a.codigo LIKE ?)"; $params[] = "%$f_area%"; $params[] = "%$f_area%"; }
    $sql .= " ORDER BY k.fecha, k.id";
    $st = $db->prepare($sql); $st->execute($params);
    $rows = $st->fetchAll();

    echo csvRow(array('FECHA','CODIGO','DESCRIPCION','UNIDAD','TIPO','REFERENCIA','AREA','CANTIDAD','PRECIO UNIT.','SALDO','USUARIO'));
    foreach ($rows as $r) {
        echo csvRow(array(
            $r['fecha'], $r['codigo'], $r['detalle'], $r['unidad_medida'],
            strtoupper($r['tipo']), isset($r['referencia_numero']) ? $r['referencia_numero'] : '',
            isset($r['area']) ? $r['area'] : '',
            number_format((float)$r['cantidad'],2,'.',''),
            number_format((float)$r['precio_unitario'],4,'.',''),
            number_format((float)$r['saldo_cantidad'],2,'.',''),
            isset($r['usuario']) ? $r['usuario'] : ''
        ));
    }

} elseif ($tipo === 'consolidado') {
    $f_desde = trim(isset($_GET['desde']) ? $_GET['desde'] : date('Y-01-01'));
    $f_hasta = trim(isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-d'));

    echo csvRow(array('=== CONSOLIDADO SISTEMA LOGISTICO ==='));
    echo csvRow(array('Periodo:', $f_desde, 'al', $f_hasta));
    echo csvRow(array('Generado:', date('d/m/Y H:i')));
    echo csvRow(array());

    echo csvRow(array('--- RESUMEN POR MATERIAL ---'));
    $rows = $db->prepare(
        "SELECT m.codigo, m.detalle, m.unidad_medida, m.stock_actual,
                COALESCE(SUM(CASE WHEN k.tipo='entrada' AND k.fecha BETWEEN ? AND ? THEN k.cantidad ELSE 0 END),0) AS entradas,
                COALESCE(SUM(CASE WHEN k.tipo='salida'  AND k.fecha BETWEEN ? AND ? THEN k.cantidad ELSE 0 END),0) AS salidas
         FROM materiales m LEFT JOIN kardex k ON k.material_id=m.id
         WHERE m.activo=1 GROUP BY m.id ORDER BY m.detalle"
    );
    $rows->execute(array($f_desde,$f_hasta,$f_desde,$f_hasta));
    echo csvRow(array('CODIGO','DESCRIPCION','UNIDAD','ENTRADAS PERIODO','SALIDAS PERIODO','STOCK ACTUAL'));
    foreach ($rows->fetchAll() as $r) {
        echo csvRow(array($r['codigo'],$r['detalle'],$r['unidad_medida'],
                     number_format((float)$r['entradas'],2,'.',''),
                     number_format((float)$r['salidas'],2,'.',''),
                     number_format((float)$r['stock_actual'],2,'.','')));
    }

    echo csvRow(array());
    echo csvRow(array('--- DETALLE DE ENTRADAS (ORDENES DE COMPRA) ---'));
    $oc = $db->prepare(
        "SELECT oc.nro_orden_compra, oc.fecha_compra, a.nombre AS area, oc.nea, oc.pecosa,
                c.num_clasificador, m.codigo, m.detalle, d.cantidad, d.precio_unitario,
                (d.cantidad*d.precio_unitario) AS total
         FROM ordenes_compra oc
         JOIN areas_usuarias a ON a.id=oc.area_id
         LEFT JOIN clasificadores c ON c.id=oc.clasificador_id
         JOIN ordenes_compra_detalle d ON d.orden_compra_id=oc.id
         JOIN materiales m ON m.id=d.material_id
         WHERE oc.fecha_compra BETWEEN ? AND ?
         ORDER BY oc.fecha_compra, oc.nro_orden_compra"
    );
    $oc->execute(array($f_desde,$f_hasta));
    echo csvRow(array('N° O.C.','FECHA','AREA','NEA','PECOSA','CLASIFICADOR','COD MAT.','MATERIAL','CANTIDAD','PRECIO UNIT.','TOTAL'));
    foreach ($oc->fetchAll() as $r) {
        echo csvRow(array($r['nro_orden_compra'],formatDate($r['fecha_compra']),$r['area'],
                     isset($r['nea']) ? $r['nea'] : '',isset($r['pecosa']) ? $r['pecosa'] : '',
                     isset($r['num_clasificador']) ? $r['num_clasificador'] : '',
                     $r['codigo'],$r['detalle'],
                     number_format((float)$r['cantidad'],2,'.',''),
                     number_format((float)$r['precio_unitario'],4,'.',''),
                     number_format((float)$r['total'],2,'.','')));
    }

    echo csvRow(array());
    echo csvRow(array('--- DETALLE DE SALIDAS (PAPELETAS) ---'));
    $ps = $db->prepare(
        "SELECT ps.nro_papeleta, ps.fecha, a.nombre AS area, r.nombres_apellidos AS responsable,
                m.codigo, m.detalle, d.cantidad, m.unidad_medida
         FROM papeletas_salida ps
         JOIN areas_usuarias a ON a.id=ps.area_id
         LEFT JOIN responsables r ON r.id=ps.responsable_id
         JOIN papeletas_salida_detalle d ON d.papeleta_id=ps.id
         JOIN materiales m ON m.id=d.material_id
         WHERE ps.fecha BETWEEN ? AND ? AND ps.estado='activo'
         ORDER BY ps.fecha, ps.nro_papeleta"
    );
    $ps->execute(array($f_desde,$f_hasta));
    echo csvRow(array('N° PAPELETA','FECHA','AREA','RESPONSABLE','COD MAT.','MATERIAL','CANTIDAD','UNIDAD'));
    foreach ($ps->fetchAll() as $r) {
        echo csvRow(array(str_pad($r['nro_papeleta'],5,'0',STR_PAD_LEFT),formatDate($r['fecha']),$r['area'],
                     isset($r['responsable']) ? $r['responsable'] : '',
                     $r['codigo'],$r['detalle'],
                     number_format((float)$r['cantidad'],2,'.',''),$r['unidad_medida']));
    }
}

exit;
