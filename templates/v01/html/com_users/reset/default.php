<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<h2>Resetta password</h2>

<div class="reset<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<row class="container">
	<div class="col-xs-2"></div>	
	<div class="col-xs-8">		
	<form class="form-horizontal" id="user-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="form-validate">

		<?php foreach ($this->form->getFieldsets() as $fieldset): ?>

		<div class="alert alert-danger" role="alert">
			<?php echo JText::_($fieldset->label); ?>
		</div>
		
		<fieldset>
			<div class="form-group">
			<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field): ?>
	            <label class="control-label col-xs-3"><?php echo $field->label; ?></label>
            	<div class="col-xs-9">			
					<?php echo $field->input; ?>
				</div>
			<?php endforeach; ?>
			</div>
		</fieldset>
		<?php endforeach; ?>

		<div class="content-btn">
			<button type="submit" class="validate btn btn-success pull-right"><?php echo JText::_('JSUBMIT'); ?></button>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
	</div>
	<div class="col-xs-2"></div>
	</row>
	
</div>
