<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$published = $this->state->get('filter.published');

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/libraries/jscolor/jscolor.js');
$document->addStyleDeclaration('
label#batch-color-lbl {
    clear: left;
    margin-right: 10px;
    margin-top: 15px;
}
input#batch-color-id {
    margin-top: 15px;
}');
?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_DPCALENDAR_BATCH_OPTIONS');?></legend>
	<p><?php echo JText::_('COM_DPCALENDAR_BATCH_TIP'); ?></p>
	<?php echo JHtml::_('batch.access');?>
	<?php echo JHtml::_('batch.language');?>

	<?php if ($published >= 0)
	{ ?>
		<label title="<?php echo JText::_('COM_DPCALENDAR_BATCH_COLOR_DESC')?>"
			class="hasTip" for="batch-color" id="batch-color-lbl"><?php echo JText::_('COM_DPCALENDAR_BATCH_COLOR_LABEL')?></label>
		<input id="batch-color-id" class="color {required:false} inputbox" name="batch[color_id]" maxlength="6"/>

		<?php
		// Because of the calendar css problem we have to create the batch fieldset manually
		$options = array(JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
		JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE')));

		// Create the batch selector to change select the category by which to move or copy.
		$lines = array('<label id="batch-choose-action-lbl" for="batch-choose-action">', JText::_('JLIB_HTML_BATCH_MENU_LABEL'), '</label>',
					'<fieldset id="batch-choose-action" class="">', '<select name="batch[category_id]" class="inputbox" id="batch-category-id">',
					'<option value="">' . JText::_('JSELECT') . '</option>',
		JHtml::_('select.options', JHtml::_('category.options', 'com_dpcalendar')), '</select>',
		JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'), '</fieldset>');
		echo implode("\n", $lines);
		?>

	<?php
	} ?>
	<p style="clear:both;"></p>
	<button type="submit" onclick="Joomla.submitbutton('event.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button"
		onclick="document.id('batch-category-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
