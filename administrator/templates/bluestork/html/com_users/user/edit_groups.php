<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

/*
 * fractis, abilito la gestione dei gruppi a root ($group_id_root = 8)
 * per gli altri solo Registred e GasPagesSeoGas ($user->organization['Organization']['j_group_registred'])
 */
$group_id_root = 8;
$user = JFactory::getUser();
if(in_array($group_id_root, $user->getAuthorisedGroups())) 
	echo JHtml::_('access.usergroups', 'jform[groups]', $this->groups, true); 
else  {
		
	/*
	 *  a Registred aggiungo GasPagesSeoGas
	 */
	$this->groups[] = $user->organization['Organization']['j_group_registred'];
	
	/*
	 * code libraries/joomla/html/html/access.php
	 */		
	$name = 'jform[groups]';
	$selected = $this->groups;
	$checkSuperAdmin = true;
	
	$count = 0;
	
	$count++;
		
	$isSuperAdmin = JFactory::getUser()->authorise('core.admin');
			
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
			
	$query->select('a.*, COUNT(DISTINCT b.id) AS level');
	$query->from($db->quoteName('#__usergroups') . ' AS a');
	$query->join('LEFT', $db->quoteName('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
	
	/*
	 *  custom
	 */
	$group_ids = '';
	foreach($selected as $selec)
		$group_ids .= $selec.',';
	
	$group_ids = substr($group_ids, 0, (strlen($group_ids)-1))		;
	
	$query->where('a.id IN ('.$group_ids.')');
	
	$query->group('a.id, a.title, a.lft, a.rgt, a.parent_id');
	$query->order('a.lft ASC');
	$db->setQuery($query);
	$groups = $db->loadObjectList();

	// Check for a database error.
	if ($db->getErrorNum())
	{
		JError::raiseNotice(500, $db->getErrorMsg());
		return null;
	}
	
	$html = array();
	
	$html[] = '<ul class="checklist usergroups">';

	for ($i = 0, $n = count($groups); $i < $n; $i++)
	{
	$item = &$groups[$i];
		
		// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
		if ((!$checkSuperAdmin) || $isSuperAdmin || (!JAccess::checkGroup($item->id, 'core.admin')))
					{
				// Setup  the variable attributes.
			$eid = $count . 'group_' . $item->id;
			// Don't call in_array unless something is selected
			$checked = '';
			if ($selected)
			{
				$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
			}
						$rel = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';
		
						// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"';
			
			/*
			 *  custom
			 */
			//$html[] = ' disabled="true" ';
			
			$html[] = '				' . $checked . $rel . ' />';
			$html[] = '		<label for="' . $eid . '">';
			$html[] = '		' . str_repeat('<span class="gi">|&mdash;</span>', $item->level) . $item->title;
			$html[] = '		</label>';
			$html[] = '	</li>';
		}
	}
	$html[] = '</ul>';

	echo implode("\n", $html);
}
?>