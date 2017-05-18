<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

?>

<fieldset id="users-profile-core">
	<legend>
		<?php echo JText::_('COM_USERS_PROFILE_CORE_LEGEND'); ?>
	</legend>
	
	<div class="form-group">
	  <label class="control-label col-xs-3"><?php echo JText::_('COM_USERS_PROFILE_NAME_LABEL'); ?></label>
		<div class="col-xs-9">	
			<?php echo $this->data->name; ?>
		</div>
	</div>	
	<div class="form-group">
	  <label class="control-label col-xs-3"><?php echo JText::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></label>
		<div class="col-xs-9">	
			<?php echo $this->data->username; ?>
		</div>
	</div>	
	<div class="form-group">
	  <label class="control-label col-xs-3"><?php echo JText::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?></label>
		<div class="col-xs-9">	
			<?php echo $this->data->registerDate; ?>
		</div>
	</div>	
	<div class="form-group">
	  <label class="control-label col-xs-3"><?php echo JText::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?></label>
		<div class="col-xs-9">	
			<?php if ($this->data->lastvisitDate != '0000-00-00 00:00:00')
					echo JHtml::_('date', $this->data->lastvisitDate);
			else
					echo JText::_('COM_USERS_PROFILE_NEVER_VISITED'); 
			?>
		</div>
	</div>	
	
</fieldset>
