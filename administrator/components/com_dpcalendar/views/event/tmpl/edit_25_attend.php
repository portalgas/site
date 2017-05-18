<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();
echo JHtml::_('sliders.panel', JText::_('COM_DPCALENDAR_VIEW_EVENT_ATTEND_OPTIONS'), 'attending-fields');
?>
<fieldset class="panelform">
	<ul class="adminformlist">
		<li><?php echo $this->form->getLabel('capacity'); ?>
			<?php echo $this->form->getInput('capacity'); ?>
			<?php echo $this->freeInformationText;?>
		</li>
		<li><?php echo $this->form->getLabel('capacity_used'); ?>
			<?php echo $this->form->getInput('capacity_used'); ?>
			<?php echo $this->freeInformationText;?>
		</li>
		<li>
			<?php echo $this->form->getLabel('price'); ?>
			<?php echo $this->form->getInput('price'); ?>
			<?php echo $this->freeInformationText;?>
		</li>
		<li>
			<?php echo $this->form->getLabel('plugintype'); ?>
			<?php echo $this->form->getInput('plugintype'); ?>
			<?php echo $this->freeInformationText;?>
		</li>
		<li>
			<?php echo $this->form->getLabel('ordertext'); ?>
			<?php echo $this->form->getInput('ordertext'); ?>
			<?php echo $this->freeInformationText;?>
		</li>
		<li>
			<?php echo $this->form->getLabel('canceltext'); ?>
			<?php echo $this->form->getInput('canceltext'); ?>
			<?php echo $this->freeInformationText;?>
		</li>
		<li>
			<?php echo $this->form->getLabel('payment_statement', 'params'); ?>
			<?php echo $this->form->getInput('payment_statement', 'params'); ?>
			<?php echo $this->freeInformationText;?>
		</li>

		<?php if (!DPCalendarHelper::isFree())
		{?>
		<li><label id="jform_attend-lbl" for="jform_attend" title=""><?php echo JText::_('COM_DPCALENDAR_ATTEND') ?></label>
			<input type="checkbox" id="attend-state-checkbox" name="attend"
			       value="1" <?php echo $this->state->get('attend_id', 0) > 0 ? 'checked="checked"' : ''; ?>/></li>

		<li><?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?></li>

		<li><?php echo $this->form->getLabel('email'); ?>
			<?php echo $this->form->getInput('email'); ?></li>

		<li><?php echo $this->form->getLabel('telephone'); ?>
			<?php echo $this->form->getInput('telephone'); ?></li>
		<li><?php echo $this->form->getLabel('remind_time'); ?>
			<?php echo $this->form->getInput('remind_time'); ?>
			<?php echo $this->form->getInput('remind_type'); ?></li>
		<?php
		}?>
	</ul>
	<?php
	echo $this->form->getInput('user_id');
	echo $this->form->getInput('attend_date');?>
</fieldset>
