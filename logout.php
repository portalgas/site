<?php 
/*
 * fatto il logout di joomla
 * redirect su logout di neo
 * redirect su login di joomla
 */
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('JPATH_BASE', dirname(__FILE__));
require_once JPATH_BASE.'/includes/defines.php';
require_once JPATH_BASE.'/includes/framework.php';

$app = JFactory::getApplication('site');
$neo_portalgas_url  = $app->getCfg('NeoPortalgasUrl');
// echo 'neo_portalgas_url '.$neo_portalgas_url;

$url = $neo_portalgas_url.'users/logout';

ob_start();
header('Location: '.$url);
ob_end_flush();
die();