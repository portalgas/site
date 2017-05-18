<?php
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 *
 */
if (!defined('ROOT')) {
	define('ROOT', dirname(dirname(dirname(__FILE__))));
}
	
/**
 * The actual directory name for the "app".
 *
 */
if (!defined('APP_DIR')) {
	define('APP_DIR', basename(dirname(dirname(__FILE__))));
}

	
/**
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 */
define('CAKE_CORE_INCLUDE_PATH',  DS .'var' . DS .'cakephp');

if (!defined('WEBROOT_DIR')) {  // Cron
	define('WEBROOT_DIR', basename(dirname(__FILE__)));
}
if (!defined('WWW_ROOT')) {  // /var/www/portalgas/components/com_cake/app/Cron/
	define('WWW_ROOT', dirname(__FILE__) . DS);
}

if (!defined('APP')) {
	define('APP', substr(WWW_ROOT,0,(strlen(WWW_ROOT) - strlen(WEBROOT_DIR. DS))));  // /var/www/portalgas/components/com_cake/app/
}
	
include(CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'bootstrap.php');

//App::uses('UtilsCrons', 'Lib');
require (APP . 'Lib/UtilsCrons.php');

/*
 * $argc numero argomenti dal terminale php -f /...
 * 
 * $argv
 *   [0] => /var/www/portalgas/components/com_cake/app/Cron/index.php
 *   [1] => ordersStatoElaborazione
 *   [2] => organization_id
 */
//if(!isset($argv)) die("Not permission!");

if(isset($argv[1])) {
	$methodName = $argv[1];
	if(isset($argv[2]))
		$organization_id = $argv[2];
	else 
		$organization_id = 0;

	echo "-----------------------------------------------------------------------\n";
	echo "Chiamato metodo $methodName con organization_id = $organization_id \n\n";
	
	$utilsCrons = new UtilsCrons(new View(null));
	$utilsCrons->{$methodName}($organization_id);
}
else
	die("Not permission, Method not call!");
?>