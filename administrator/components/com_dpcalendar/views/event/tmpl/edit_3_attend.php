<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('capacity'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('capacity'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('capacity_used'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('capacity_used'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>

<?php if (!DPCalendarHelper::isFree() && is_numeric($this->item->id))
{?>
<hr/>
<div class="control-group">
	<div class="control-label">
		<?php echo JText::_('COM_DPCALENDAR_ATTEND')?>
	</div>
	<div class="controls">
       <input type="checkbox" id="attend-state-checkbox" name="attend"
       		value="1" <?php echo $this->state->get('attend_id', 0) > 0 ? 'checked="checked"' : '';?>/>
	</div>
</div>
<div class="control-group attend-control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('name'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('name'); ?>
	</div>
</div>
<div class="control-group attend-control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('email'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('email'); ?>
	</div>
</div>
<div class="control-group attend-control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('telephone'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('telephone'); ?>
	</div>
</div>
<div class="control-group attend-control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('remind_time'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('remind_time'); ?>
		<?php echo $this->form->getInput('remind_type'); ?>
	</div>
</div>
<?php
echo $this->form->getInput('user_id');
echo $this->form->getInput('attend_date');

}