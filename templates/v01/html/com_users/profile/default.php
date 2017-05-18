<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
?>

<h2>Il tuo profilo</h2>

<div class="profile<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

	<div class="container">
	<div class="col-xs-2"></div>	
	<div class="col-xs-8">		
	<form class="form-horizontal"> 
	
<?php echo $this->loadTemplate('core'); ?>

<?php echo $this->loadTemplate('params'); ?>

<?php echo $this->loadTemplate('custom'); ?>

<?php if (JFactory::getUser()->id == $this->data->id) : ?>
	<div class="content-btn">
		<a class="validate btn btn-success pull-right" href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id='.(int) $this->data->id);?>">
			<?php echo JText::_('COM_USERS_Edit_Profile'); ?></a>
	</div>
<?php endif; ?>

	</form>
	</div>
	<div class="col-xs-2"></div>
	</div> <!-- class="container" -->
	
</div>
