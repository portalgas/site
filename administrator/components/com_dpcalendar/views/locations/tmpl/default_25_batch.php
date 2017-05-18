<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$published = $this->state->get('filter.published');
?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_DPCALENDAR_BATCH_OPTIONS');?></legend>
	<p><?php echo JText::_('COM_DPCALENDAR_BATCH_TIP'); ?></p>
	<?php echo JHtml::_('batch.language');?>
	<button type="submit" onclick="Joomla.submitbutton('event.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button"
		onclick="document.id('batch-category-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
