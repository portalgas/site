<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'chosen' => true, 'bootstrap' => true));
JFactory::getDocument()->addScript(JURI::base() . 'components/com_dpcalendar/libraries/iframe-resizer/iframeResizer.contentWindow.min.js');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$input = JFactory::getApplication()->input;

if ($input->getCmd('tmpl') == 'component')
{
	$bar = JToolbar::getInstance('toolbar');
	echo $bar->render();
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'extcalendar.cancel' || document.formvalidator.isValid(document.id('extcalendar-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('extcalendar-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="extcalendar-form" class="form-validate dp-container"><div class="form-horizontal">
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('color'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('color'); ?></div>
	</div>

	<?php echo $this->loadTemplate('params'); ?>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('access_content'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('access_content'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('state'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('state'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('language'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('language'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('rules'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('rules'); ?></div>
	</div>

	<?php echo $this->form->getInput('asset_id'); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="dpplugin" value="<?php echo $input->get('dpplugin')?>" />
	<input type="hidden" name="tmpl" value="<?php echo $input->get('tmpl')?>" />
	<?php echo JHtml::_('form.token'); ?>
</div></form>
