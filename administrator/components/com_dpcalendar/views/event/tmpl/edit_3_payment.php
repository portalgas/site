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
		<?php echo $this->form->getLabel('price'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('price'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('plugintype'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('plugintype'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('ordertext'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('ordertext'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('canceltext'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('canceltext'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('payment_statement', 'params'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('payment_statement', 'params'); ?>
		<?php echo $this->freeInformationText;?>
	</div>
</div>
