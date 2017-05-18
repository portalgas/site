<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$document = JFactory::getDocument();
$document->addStyleDeclaration('#jform_scheduling_monthly_days li{ float: left;width: 20px;}
		#jform_scheduling_monthly_days{max-width:330px}
		#jform_scheduling_monthly_days ul{display: block;}');
$document->addStyleDeclaration('#jform_scheduling_monthly_week li{ float: left;width: 20px;}
		#jform_scheduling_monthly_week{max-width:330px}
		#jform_scheduling_monthly_week ul{display: block;}');
$document->addStyleDeclaration('ul.adminformlist li {height:30px}');
$document->addStyleDeclaration('ul.adminformlist .text-warning {color: #c09853;}');

echo JHtml::_('sliders.panel', JText::_('COM_DPCALENDAR_FIELDS_DATE_OPTIONS'), 'date-fields');
?>
<fieldset class="panelform">
	<ul class="adminformlist">
		<li><?php echo $this->form->getLabel('start_date'); ?>
		<?php echo $this->form->getInput('start_date'); ?></li>
		<li><?php echo $this->form->getLabel('end_date'); ?>
		<?php echo $this->form->getInput('end_date'); ?></li>

		<li><?php echo $this->form->getLabel('all_day'); ?>
		<?php echo $this->form->getInput('all_day'); ?></li>

		<?php if ($this->item->original_id < 1)
		{?>
		<li id="scheduling-options"><?php echo $this->form->getLabel('scheduling'); ?>
		<?php echo $this->form->getInput('scheduling'); ?>
		<?php echo $this->freeInformationText;?></li>
		<li id="scheduling-options-end"><?php echo $this->form->getLabel('scheduling_end_date'); ?>
		<?php echo $this->form->getInput('scheduling_end_date'); ?></li>
		<li id="scheduling-options-interval"><?php echo $this->form->getLabel('scheduling_interval'); ?>
		<?php echo $this->form->getInput('scheduling_interval'); ?></li>
		<li id="scheduling-options-repeat_count"><?php echo $this->form->getLabel('scheduling_repeat_count'); ?>
		<?php echo $this->form->getInput('scheduling_repeat_count'); ?></li>

		<li id="scheduling-options-day"><?php echo $this->form->getLabel('scheduling_daily_weekdays'); ?>
		<?php echo $this->form->getInput('scheduling_daily_weekdays'); ?></li>
		<li id="scheduling-options-week"><?php echo $this->form->getLabel('scheduling_weekly_days'); ?>
		<?php echo $this->form->getInput('scheduling_weekly_days'); ?></li>

		<li class="scheduling-options-month" id="scheduling-options-month-options"><?php echo $this->form->getLabel('scheduling_monthly_options'); ?>
		<?php echo $this->form->getInput('scheduling_monthly_options'); ?></li>
		<li class="scheduling-options-month" id="scheduling-options-month-days"><?php echo $this->form->getLabel('scheduling_monthly_days'); ?>
		<?php echo $this->form->getInput('scheduling_monthly_days'); ?></li>
		<li class="scheduling-options-month" id="scheduling-options-month-week"><?php echo $this->form->getLabel('scheduling_monthly_week'); ?>
		<?php echo $this->form->getInput('scheduling_monthly_week'); ?></li>
		<li class="scheduling-options-month" id="scheduling-options-month-week-days"><?php echo $this->form->getLabel('scheduling_monthly_week_days'); ?>
		<?php echo $this->form->getInput('scheduling_monthly_week_days'); ?></li>
		<li id="scheduling-expert-button">
		<button type="button" style="float:left;clear:left"><?php echo JText::_('COM_DPCALENDAR_FIELD_SCHEDULING_EXPERT_LABEL');?></button>
		</li>
		<li id="scheduling-rrule">
		<?php echo $this->form->getLabel('rrule'); ?>
		<?php echo $this->form->getInput('rrule'); ?>
		</li>
		<?php
		} ?>
	</ul>
</fieldset>
