<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$fieldSets = $this->form->getFieldsets('params');
foreach ($fieldSets as $name => $fieldSet)
{
	echo JHtml::_('sliders.panel', JText::_($fieldSet->label), $name . '-params');
	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
	<fieldset class="panelform">
		<ul class="adminformlist">
		<?php foreach ($this->form->getFieldset($name) as $field)
		{ ?>
			<li><?php echo $field->label; ?>
			<?php echo $field->input; ?></li>
		<?php
		} ?>
		</ul>
	</fieldset>
<?php
}
