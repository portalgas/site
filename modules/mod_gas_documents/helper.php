<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modGasDocumentsHelper
{
	static function getDocuments($db_documents=[])
	{
		$documents = [];
		if(empty($db_documents))
			return $documents;

		$path_base = '';
		$url_base = '';
		switch (JPATH_BASE) {
            case '/var/www/portalgas':
				$path_base = '/var/www/neo.portalgas/webroot';
				$url_base = 'https://neo.portalgas.it';
				break;
			case '/var/www/test.portalgas':
				$path_base = '/var/www/neotest.portalgas/webroot';
				$url_base = 'https://neotest.portalgas.it';
				break;
			case '/var/www/next.portalgas':
				$path_base = '/var/www/neonext.portalgas/webroot';
				$url_base = 'https://neonext.portalgas.it';
				break;
            case '/var/www/my/portalgas':
                $path_base = '/var/www/my/neo.portalgas/webroot';
                $url_base = 'http://neo.portalgas.it';
                break;
			default:
				# code...
				break;
		}

		if(empty($path_base))
			return $documents;

		$i=0;
		foreach ($db_documents as $db_document) {

			/*
            [id] => 2
            [name] => foto
            [path] => /files/Documents/file_name/2/
            [file_preview_path] => 
            [file_name] => foto-1.jpg
            [descri] => lorem ipsum
            [file_size] => 
            [file_ext] => 
            [file_type] => 
            */

			if(file_exists($path_base.$db_document['path'])) {
				$documents[$i] = $db_document;
				$documents[$i]['path_full'] = $path_base.$db_document['path'];
				$documents[$i]['url_full']  = $url_base.$db_document['path'];
				$i++;				
			}
		} // end foreach ($db_documents as $db_document)

		// echo "<pre>"; print_r($documents); echo "</pre>"; 
	
		return $documents; 
	}

	static function getDataBaseDocuments($organization_id)
	{
		$db = JFactory::getDbo();
		$rows = [];
		
		$sql = "SELECT 
					Document.id, Document.name, Document.path, 
					Document.file_preview_path, Document.file_name, Document.descri, 
					Document.file_size, Document.file_ext, Document.file_type   
				FROM
					documents Document
				WHERE
					Document.is_active = 1 and Document.organization_id = ".(int)$organization_id."
				ORDER BY Document.sort";
		//echo '<br />'.$sql;
		$db->setQuery($sql);
		if ($db->query())
			$rows = $db->loadAssocList();
		//$rows = $db->loadObjectList();
		//$rows = $db->loadResult();
		
		return $rows;
	}	
}