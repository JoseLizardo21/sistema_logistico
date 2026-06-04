-- ============================================================
-- SISTEMA LOGÃSTICO DE ALMACÃ‰N
-- Base de Datos: sis_logistico
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `sis_logistico`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sis_logistico`;

-- ----------------------------------------------------------
-- TABLA: usuarios
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rol` enum('administrador','gerente','asistente') NOT NULL DEFAULT 'asistente',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: materiales
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `materiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `detalle` varchar(500) NOT NULL,
  `unidad_medida` varchar(50) NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: areas_usuarias
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `areas_usuarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: responsables
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `responsables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombres_apellidos` varchar(200) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `area_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `area_id` (`area_id`),
  CONSTRAINT `fk_resp_area` FOREIGN KEY (`area_id`) REFERENCES `areas_usuarias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: clasificadores
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clasificadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_clasificador` varchar(30) NOT NULL,
  `detalle` varchar(500) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_clasificador` (`num_clasificador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: ordenes_compra
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ordenes_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nro_orden_compra` varchar(50) NOT NULL,
  `area_id` int(11) NOT NULL,
  `clasificador_id` int(11) DEFAULT NULL,
  `nea` varchar(50) DEFAULT NULL,
  `pecosa` varchar(50) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'aprobado',
  `observacion` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nro_orden_compra` (`nro_orden_compra`),
  KEY `area_id` (`area_id`),
  KEY `clasificador_id` (`clasificador_id`),
  CONSTRAINT `fk_oc_area` FOREIGN KEY (`area_id`) REFERENCES `areas_usuarias` (`id`),
  CONSTRAINT `fk_oc_clasif` FOREIGN KEY (`clasificador_id`) REFERENCES `clasificadores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: ordenes_compra_detalle
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ordenes_compra_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_compra_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`id`),
  KEY `orden_compra_id` (`orden_compra_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `fk_ocd_oc` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`),
  CONSTRAINT `fk_ocd_mat` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: papeletas_salida
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `papeletas_salida` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nro_papeleta` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `area_id` int(11) NOT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('activo','anulado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nro_papeleta` (`nro_papeleta`),
  KEY `area_id` (`area_id`),
  KEY `responsable_id` (`responsable_id`),
  CONSTRAINT `fk_ps_area` FOREIGN KEY (`area_id`) REFERENCES `areas_usuarias` (`id`),
  CONSTRAINT `fk_ps_resp` FOREIGN KEY (`responsable_id`) REFERENCES `responsables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: papeletas_salida_detalle
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `papeletas_salida_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `papeleta_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `papeleta_id` (`papeleta_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `fk_psd_ps` FOREIGN KEY (`papeleta_id`) REFERENCES `papeletas_salida` (`id`),
  CONSTRAINT `fk_psd_mat` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- TABLA: kardex
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kardex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `referencia_numero` varchar(100) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `saldo_cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha` date NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `material_id` (`material_id`),
  KEY `area_id` (`area_id`),
  CONSTRAINT `fk_kd_mat` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`),
  CONSTRAINT `fk_kd_area` FOREIGN KEY (`area_id`) REFERENCES `areas_usuarias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- DATOS INICIALES
-- Passwords: admin/admin123, gerente/admin123, asistente/admin123
-- Hash generado con password_hash('admin123', PASSWORD_BCRYPT)
-- ==========================================================

INSERT INTO `usuarios` (`username`, `password`, `nombre_completo`, `email`, `rol`) VALUES
('admin',     '$2y$10$2.ZOKBc4DJrUFri.CKa23.QrtQPc6gKxZHhs2dDbopkb4bgMFVSqu', 'Administrador del Sistema', 'admin@sistema.com',     'administrador'),
('gerente',   '$2y$10$2.ZOKBc4DJrUFri.CKa23.QrtQPc6gKxZHhs2dDbopkb4bgMFVSqu', 'Gerente General',           'gerente@sistema.com',   'gerente'),
('asistente', '$2y$10$2.ZOKBc4DJrUFri.CKa23.QrtQPc6gKxZHhs2dDbopkb4bgMFVSqu', 'Asistente de AlmacÃ©n',      'asistente@sistema.com', 'asistente');

INSERT INTO `areas_usuarias` (`codigo`, `nombre`) VALUES
('0001', 'SUB GERENCIA DE ABASTECIMIENTOS'),
('0002', 'GERENCIA DE ASESORIA LEGAL'),
('0003', 'SUB GERENCIA DE CATASTRO'),
('0004', 'SUB GERENCIA DE CONTABILIDAD'),
('0005', 'GERENCIA DE DESARROLLO ECONOMICO'),
('0006', 'GERENCIA DE DESARROLLO SOCIAL'),
('0007', 'SUB GERENCIA ESTUDIOS Y PROYECTOS'),
('0008', 'SUB GERENCIA FISCALIZACION'),
('0009', 'GERENCIA MUNICIPAL'),
('0010', 'SUB GERENCIA IMAGEN'),
('0011', 'SUB GERENCIA DE INFORMATICA'),
('0012', 'GERENCIA DE INFRAESTRUCTURA Y DESARROLLO URBANO Y RURAL'),
('0013', 'GERENCIA DE PRESUPUESTO'),
('0014', 'SUB GERENCIA PROCURADURIA'),
('0015', 'SUB GERENCIA DE RECURSOS HUMANOS');

