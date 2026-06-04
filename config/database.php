<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sis_logistico');
define('BASE_URL', '/sis_logistico');
define('SITE_NAME', 'SIS. LOGÍSTICO DE ALMACÉN');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;color:red"><b>Error de conexión a la base de datos:</b><br>' . htmlspecialchars($e->getMessage()) . '<br><br>Asegúrate de que MySQL está activo y la base de datos <b>sis_logistico</b> existe.</div>');
        }
    }
    return $pdo;
}
