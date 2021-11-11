<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

$fieldSets = $this->form->getFieldsets('metadata');
foreach ($fieldSets as $name => $fieldSet)
{
	echo JHtml::_('sliders.panel', JText::_($fieldSet->label), $name . '-options');
	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
	<fieldset class="panelform">
		<ul class="adminformlist">
			<?php if ($name == 'jmetadata')
			{ ?>
				<li><?php echo $this->form->getLabel('metadesc'); ?>
				<?php echo $this->form->getInput('metadesc'); ?></li>

				<li><?php echo $this->form->getLabel('metakey'); ?>
				<?php echo $this->form->getInput('metakey'); ?></li>

				<li><?php echo $this->form->getLabel('xreference'); ?>
				<?php echo $this->form->getInput('xreference'); ?></li>
			<?php
			}?>
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
