<?php
/*
 * FRONT-END arrivano le chiamate
*  /consegne-nome-gas   SEO
*  /?option=com_cake&controller=deliveries&action=tabs
*  /components/com_cake/cake.php dove require_once 'cake/components/com_cake/app/webroot/index.php';
*  => Configure::read('urlFrontEndToRewriteCakeRequest')
*  
* il BACKOFFICE arrivano le chiamate
*  /administrator/index.php?option=com_cake&controller=users&action=index
* 	/administrator/components/com_cake/cake.php dove require_once 'cake/components/com_cake/app/webroot/index.php';
*
* in app/Lib/Routing/Dispatcher.php
* se FRONT-END
*		if($_SERVER['REQUEST_URI'] == '/consegne-nome-gas') {
*			$request->query['option'] = 'com_cake';
*			$request->query['controller'] = 'deliveries';
*			$request->query['action'] = 'tabs';
*		}
*
* se BACKOFFICE && $this->isUrlToFrontEndValide($request)
*
* */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/components/com_cake/app/webroot/index.php'; 