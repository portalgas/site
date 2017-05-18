<?php
/**
 * List View Template: Default
 *
 * @package         Content Templater
 * @version         4.6.3
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2013 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('script', 'system/multiselect.js', false, true);

JHtml::stylesheet('nnframework/style.min.css', false, true);
JHtml::stylesheet('contenttemplater/style.min.css', false, true);

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$ordering = ($listOrder == 'a.ordering');

$editor = JFactory::getEditor();

$user = JFactory::getUser();
$canCreate = $user->authorise('core.create', 'com_contenttemplater');
$canEdit = $user->authorise('core.edit', 'com_contenttemplater');
$canChange = $user->authorise('core.edit.state', 'com_contenttemplater');
$canCheckin = $user->authorise('core.manage', 'com_checkin');
$saveOrder = ($listOrder == 'a.ordering');

$cols = 7;

// Version check
require_once JPATH_PLUGINS . '/system/nnframework/helpers/versions.php';
if ($this->config->show_update_notification) {
	echo NNVersions::getInstance()->getMessage('contenttemplater', '', '', 'component');
}
?>

	<form action="<?php echo JRoute::_('index.php?option=com_contenttemplater&view=list'); ?>" method="post"
		name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<div class="filter-search fltlft">
				<label class="filter-search-lbl"
					for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
				<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button"
					onclick="document.id( 'filter_search' ).value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="filter-select fltrt">
				<select name="filter_state" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
					<?php echo JHtml::_('select.options', ContentTemplaterHelper::publishedOptions(), 'value', 'text', $this->state->get('filter.state'), true); ?>
				</select>
			</div>
		</fieldset>
		<div class="clr"></div>

		<table class="adminlist">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll( this )" />
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_DESCRIPTION', 'a.description', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" nowrap="nowrap">
						<?php echo JText::_('NN_FRONTEND'); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
						<?php if ($canChange && $saveOrder) : ?>
							<?php echo JHtml::_('grid.order', $this->list, 'filesave.png', 'list.saveorder'); ?>
						<?php endif; ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $cols; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->list as $i => $item) :
					$canCheckinItem = ($canCheckin || $item->checked_out == 0 || $item->checked_out == $user->get('id'));
					$canChangeItem = ($canChange && $canCheckinItem);

					if ($item->button_enable_in_frontend) {
						$enable_in_frontend = '<a class="icon-16-allowinactive hasTip" title="' . JText::_('NN_ENABLE_IN_FRONTEND') . '"></a>';
					} else {
						$enable_in_frontend = '<a class="icon-16-denyinactive hasTip" title="' . JText::_('NN_NOT') . ' ' . JText::_('NN_ENABLE_IN_FRONTEND') . '"></a>';
					}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $editor, $item->checked_out_time, 'list.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_contenttemplater&task=item.edit&id=' . $item->id); ?>"
									title="<?php echo $this->escape($item->name); ?>">
									<?php echo $this->escape(str_replace(JURI::root(), '', $item->name)); ?></a>
							<?php else : ?>
								<?php echo $this->escape(str_replace(JURI::root(), '', $item->name)); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php
							$description = explode('---', $item->description);
							$descr = trim($description['0']);
							if (isset($description['1'])) {
								$descr = '<span class="hasTip" title="' . makeTooltipSafe(trim($descr) . '::' . trim($description['1'])) . '">' . $this->escape($descr) . '</span>';
							}
							echo $descr;
							?>
						</td>
						<td align="center">
							<?php echo $enable_in_frontend; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'list.', $canChangeItem); ?>
						</td>
						<td class="order">
							<?php if ($canChangeItem) : ?>
								<?php if ($saveOrder) : ?>
									<?php if ($listDirn == 'asc') : ?>
										<span><?php echo $this->pagination->orderUpIcon($i, 1, 'list.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, 1, 'list.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php elseif ($listDirn == 'desc') : ?>
										<span><?php echo $this->pagination->orderUpIcon($i, 1, 'list.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, 1, 'list.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
								<input type="text" name="order[]" size="5"
									value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text-area-order" />
							<?php else : ?>
								<?php echo $item->ordering; ?>
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo ( int ) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>

<?php
// PRO Check
require_once JPATH_PLUGINS . '/system/nnframework/helpers/licenses.php';
echo NNLicenses::getInstance()->getMessage('CONTENT_TEMPLATER', 0);

// Copyright
echo NNVersions::getInstance()->getCopyright('CONTENT_TEMPLATER', '', 7152, 'contenttemplater', 'component');

function makeTooltipSafe($str)
{
	return str_replace(
		array('"', '::', "&lt;", "\n"),
		array('&quot;', '&#58;&#58;', "&amp;lt;", '<br />'),
		htmlentities(trim($str), ENT_QUOTES, 'UTF-8')
	);
}
