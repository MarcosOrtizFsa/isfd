<?php
/** Configuracion de base de datos. En produccion usar variables de entorno. */
define('HOST', getenv('ISFD_DB_HOST') ?: 'localhost');
define('USU', getenv('ISFD_DB_USER') ?: 'root');
define('CLA', getenv('ISFD_DB_PASSWORD') ?: '');
define('BD', getenv('ISFD_DB_NAME') ?: 'isfd_formosa');

$id_system_06 = 1;
