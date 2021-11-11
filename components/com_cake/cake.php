<?php
/*
 * FRONT-END arrivano le chiamate
*  /consegne-gasnome   SEO
*  /?option=com_cake&controller=Deliveries&action=tabs
*  /components/com_cake/cake.php dove require_once 'cake/components/com_cake/app/webroot/index.php';
*  => Configure::read('urlFrontEndToRewriteCakeRequest')
*  
* il BACKOFFICE arrivano le chiamate
*  /administrator/index.php?option=com_cake&controller=Users&action=index
* 	/administrator/components/com_cake/cake.php dove require_once 'cake/components/com_cake/app/webroot/index.php';
*
* in app/Lib/Routing/Dispatcher.php
* se FRONT-END
*		if($_SERVER['REQUEST_URI'] == '/gas-tabs') {
*			$request->query['option'] = 'com_cake';
*			$request->query['controller'] = 'deliveries';
*			$request->query['action'] = 'tabs';
*		}
*
* se BACKOFFICE && $this->isUrlToFrontEndValide($request)
*
* */

defined( '_JEXEC' ) or die( 'Restricted access' );

/*
$controller = JControllerLegacy::getInstance('Cake');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

con index.php?option=com_cake&view=deliveries&layout=tabs
richiamerebbe views/deliveries/view.html.php
			  views/deliveries/tmpl/tabs.php
*/

/*
$document = JFactory::getDocument();
$document->setTitle("com_cake");
$joomla_path = dirname(dirname(dirname(__FILE__)));

require_once($joomla_path.'/configuration.php');

// Constants to be used later in com_cake
$config = new JConfig();
*/

/*
define('JOOMLA_PATH',JURI::base());  // http://localhost/
define('SITE_ROOT',JURI::root());
define('DB_SERVER',$config->host);
define('DB_USER',$config->user);
define('DB_PASSWORD',$config->password);
define('DB_NAME',$config->db);
*/

require_once 'app/webroot/index.php';