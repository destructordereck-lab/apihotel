<?php
// cabeceras permisos
header('Content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Kickstart the framework
$f3 = require('lib/base.php');

$f3->set('DEBUG', 3);
if ((float)PCRE_VERSION < 8.0) {
    trigger_error('PCRE version is out of date');
}

// Load configuration
$f3->config('config.ini'); // cargar la configuración con la base de datos
$f3->config('routes.ini'); // cargar las rutas URL

// Configurar la conexion con la base de Datos
// Usar variables de entorno si existen, si no usar config.ini
$dbHost = getenv('DB_HOST') ?: $f3->get('database.host');
$dbUser = getenv('DB_USER') ?: $f3->get('database.user');
$dbPass = getenv('DB_PASS') ?: $f3->get('database.pass');
$dbName = getenv('DB_NAME') ?: $f3->get('database.dbname');

try {
    $f3->set('DB', new DB\SQL(
        "mysql:host={$dbHost};port=3306;dbname={$dbName}",
        $dbUser,
        $dbPass,
        array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        )
    ));
    // Log temporal para confirmar conexión (opcional)
    error_log("DEBUG: DB connected to {$dbHost}/{$dbName} as {$dbUser}");
} catch (Exception $e) {
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// Rutas y handlers
$f3->route('GET /', function($f3) {
    $classes = array(
        'Base' =>
            array(
                'hash',
                'json',
                'session',
                'mbstring'
            ),
        'Cache' =>
            array(
                'apc',
                'apcu',
                'memcache',
                'memcached',
                'redis',
                'wincache',
                'xcache'
            ),
        'DB\SQL' =>
            array(
                'pdo',
                'pdo_dblib',
                'pdo_mssql',
                'pdo_mysql',
                'pdo_odbc',
                'pdo_pgsql',
                'pdo_sqlite',
                'pdo_sqlsrv'
            ),
        'DB\Jig' =>
            array('json'),
        'DB\Mongo' =>
            array(
                'json',
                'mongo'
            ),
        'Auth' =>
            array('ldap','pdo'),
        'Bcrypt' =>
            array(
                'openssl'
            ),
        'Image' =>
            array('gd'),
        'Lexicon' =>
            array('iconv'),
        'SMTP' =>
            array('openssl'),
        'Web' =>
            array('curl','openssl','simplexml'),
        'Web\Geo' =>
            array('geoip','json'),
        'Web\OpenID' =>
            array('json','simplexml'),
        'Web\OAuth2' =>
            array('json'),
        'Web\Pingback' =>
            array('dom','xmlrpc'),
        'CLI\WS' =>
            array('pcntl')
    );
    $f3->set('classes', $classes);
    $f3->set('content', 'welcome.htm');
    echo View::instance()->render('layout.htm');
});

// Rutas adicionales
$f3->route('GET /userref', function($f3) {
    $f3->set('content', 'userref.htm');
    echo View::instance()->render('layout.htm');
});

// API routes (asegúrate que controladores existen en controladores/)
$f3->route('POST /signup', 'Auth_Ctrl->registro');
$f3->route('POST /login', 'Auth_Ctrl->login');

// Endpoint de diagnóstico temporal (eliminar después de depurar)
$f3->route('GET /_debug_db', function($f3) {
    $db = $f3->get('DB');
    if (!$db) {
        echo json_encode(['db' => 'null']);
        return;
    }
    try {
        $res = $db->exec('SELECT 1 as ok');
        echo json_encode(['db' => 'ok', 'test' => $res]);
    } catch (Exception $e) {
        echo json_encode(['db' => 'error', 'msg' => $e->getMessage()]);
    }
});

// Ejecutar framework
$f3->run();
