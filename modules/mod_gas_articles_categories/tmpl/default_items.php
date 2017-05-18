<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * se organization_id > 0 ho le categorie dell'organization dove sono associati SuppliersOrganizations
 */
if(!empty($listOrganization)) {
	$categoryIdOrganization = array();
	foreach($listOrganization as $key => $value) 
		$categoryIdOrganization[] = $value->id;
	
	$listFiltrata = array();	if(!empty($list)) {
		
		/*
		 * ciclo per fare il match tra gli id delle categorie dell'organization e quelle di joomla
		 * se c'e' il match li inserisco tra le categorie filtrate
		 */		foreach($list as $i => $item) { 			if(in_array($item->id, $categoryIdOrganization))				$listFiltrata[] = $item;		}
		
		$list = $listFiltrata;	}
}

foreach ($list as $item) :
	 
	$link = JRoute::_(ContentHelperRoute::getCategoryRoute($item->id));
	// echo '<br />'.$item->id.' '.$link;
?>
	<li> <?php $levelup=$item->level-$startLevel -1; ?>
  <h<?php echo $params->get('item_heading')+ $levelup; ?>>
		<a href="<?php echo $link; ?>">
		<?php echo $item->title;?></a>
   </h<?php echo $params->get('item_heading')+ $levelup; ?>>

		<?php
		if($params->get('show_description', 0))
		{
			echo JHtml::_('content.prepare', $item->description, $item->getParams(), 'mod_gas_articles_categories.content');
		}
		if($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0) || ($params->get('maxlevel') >= ($item->level - $startLevel))) && count($item->getChildren()))
		{

			echo '<ul>';
			$temp = $list;
			$list = $item->getChildren();
			require JModuleHelper::getLayoutPath('mod_gas_articles_categories', $params->get('layout', 'default').'_items');
			$list = $temp;
			echo '</ul>';
		}
		?>
 </li>
<?php endforeach; ?>