INSERT INTO `responsables` (`codigo`, `nombres_apellidos`, `dni`, `area_id`) VALUES
('R001', 'MAG. FRANCO',        '00000001', 1),
('R002', 'ABOG. MARIELA',      '00000002', 2),
('R003', 'ING. MARCO MEDINA',  '00000003', 3),
('R004', 'CPC. MARTINEZ',      '00000004', 4),
('R005', 'ECO. YAMUNAQUE',     '00000005', 5),
('R006', 'MAG. ANTON',         '00000006', 6),
('R007', 'ING. GALARZA',       '00000007', 7),
('R008', 'ING. CESAR',         '00000008', 8),
('R009', 'ABOG. YARLEQUE',     '00000009', 9),
('R010', 'JAVIER',             '00000010', 10),
('R011', 'ING. CELIA',         '00000011', 11),
('R012', 'ING. MEDINA MM',     '00000012', 12),
('R013', 'CPC. FLOR',          '00000013', 13),
('R014', 'LUIS A. ROBLES S',   '00000014', 14),
('R015', 'DOMINGO',            '00000015', 15);

INSERT INTO `materiales` (`codigo`, `detalle`, `unidad_medida`) VALUES
('MAT-001', 'PAPEL BOND A4',                                                                         'RESMA'),
('MAT-002', 'ARCHIVADOR OFICIO LOMO ANCHO OFICIO',                                                   'UND'),
('MAT-003', 'ARCHIVADOR MEDIO OFICIO LOMO ANCHO',                                                    'UND'),
('MAT-004', 'BOLIGRAFO AZUL LAPIC TRIPLUS L35F PUNTA FINA X 50UND',                                  'CAJA'),
('MAT-005', 'BOLIGRAFO NEGRO LAPIC TRIPLUS L35F PUNTA FINA X50UND',                                  'CAJA'),
('MAT-006', 'BOLIGRAFO ROJO LAPIC TRIPLUS L35F PUNTA FINA X 50UND',                                  'CAJA'),
('MAT-007', 'BORRADOR GRANDE X 20 UND',                                                               'CAJA'),
('MAT-008', 'CARTULINAS PLIEGOS BLANCO',                                                              'UND'),
('MAT-009', 'CARTULINAS PLIEGOS COLOR',                                                               'UND'),
('MAT-010', 'CINTA ADHESIVA TRANSPARENTE (1/2"X72YDS) X 12 UND',                                     'CAJA'),
('MAT-011', 'CINTA DE EMBALAJE 2 in X 80 yd COLOR TRANSPARENTES',                                    'UND'),
('MAT-012', 'CINTA MASKETING GRUESA X12 UND',                                                         'CAJA'),
('MAT-013', 'CHINCHINES X50 UND',                                                                     'CAJA'),
('MAT-014', 'CLIP MARIPOSA DE METAL NÂº 1 X 100 UND',                                                 'CAJA'),
('MAT-015', 'CLIP MARIPOSA DE METAL NÂº 3 X 100 UND',                                                 'CAJA'),
('MAT-016', 'CORRECTOR LIQUIDO TIPO LAPICERO CAJA X 12 UND',                                         'CAJA'),
('MAT-017', 'CUADERNOS CARPETA CUADRICULADO X 100 HOJAS CAJ X 100 UND',                              'CAJA'),
('MAT-018', 'CUTTER CUCHILLA ARTESCO 18 MM ACERO INOXIDABLE',                                        'UND'),
('MAT-019', 'ENGRAPADOR TIPO ALICATE 50 HOJAS',                                                       'UND'),
('MAT-020', 'FASTENNER PARA PAPEL (TIPO FASTENER) DE METAL x50UND',                                  'CAJA'),
('MAT-021', 'FOLDER MANILA TAMAÃ‘O A4',                                                                'CIENTO'),
('MAT-022', 'FORRO VINIFAN T/ OFICIO',                                                                'UND'),
('MAT-023', 'GOMA LIQUIDA X 250 ml',                                                                  'UND'),
('MAT-024', 'GRAPA 26/6 CAJA X 5000UND',                                                              'CAJA'),
('MAT-025', 'HOJAS ARCOLOR X 500 HOJAS',                                                              'PAQUETE'),
('MAT-026', 'LAPIZ NEGRO NÂº 3 CON BORRADOR X12 UND',                                                 'CAJA'),
('MAT-027', 'LIBRO DE ACTAS 400 FOLIOS',                                                              'UND'),
('MAT-028', 'LIGAS DELGADAS NÂ° 18 (CAJA)',                                                            'CAJA'),
('MAT-029', 'MICAS A4 X 10 UND',                                                                      'PAQUETE'),
('MAT-030', 'NOTA AUTOADHESIVA POSTIT 5COLOR (7.5 cm X 7.5 cm) X 500 HOJA',                          'PAQUETE'),
('MAT-031', 'PAPEL LUSTRE PLIEGO, ROJO, AZUL, VERDE, AMARILLO, CELESTE, ANARANJADO, VERDE CLARO, LILA', 'UND'),
('MAT-032', 'PAPELOTES SABANA BLANCO',                                                                'UND'),
('MAT-033', 'PAPELOTES SABANA CUADRICULADO',                                                          'UND'),
('MAT-034', 'PAPELOTES SABANA RAYADO',                                                                'UND');

INSERT INTO `clasificadores` (`num_clasificador`, `detalle`) VALUES
('2.3.1.5.1.2',  'PAPELERIA EN GENERAL, UTILES Y MATERIALES DE OFICINA'),
('2.3.1.5.1.1',  'REPUESTOS Y ACCESORIOS'),
('2.3.1.99.1.2', 'OTROS BIENES'),
('2.3.1.5.1.3',  'ASEO, LIMPIEZA Y TOCADOR'),
('2.3.1.5.3.1',  'COMBUSTIBLES Y CARBURANTES');
