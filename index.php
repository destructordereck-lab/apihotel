<?php
//cabeceras permisos
header('Content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}


//fin cabecera

// Kickstart the framework
$f3=require('lib/base.php');

$f3->set('DEBUG',3);
if ((float)PCRE_VERSION<8.0)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');//cargar la configuración con la base de datos
$f3->config('routes.ini');//cargar las rutas URL

//configurar la conexion con la base de Datos
$f3->set('DB', new DB\SQL(
    'mysql:host=' . $f3->get('database.host') . ';port=3306;dbname=' . $f3->get('database.dbname'),
    $f3->get('database.user'),
    $f3->get('database.pass'),
    array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    )
));


$f3->route('GET /',
	function($f3) {
		$classes=array(
			'Base'=>
				array(
					'hash',
					'json',
					'session',
					'mbstring'
				),
			'Cache'=>
				array(
					'apc',
					'apcu',
					'memcache',
					'memcached',
					'redis',
					'wincache',
					'xcache'
				),
			'DB\SQL'=>
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
			'DB\Jig'=>
				array('json'),
			'DB\Mongo'=>
				array(
					'json',
					'mongo'
				),
			'Auth'=>
				array('ldap','pdo'),
			'Bcrypt'=>
				array(
					'openssl'
				),
			'Image'=>
				array('gd'),
			'Lexicon'=>
				array('iconv'),
			'SMTP'=>
				array('openssl'),
			'Web'=>
				array('curl','openssl','simplexml'),
			'Web\Geo'=>
				array('geoip','json'),
			'Web\OpenID'=>
				array('json','simplexml'),
			'Web\OAuth2'=>
				array('json'),
			'Web\Pingback'=>
				array('dom','xmlrpc'),
			'CLI\WS'=>
				array('pcntl')
		);
		$f3->set('classes',$classes);
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET /userref',
	function($f3) {
		$f3->set('content','userref.htm');
		echo View::instance()->render('layout.htm');
	}
);
$f3->route('POST /signup', 'Auth_Ctrl->registro');
$f3->route('POST /login', 'Auth_Ctrl->login');
$f3->run();
