<?php
/**
 * Item View Template: Edit
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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');

JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

$user = JFactory::getUser();
$db = JFactory::getDBO();
$query = $db->getQuery(true)
	->select('c.misc')
	->from('#__' . $this->config->contact_table . ' as c')
	->where('c.user_id = ' . (int) $user->id);
$db->setQuery($query);
$contact = $db->loadObject();

JHtml::script('nnframework/script.min.js', false, true);
JHtml::stylesheet('nnframework/style.min.css', false, true);

$view_state = $this->item->view_state;
if ($view_state == -1) {
	$view_state = $this->config->view_state;
}

$script = "
	var JFormValidator = new Class({ });
";
JFactory::getDocument()->addScriptDeclaration($script);
JFactory::getDocument()->addStyleDeclaration('.nn_multiselect { width:300px; }');

?>

<form action="<?php echo JRoute::_('index.php?option=com_contenttemplater&id=' . ( int ) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form">
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

	<?php ob_start(); ?>
	<div style="display:none;">
		<?php echo str_replace('value="' . $view_state . '"', 'value="' . $view_state . '" checked="checked"', str_replace('checked="checked"', '', $this->render($this->item->form, 'view_state'))); ?>
	</div>

	<div class="view_state_<?php echo $view_state; ?>" id="view_state_div">
		<div class="button1 button_view_state simple">
			<div>
				<a onclick="NNFrameworkChangeView( 0 );" class="hasTip" title="<?php echo JText::_('NN_SIMPLE') . '::' . JText::_('CT_SIMPLE_DESC'); ?>"><?php echo JText::_('NN_SIMPLE'); ?></a>
			</div>
		</div>
		<div class="button1 button_view_state normal">
			<div>
				<a onclick="NNFrameworkChangeView( 1 );" class="hasTip" title="<?php echo JText::_('NN_NORMAL') . '::' . JText::_('CT_NORMAL_DESC'); ?>"><?php echo JText::_('NN_NORMAL'); ?></a>
			</div>
		</div>
		<div class="button1 button_view_state advanced">
			<div>
				<a onclick="NNFrameworkChangeView( 2 );" class="hasTip" title="<?php echo JText::_('NN_ADVANCED') . '::' . JText::_('CT_ADVANCED_DESC'); ?>"><?php echo JText::_('NN_ADVANCED'); ?></a>
			</div>
		</div>
		<div class="clr"></div>
	</div>
	<?php
	$view_state_buttons = ob_get_contents();
	ob_end_clean();
	$view_state_buttons = str_replace('VIEW_STATE_PLACEHOLDER', $view_state_buttons, $this->render($this->item->form, 'main_texts'));
	$view_state_buttons = preg_replace('#(<label.*?>|</label>)#', '', $view_state_buttons);
	?>


	<div class="width-100">
		<?php echo $view_state_buttons; ?>
	</div>

	<table width="100%">
		<tr>
			<td valign="top">
				<div class="width-100">
					<?php echo $this->render($this->item->form, 'details', JText::_('JDETAILS')); ?>

					<?php echo $this->render($this->item->form, '-content', JText::_('NN_CONTENT')); ?>

					<fieldset class="adminform">
						<legend><?php echo JText::_('CT_DYNAMIC_TAGS'); ?></legend>
						<?php
						$_tick = '<img src="../media/nnframework/images/tick.png" border="0" alt="' . JText::_('JYES') . '" title="' . JText::_('JYES') . '" />';
						$_cross = '<img src="../media/nnframework/images/publish_x.png" border="0" alt="' . JText::_('JNO') . '" title="' . JText::_('JNO') . '" />';
						?>
						<p><?php echo JText::_('CT_DYNAMIC_TAGS_DESC'); ?></p>

						<table class="adminlist" style="width:auto;">
							<thead>
								<tr>
									<th><label><?php echo JText::_('CT_SYNTAX'); ?></label></th>
									<th style="text-align:left">
										<span><?php echo JText::_('JGLOBAL_DESCRIPTION'); ?></span></th>
									<th style="text-align:left">
										<span><?php echo JText::_('CT_OUTPUT_EXAMPLE'); ?></span></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="font-family:monospace">[[user:id]]</td>
									<td><?php echo JText::_('CT_DYNAMIC_TAG_USER_ID'); ?></td>
									<td><?php echo $user->id; ?></td>
								</tr>
								<tr>
									<td style="font-family:monospace">[[user:username]]</td>
									<td><?php echo JText::_('CT_DYNAMIC_TAG_USER_USERNAME'); ?></td>
									<td><?php echo $user->username; ?></td>
								</tr>
								<tr>
									<td style="font-family:monospace">[[user:name]]</td>
									<td><?php echo JText::_('CT_DYNAMIC_TAG_USER_NAME'); ?></td>
									<td><?php echo $user->name; ?></td>
								</tr>
								<tr>
									<td style="font-family:monospace">[[user:...]]</td>
									<td><?php echo JText::_('CT_DYNAMIC_TAG_USER_OTHER'); ?></td>
									<td><?php echo isset($contact->misc) ? $contact->misc : ''; ?></td>
								</tr>
								<tr>
									<td style="font-family:monospace">[[date:...]]</td>
									<td><?php echo JText::sprintf('CT_DYNAMIC_TAG_DATE', '<a rel="{handler: \'iframe\', size:{x:window.getSize().x-100, y: window.getSize().y-100}}" href="http://www.php.net/manual/function.strftime.php" class="modal">', '</a>', '<span style="font-family:monospace">[[date: %A, %d %B %Y]]</span>'); ?></td>
									<td><?php
										$date = JFactory::getDate('now', 'UTC');
										echo $date->toFormat('%A, %d %B %Y');
										?></td>
								</tr>
								<tr>
									<td style="font-family:monospace">[[random:...-...]]</td>
									<td><?php echo JText::_('CT_DYNAMIC_TAG_RANDOM'); ?></td>
									<td><?php echo rand(0, 100); ?></td>
								</tr>
							</tbody>
						</table>
					</fieldset>
				</div>

				<div class="clr"></div>
			</td>
			<td valign="top" width="1%">
				<div class="width-100">
					<div id="<?php echo rand(1000000, 9999999); ?>___view_state.1___view_state.2"
						class="nntoggler nntoggler_horizontal">
						<div id="<?php echo rand(1000000, 9999999); ?>___view_state.1___view_state.2"
							class="nntoggler nntoggler_nofx">
							<div style="width:550px;">
								<fieldset class="adminform">
									<legend><?php echo JText::_('NN_PUBLISHING_SETTINGS'); ?></legend>
									<div id="nn_param_preloader_container">
										<center><img
												src="<?php echo JURI::root(true); ?>/media/nnframework/images/loading.gif"
												align="middle" style="float: none;" alt="" /></center>
									</div>
								</fieldset>

								<fieldset class="adminform">
									<legend><?php echo JText::_('CT_CONTENT_SETTINGS'); ?></legend>
									<?php
									echo $this->render($this->item->form, 'override');

									echo JHtml::_('sliders.start', 'contenttemplater-sliders-content');
									echo JHtml::_('sliders.panel', JText::_('CT_MAIN_SETTINGS'), 'search-page');
									echo $this->render($this->item->form, '-settings');

									$html = JHtml::_('sliders.panel', JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-page');
									echo str_replace('<div class="panel">', '<div id="' . rand(1000000, 9999999) . '___view_state.2" class="panel nntoggler">', $html);
									echo $this->render($this->item->form, '-publishing');
									$html = JHtml::_('sliders.panel', JText::_('COM_CONTENT_ATTRIBS_FIELDSET_LABEL'), 'basic-page');
									echo str_replace('<div class="panel">', '<div id="' . rand(1000000, 9999999) . '___view_state.2" class="panel nntoggler">', $html);
									echo $this->render($this->item->form, '-basic');
									$html = JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'metadata-page');
									echo str_replace('<div class="panel">', '<div id="' . rand(1000000, 9999999) . '___view_state.2" class="panel nntoggler">', $html);
									echo $this->render($this->item->form, '-metadata');
									$html = JHtml::_('sliders.panel', JText::_('CT_CUSTOM_FIELDS'), 'customfields-page');
									echo str_replace('<div class="panel">', '<div id="' . rand(1000000, 9999999) . '___view_state.2" class="panel nntoggler">', $html);
									echo $this->render($this->item->form, '-customfields');
									echo JHtml::_('sliders.end');
									?>
							</div>
						</div>
					</div>
				</div>

				<div class="clr"></div>
			</td>
		</tr>
	</table>

	<div class="clr"></div>

	<div id="nn_param_preloader" style="visibility: hidden;">
		<div class="width-100">
			<?php
			$html = array();

			$html[] = '<fieldset class="adminform">';
			$html[] = '<legend>' . JText::_('CT_EDITOR_BUTTON_LIST') . '</legend>';
			$str = $this->render($this->item->form, 'button');
			$html[] = str_replace(array('<fieldset class="panelform">', '</fieldset>'), '', $str);
			$html[] = '</fieldset>';

			$html[] = '<div id="' . rand(1000000, 9999999) . '___view_state.2" class="nntoggler">';

			$html[] = '<fieldset class="adminform">';
			$html[] = '<legend>' . JText::_('CT_LOAD_BY_DEFAULT') . '</legend>';
			$str = $this->render($this->item->form, 'load');
			$html[] = str_replace(array('<fieldset class="panelform">', '</fieldset>'), '', $str);
			$html[] = '</fieldset>';

			$html[] = '<fieldset class="adminform">';
			$html[] = '<legend>' . JText::_('CT_LOAD_BY_URL') . '</legend>';
			$str = $this->render($this->item->form, 'url');
			$html[] = str_replace(array('<fieldset class="panelform">', '</fieldset>'), '', $str);
			$html[] = '</fieldset>';

			$html[] = JHtml::_('sliders.start', 'contenttemplater-sliders-assignments', array('startOffset' => -1));
			$html[] = JHtml::_('sliders.panel', JText::_('NN_PUBLISHING_ASSIGNMENTS'), 'assignments-page');
			$html[] = $this->render($this->item->form, 'assignments');
			$html[] = JHtml::_('sliders.end');

			$html[] = '</div>';

			echo implode('', $html);
			?>
		</div>
	</div>
</form>

<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		var form = document.adminForm;
		if (task == 'item.cancel') {
			Joomla.submitform(task);
			return;
		}

		// do field validation
		if (form['jform[name]'].value.trim() == "") {
			alert("<?php echo JText::_('CT_THE_ITEM_MUST_HAVE_A_NAME', true); ?>");
		} else {
			Joomla.submitform(task);
		}
	}
</script>
