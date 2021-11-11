<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'chosen' => true, 'maps' => true));

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/location/tmpl/edit.js');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'location.cancel' || document.formvalidator.isValid(document.id('location-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('location-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="location-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend>
				<?php echo empty($this->item->id) ? JText::_('COM_DPCALENDAR_NEW_EVENT') : JText::sprintf('COM_DPCALENDAR_EDIT_EVENT', $this->item->id); ?>
			</legend>
			<ul class="adminformlist" style="float: left;">
				<li class="cf"><?php echo $this->form->getLabel('geocomplete'); ?>
				<?php echo $this->form->getInput('geocomplete'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('country'); ?>
				<?php echo $this->form->getInput('country'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('province'); ?>
				<?php echo $this->form->getInput('province'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('city'); ?>
				<?php echo $this->form->getInput('city'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('zip'); ?>
				<?php echo $this->form->getInput('zip'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('street'); ?>
				<?php echo $this->form->getInput('street'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('number'); ?>
				<?php echo $this->form->getInput('number'); ?></li>
				<li class="cf"><?php echo $this->form->getLabel('room'); ?>
				<?php echo $this->form->getInput('room'); ?></li>

				<li class="cf">
				<?php echo $this->form->getLabel('latitude'); ?>
				<?php echo $this->form->getInput('latitude'); ?>
				</li>
				<li class="cf">
				<?php echo $this->form->getLabel('longitude'); ?>
				<?php echo $this->form->getInput('longitude'); ?>
				</li>

				<li class="cf"><?php echo $this->form->getLabel('url'); ?>
				<?php echo $this->form->getInput('url'); ?></li>
			</ul>
			<style type="text/css">.map_canvas{width:500px;height:400px;}</style>
							<div class="map_canvas"></div>

			<div>
				<?php echo $this->form->getLabel('description'); ?>
				<div class="clr"></div>
				<?php echo $this->form->getInput('description'); ?>
			</div>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start', 'event-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>

		<fieldset class="panelform">
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?></li>

				<li><?php echo $this->form->getLabel('created_by_alias'); ?>
				<?php echo $this->form->getInput('created_by_alias'); ?></li>

				<li><?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?></li>

				<li><?php echo $this->form->getLabel('publish_up'); ?>
				<?php echo $this->form->getInput('publish_up'); ?></li>

				<li><?php echo $this->form->getLabel('publish_down'); ?>
				<?php echo $this->form->getInput('publish_down'); ?></li>

				<?php if ($this->item->modified_by)
				{ ?>
					<li><?php echo $this->form->getLabel('modified_by'); ?>
					<?php echo $this->form->getInput('modified_by'); ?></li>

					<li><?php echo $this->form->getLabel('modified'); ?>
					<?php echo $this->form->getInput('modified'); ?></li>
				<?php
				} ?>

			</ul>
		</fieldset>

		<?php echo $this->loadTemplate('params'); ?>

		<?php echo JHtml::_('sliders.end'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>

<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_DPCALENDAR_FOOTER'), JRequest::getVar('DPCALENDAR_VERSION'));?>
</div>
