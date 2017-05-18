<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_dpcalendar.category');

$fLevels	= array();
$fLevels[]	= JHtml::_('select.option', '1', JText::_('J1'));
$fLevels[]	= JHtml::_('select.option', '2', JText::_('J2'));
$fLevels[]	= JHtml::_('select.option', '3', JText::_('J3'));
$fLevels[]	= JHtml::_('select.option', '4', JText::_('J4'));
$fLevels[]	= JHtml::_('select.option', '5', JText::_('J5'));
$fLevels[]	= JHtml::_('select.option', '6', JText::_('J6'));
$fLevels[]	= JHtml::_('select.option', '7', JText::_('J7'));
$fLevels[]	= JHtml::_('select.option', '8', JText::_('J8'));
$fLevels[]	= JHtml::_('select.option', '9', JText::_('J9'));
$fLevels[]	= JHtml::_('select.option', '10', JText::_('J10'));

$eventTypes = array();
$eventTypes[] = JHtml::_('select.option', '0', JText::_('COM_DPCALENDAR_VIEW_EVENTS_SELECT_NORMAL_EVENTS'));
$eventTypes[] = JHtml::_('select.option', '1', JText::_('COM_DPCALENDAR_VIEW_EVENTS_SELECT_ORIGIONAL_EVENTS'));
$eventTypes[] = JHtml::_('select.option', '2', JText::_('COM_DPCALENDAR_VIEW_EVENTS_SELECT_BOTH_EVENTS'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=events'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				title="<?php echo JText::_('COM_DPCALENDAR_SEARCH_IN_TITLE'); ?>" />

			<label class="filter-search-lbl" for="filter_search_start"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENTS_START_DATE_AFTER_LABEL'); ?>:</label>
			<?php echo JHtml::_('calendar',
					$this->escape($this->state->get('filter.search_start')),
					'filter_search_start',
					'filter_search_start',
					'%Y-%m-%d',
					array('class' => 'inputbox', 'maxlength' => '10', 'size' => '10'));?>

			<label class="filter-search-lbl" for="filter_search_end"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENTS_END_DATE_BEFORE_LABEL'); ?>:</label>
			<?php echo JHtml::_('calendar',
					$this->escape($this->state->get('filter.search_end')),
					'filter_search_end',
					'filter_search_end',
					'%Y-%m-%d',
					array('class' => 'inputbox', 'maxlength' => '10', 'size' => '10'));?>

			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button"
				onclick="document.id('filter_search').value='';document.id('filter_search_start').value='';document.id('filter_search_end').value='';this.form.submit();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_event_type" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $eventTypes, 'value', 'text', $this->state->get('filter.event_type'), true);?>
			</select>
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENTS_SELECT_CALENDAR');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_dpcalendar'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>

			<select name="filter_level" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_MAX_LEVELS');?></option>
				<?php echo JHtml::_('select.options', $fLevels, 'value', 'text', $this->state->get('filter.level'));?>
			</select>

            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="15%">
					<?php echo JHtml::_('grid.sort',  'JDATE', 'a.start_date', $listDirn, $listOrder); ?>
				</th>
				<th width="3%">
					<?php echo JHtml::_('grid.sort',  'COM_DPCALENDAR_FIELD_COLOR_LABEL', 'a.color', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th width="20%">
					<?php echo JHtml::_('grid.sort',  'COM_DPCALENDAR_CALENDAR', 'category_title', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item)
		{
			$item->cat_link	= JRoute::_('index.php?option=com_categories&extension=com_dpcalendar&task=edit&type=other&cid[]=' . $item->catid);
			$canCreate	= $user->authorise('core.create',		'com_dpcalendar.category.' . $item->catid);
			$canEdit	= $user->authorise('core.edit',			'com_dpcalendar.category.' . $item->catid);
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
			$canChange	= $user->authorise('core.edit.state',	'com_dpcalendar.category.' . $item->catid) && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out)
					{ ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'events.', $canCheckin); ?>
					<?php
					} ?>
					<?php if ($canEdit)
					{ ?>
						<a href="<?php echo JRoute::_('index.php?option=com_dpcalendar&task=event.edit&id=' . (int) $item->id); ?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php
					} else
					{ ?>
							<?php echo $this->escape($item->title); ?>
					<?php
					} ?>
					<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></p>
				</td>
				<td>
					<?php echo DPCalendarHelper::renderEvents(array($item), '{{#events}}{{date}}{{/events}}'); ?>
				</td>
				<td class="center" style="background: none repeat scroll 0 0 #<?php echo $item->color;?>">
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'events.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->category_title); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center">
					<?php echo $item->hits; ?>
				</td>
				<td class="center nowrap">
					<?php if ($item->language == '*')
					{?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php
					} else
					{?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php
					}?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php
			} ?>
		</tbody>
	</table>

	<?php echo $this->loadTemplate('batch'); ?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_DPCALENDAR_FOOTER'), JRequest::getVar('DPCALENDAR_VERSION'));?>
</div>
