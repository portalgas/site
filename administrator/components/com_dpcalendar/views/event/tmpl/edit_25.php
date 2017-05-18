<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'chosen' => true, 'maps' => true));
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'system/core.js', false, true);

$document = JFactory::getDocument();
$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/dpcalendar/dpcalendar.js');

$document->addScript(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/edit_25.js');
$document->addStyleDeclaration('.cf:before, .cf:after { content: ""; display: table; }
.cf:after { clear: both; }
.cf { zoom: 1; } #location-form{background-color:#b0c4de;}');

$document->addScriptDeclaration("dpjQuery(document).ready(function(){dpjQuery('#jform_location_ids').chosen({
	no_results_text: '" . JText::_('COM_DPCALENDAR_VIEW_EVENT_NONE_LABEL') . "',
	placeholder_text_multiple: '" . JText::_('COM_DPCALENDAR_VIEW_EVENT_SELECTED_LABEL') . "'
});});");

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'event.cancel' || document.formvalidator.isValid(document.id('event-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('event-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<h3>
<span style="color: red">
<?php if ($this->item->original_id > 0)
{
	echo sprintf(JText::_('COM_DPCALENDAR_VIEW_EVENT_GOTO_ORIGINAL'),
	JRoute::_('index.php?option=com_dpcalendar&task=event.edit&id=' . $this->item->original_id));
}?>
<?php if ($this->item->original_id == -1)
{
	echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ORIGINAL_WARNING');
}?>
</span>
</h3>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="event-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend>
				<?php echo empty($this->item->id) ? JText::_('COM_DPCALENDAR_NEW_EVENT') : JText::sprintf('COM_DPCALENDAR_EDIT_EVENT', $this->item->id); ?>
			</legend>
			<ul class="adminformlist" style="float: left;">
				<li class="cf"><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('access_content'); ?>
				<?php echo $this->form->getInput('access_content'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('location'); ?>
				<?php echo $this->form->getInput('location'); ?></li>

				<?php if ($this->form->getField('location_ids'))
				{?>
				<li class="cf"><?php echo $this->form->getLabel('location_ids'); ?>
					<?php echo $this->form->getInput('location_ids');?>
					<?php if (DPCalendarHelper::getActions()->get('core.create'))
					{?>
						<a href="javascript:void(0);" id="location-activator" class="icon-16-newarticle" style="margin-top:10px;padding-left: 16px;"></a>
						<?php
					}
					if (DPCalendarHelper::getActions()->get('core.delete'))
					{?>
					<a href="javascript:void(0);" id="location-remove" class="icon-16-trash" style="padding-left: 16px"></a>
					<?php
					} ?>
					<div class="clr"></div>
					<?php if (DPCalendarHelper::getActions()->get('core.create'))
					{?>
					<div id="location-form">
						<button type="button" id="location-save-button">
							<?php echo JText::_('JSAVE') ?>
						</button>
						<button type="button" id="location-cancel-button">
							<?php echo JText::_('JCANCEL') ?>
						</button>
						<?php $locationForm = JForm::getInstance('com_dpcalendar.location', 'location', array('control' => 'location'));
						$locationForm->setFieldAttribute('title', 'required', false);?>
						<input type="hidden" id="location_token" value="<?php echo JSession::getFormToken();?>" />
						<?php echo $locationForm->getLabel('title'); ?>
						<?php echo $locationForm->getInput('title'); ?>
						<?php echo $locationForm->getLabel('country'); ?>
						<?php echo $locationForm->getInput('country'); ?>
						<?php echo $locationForm->getLabel('province'); ?>
						<?php echo $locationForm->getInput('province'); ?>
						<?php echo $locationForm->getLabel('city'); ?>
						<?php echo $locationForm->getInput('city'); ?>
						<?php echo $locationForm->getLabel('zip'); ?>
						<?php echo $locationForm->getInput('zip'); ?>
						<?php echo $locationForm->getLabel('street'); ?>
						<?php echo $locationForm->getInput('street'); ?>
						<?php echo $locationForm->getLabel('number'); ?>
						<?php echo $locationForm->getInput('number'); ?>
						<?php echo $locationForm->getLabel('room'); ?>
						<?php echo $locationForm->getInput('room'); ?>
						<div style="clear:both;"></div>
					</div>
					<?php
					}?>
				</li>
				<?php
				}?>

				<li class="cf"><?php echo $this->form->getLabel('color'); ?>
				<?php echo $this->form->getInput('color'); ?></li>

				<li class="cf"><?php echo $this->form->getLabel('url'); ?>
				<?php echo $this->form->getInput('url'); ?></li>
			</ul>

			<div>
				<?php echo $this->form->getLabel('description'); ?>
				<div class="clr"></div>
				<?php echo $this->form->getInput('description'); ?>
			</div>
			<div id="event-location-frame" style="margin-top:30px;width:100%;height:200px"></div>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start', 'event-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

		<?php echo $this->loadTemplate('date'); ?>

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

				<?php if ($this->item->hits)
				{ ?>
					<li><?php echo $this->form->getLabel('hits'); ?>
					<?php echo $this->form->getInput('hits'); ?></li>
				<?php
				} ?>
			</ul>
		</fieldset>

		<?php echo $this->loadTemplate('attend'); ?>
		<?php echo $this->loadTemplate('params'); ?>
		<?php echo $this->loadTemplate('metadata'); ?>

		<?php echo JHtml::_('sliders.end'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>

<div style="text-align: center; clear: both">
	<?php echo sprintf(JText::_('COM_DPCALENDAR_FOOTER'), JRequest::getVar('DPCALENDAR_VERSION'));?>
</div>
