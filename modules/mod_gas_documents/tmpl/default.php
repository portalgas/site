<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// echo "<pre>"; print_r($documents); echo "</pre>";  

if (!empty($documents)) {

	echo '<h2>Documenti</h2>';

	echo '<ul class="documents">';
	foreach ($documents as $document) {

		echo '<li>';

		$path_full = $document['url_full'].$document['file_name'];
		echo '<div class="document-title">';
		echo '<a target="_blank" href="'.$path_full.'">'.$document['name'].'</a>';
		echo '</div>';
		if(!empty($document['descri'])) {
			echo '<div class="document-descri">';
			echo $document['descri'];
			echo '</div>';			
		}

		echo '</li>';
	}
	echo '</ul>';
	?>
	<style>
	ul.documents {
	   padding: 0;
	}
	ul.documents li {
	   list-style: none;
	}
	ul.documents li .document-title {
	    
	}
	ul.documents li .document-descri {
	    padding-left: 5px;
	}
	</style>
	<?php
} 
?>